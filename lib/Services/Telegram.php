<?php

namespace FS\Services;

use FS\FS_Config;

class Telegram {
	private $token;
	private $user_ids = [];

	private const CHAT_IDS_OPTION = 'fs_telegram_chat_ids_map';

	function __construct() {
		$this->token   = FS_Config::get_telegram_bot_token();
		$this->user_ids = $this->get_user_ids();
	}

	/**
	 * Parse configured Telegram user IDs.
	 *
	 * @return array
	 */
	private function get_user_ids() {
		$raw_user_ids = fs_option( 'fs_telegram_user_id' );

		if ( is_array( $raw_user_ids ) ) {
			$raw_user_ids = implode( PHP_EOL, $raw_user_ids );
		}

		$user_ids = preg_split( '/[\r\n,]+/', (string) $raw_user_ids );
		$user_ids = array_map(
			static function ( $user_id ) {
				return preg_replace( '/[^0-9\-]/', '', trim( (string) $user_id ) );
			},
			$user_ids
		);
		$user_ids = array_filter( $user_ids, static function ( $user_id ) {
			return $user_id !== '';
		} );

		return array_values( array_unique( $user_ids ) );
	}

	/**
	 * Return cached chat IDs keyed by Telegram user ID.
	 *
	 * @return array
	 */
	private function get_chat_ids_map() {
		$chat_ids_map = get_option( self::CHAT_IDS_OPTION, [] );

		return is_array( $chat_ids_map ) ? $chat_ids_map : [];
	}

	/**
	 * Persist cached chat IDs keyed by Telegram user ID.
	 *
	 * @param array $chat_ids_map
	 *
	 * @return void
	 */
	private function update_chat_ids_map( $chat_ids_map ) {
		update_option( self::CHAT_IDS_OPTION, $chat_ids_map );
	}

	/**
	 * Extract chat id from Telegram update for a specific user.
	 *
	 * @param array  $update
	 * @param string $user_id
	 *
	 * @return string
	 */
	private function extract_chat_id_from_update( $update, $user_id ) {
		$message_paths = [
			[ 'message' ],
			[ 'edited_message' ],
			[ 'callback_query', 'message' ],
		];

		foreach ( $message_paths as $path ) {
			$message = $update;
			foreach ( $path as $segment ) {
				if ( ! isset( $message[ $segment ] ) || ! is_array( $message[ $segment ] ) ) {
					$message = null;
					break;
				}
				$message = $message[ $segment ];
			}

			if ( empty( $message['chat']['id'] ) || ! isset( $message['from']['id'] ) ) {
				continue;
			}

			if ( (string) $message['from']['id'] !== $user_id ) {
				continue;
			}

			// Personal bot notifications must target the user's private chat.
			if ( empty( $message['chat']['type'] ) || $message['chat']['type'] !== 'private' ) {
				continue;
			}

			if ( (string) $message['chat']['id'] === $user_id ) {
				return (string) $message['chat']['id'];
			}
		}

		return '';
	}

	/**
	 * Cached chat id is valid only for a private dialog with the same user.
	 *
	 * @param string $user_id
	 * @param mixed  $chat_id
	 *
	 * @return bool
	 */
	private function is_valid_private_chat_id( $user_id, $chat_id ) {
		return (string) $chat_id !== '' && (string) $chat_id === (string) $user_id;
	}

