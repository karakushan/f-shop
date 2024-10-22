<?php

namespace FS\Services;

use FS\FS_Config;

class Telegram {
	private $token;
	private $user_id;

	function __construct() {
		$this->token   = FS_Config::get_telegram_bot_token();
		$this->user_id = fs_option( 'fs_telegram_user_id' );
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
		if ( get_option( 'fs_telegram_chat_id' ) ) {
			return get_option( 'fs_telegram_chat_id' );
		}
		$updates = $this->get_updates();
		$chat_id = '';
		foreach ( $updates as $update ) {
			if ( isset( $update['message']['chat']['id'] ) ) {
				if ( $update['message']['from']['id'] == $user_id ) {
					$chat_id = $update['message']['chat']['id'];
					break;
				}
			}
		}

		update_option( 'fs_telegram_chat_id', $chat_id );

		return $chat_id;
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
		if ( ! $this->user_id ) {
			return;
		}
		$token   = FS_Config::get_telegram_bot_token();
		$chat_id = $this->get_chat_id_by_user_id( $this->user_id );
		if ( ! $chat_id ) {
			error_log( 'Telegram chat id not found for user ' . $this->user_id );

			return;
		}

		$url      = 'https://api.telegram.org/bot' . $token . '/sendMessage';
		$keyboard = [
			'inline_keyboard' => [
				[ $urlButton ]
			]
		];
		$url      = $url . '?' . http_build_query( [
				'parse_mode'   => 'HTML',
				'chat_id'      => $chat_id,
				'text'         => $message,
				'reply_markup' => json_encode( $keyboard )
			] );

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			error_log( print_r( $response->get_error_message(), true ) );
		}
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