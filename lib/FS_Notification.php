<?php

namespace FS;

class FS_Notification {
	protected $recipients;
	protected $subject;
	protected $message;
	protected $attachment;
	protected $sender;
	protected $channels = [
		'email'
	];

	protected string $template = '';

	/**
	 * Handles notification sending via a specified channel.
	 *
	 * @return \WP_Error
	 * @throws \WP_Error If the specified channel does not exist.
	 */
	public function send() {
		foreach ( $this->channels as $channel ) {
			// check if method exists
			if ( ! method_exists( __CLASS__, 'send_via_' . $channel ) ) {
				return new \WP_Error( 'fs_invalid_channel', 'Channel ' . $channel . ' does not exist' );
			}
			$this->{'send_via_' . $channel}();
		}

	}

	/**
	 * @param mixed $recipients
	 */
	public function set_recipients( array $recipients ): void {
		$this->recipients = $recipients;
	}

	/**
	 * @param mixed $message
	 */
	public function set_message( $message, $replace = [] ): void {
		$this->message = $message;
	}

	/**
	 * @param mixed $subject
	 */
	public function set_subject( $subject ): void {
		$this->subject = $subject;
	}

	public function set_template( $template, $replace = [] ): void {
		$this->template = $template;
	}

	private function send_via_email() {
		$headers = array(
			sprintf(
				'From: %s <%s>',
				fs_option( 'name_sender', get_bloginfo( 'name' ) ),
				fs_option( 'email_sender', 'shop@' . $_SERVER['SERVER_NAME'] )
			),
			'Content-type: text/html; charset=utf-8'
		);

		return wp_mail( $this->recipients, $this->subject, $this->message, $headers, $this->attachment );
	}


	private function validate() {

	}
}