	/**
	 * Telegram rejects inline URL buttons pointing to local/dev hosts.
	 *
	 * @param array $url_button
	 *
	 * @return bool
	 */
	private function has_valid_url_button( $url_button ) {
		if ( empty( $url_button['url'] ) || empty( $url_button['text'] ) ) {
			return false;
		}

		$url = (string) $url_button['url'];
		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( $host === '' ) {
			return false;
		}

		$blocked_hosts = [ 'localhost', '127.0.0.1', '0.0.0.0' ];
		if ( in_array( strtolower( $host ), $blocked_hosts, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get chat id by user id
	 *
	 * @param $user_id
	 * @param $save
	 *
	 * @return void
	 */
	function get_chat_id_by_user_id( $user_id ) {
		$user_id      = (string) $user_id;
		$chat_ids_map = $this->get_chat_ids_map();

		if ( ! empty( $chat_ids_map[ $user_id ] ) && $this->is_valid_private_chat_id( $user_id, $chat_ids_map[ $user_id ] ) ) {
			return $chat_ids_map[ $user_id ];
		}

		if ( isset( $chat_ids_map[ $user_id ] ) ) {
			unset( $chat_ids_map[ $user_id ] );
			$this->update_chat_ids_map( $chat_ids_map );
		}

		$updates = $this->get_updates();
		if ( ! is_array( $updates ) ) {
			return '';
		}

		$chat_id = '';
		for ( $i = count( $updates ) - 1; $i >= 0; $i-- ) {
			$chat_id = $this->extract_chat_id_from_update( $updates[ $i ], $user_id );
			if ( $chat_id !== '' ) {
				break;
			}
		}

		if ( $chat_id !== '' && $this->is_valid_private_chat_id( $user_id, $chat_id ) ) {
			$chat_ids_map[ $user_id ] = $chat_id;
			$this->update_chat_ids_map( $chat_ids_map );
			return $chat_id;
		}

		// For private bot dialogs Telegram chat_id usually equals the user_id.
		return $user_id;
	}

	/**
	 * Send message to telegram
	 *
	 * @see https://core.telegram.org/bots/api#sendmessage
	 *
	 * @param $message
	 * @param $urlButton
	 *
	 * @return void
	 */
	function send_message( $message, $urlButton = [] ) {
		$results = [];

		if ( empty( $this->user_ids ) || ! $this->token ) {
			return $results;
		}

		foreach ( $this->user_ids as $user_id ) {
			$chat_id = $this->get_chat_id_by_user_id( $user_id );
			if ( ! $chat_id ) {
				error_log( 'Telegram chat id not found for user ' . $user_id );
				$results[] = [
					'user_id' => $user_id,
					'status'  => 'chat_not_found',
				];
				continue;
			}

			$query_args = [
				'parse_mode' => 'HTML',
				'chat_id'    => $chat_id,
				'text'       => $message,
			];

			if ( $this->has_valid_url_button( $urlButton ) ) {
				$query_args['reply_markup'] = wp_json_encode(
					[
						'inline_keyboard' => [
							[ $urlButton ],
						],
					]
				);
			}

			$url = 'https://api.telegram.org/bot' . $this->token . '/sendMessage?' . http_build_query( $query_args );

			$response = wp_remote_get( $url );

			if ( is_wp_error( $response ) ) {
				error_log( 'Telegram sendMessage failed for user ' . $user_id . ': ' . $response->get_error_message() );
				$results[] = [
					'user_id' => $user_id,
					'chat_id' => $chat_id,
					'status'  => 'request_failed',
					'error'   => $response->get_error_message(),
				];
				continue;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $body['ok'] ) ) {
				$description = ! empty( $body['description'] ) ? $body['description'] : 'Unknown Telegram API error';
				error_log( 'Telegram sendMessage rejected for user ' . $user_id . ': ' . $description );
				$results[] = [
					'user_id' => $user_id,
					'chat_id' => $chat_id,
					'status'  => 'rejected',
					'error'   => $description,
				];
				continue;
			}

			$results[] = [
				'user_id' => $user_id,
				'chat_id' => $chat_id,
				'status'  => 'sent',
			];
		}

		return $results;
	}

	/**
	 * Get updates
	 * @see https://core.telegram.org/bots/api#getting-updates
	 *
	 * @return array|mixed|string
	 */
	function get_updates() {
		$url = 'https://api.telegram.org/bot' . $this->token . '/getUpdates';

		// Выполняем GET запрос к Telegram API
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return "Ошибка получения данных: " . $response->get_error_message();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		return isset( $data['result'] ) ? $data['result'] : [];
	}

	function escape_markdown( $text ) {
		// Массив символов, которые нужно экранировать
		$special_chars = [ '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!' ];

		// Проходим по каждому символу и добавляем перед ним обратный слэш
		foreach ( $special_chars as $char ) {
			$text = str_replace( $char, '\\' . $char, $text );
		}

		return $text;
	}

}
