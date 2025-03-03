<?php

namespace FS;

use FS\Services\Telegram;

class FS_Notification
{
	protected $recipients;
	protected $subject;
	protected $message;
	protected $attachment;
	protected $sender;
	protected $replace;
	protected $channels = [
		'email',
	];

	protected string $template = '';

	/**
	 * Handles notification sending via a specified channel.
	 *
	 * @return \WP_Error
	 * @throws \WP_Error If the specified channel does not exist.
	 */
	public function send()
	{
		foreach ($this->channels as $channel) {
			// check if method exists
			if (! method_exists(__CLASS__, 'send_via_' . $channel)) {
				return new \WP_Error('fs_invalid_channel', 'Channel ' . $channel . ' does not exist');
			}
			$this->{'send_via_' . $channel}();
		}
	}

	/**
	 * @param mixed $recipients
	 */
	public function set_recipients(array $recipients): void
	{
		$this->recipients = $recipients;
	}

	/**
	 * Sets notification message
	 *
	 * @param mixed $attachment
	 * @param mixed $message
	 */
	public function set_message($message, $replace = []): void
	{
		$this->replace = $replace;
		$this->message = $message;
	}

	/**
	 * Sets notification subject
	 *
	 * @param mixed $subject
	 */
	public function set_subject($subject): void
	{
		$this->subject = $subject;
	}

	/**
	 * Sets notification template
	 *
	 * @param $template
	 * @param $replace
	 *
	 * @return void
	 */
	public function set_template($template, $replace = []): void
	{
		$this->template = $template;
		$this->replace  = $replace;
		$this->message  = fs_frontend_template($template, [
			'vars' => $replace
		]);
	}

	/**
	 * Sends notification via email
	 *
	 * @return bool|mixed
	 */
	private function send_via_email()
	{
		$headers = array(
			sprintf(
				'From: %s <%s>',
				fs_option('name_sender', get_bloginfo('name')),
				fs_option('email_sender', 'shop@' . $_SERVER['SERVER_NAME'])
			),
			'Content-type: text/html; charset=utf-8'
		);

		return wp_mail($this->recipients, $this->subject, $this->replace_mail_variables($this->message), $headers, $this->attachment);
	}

	/**
	 * Sends notification via telegram
	 *
	 * @return void
	 */
	private function send_via_telegram()
	{
		$urlButton = [
			'text' => __('View order', 'f-shop'),
			'url'  => $this->replace['order_edit_url'],
		];

		$telegram = new Telegram();
		$message  = "<b>Нове замовлення №%order_id% на сайті %site_name%</b>\n\n";
		$message  .= "<b>Сума замовлення:</b> %cart_amount%\n"
			. "<b>Спосіб доставки:</b> %delivery_method%\n"
			. "<b>Відділення:</b> %delivery_number%\n"
			. "<b>Адреса:</b> %client_address%\n"
			. "<b>Тип оплати:</b> %payment_method%\n"
			. "<b>Прізвище та ім’я:</b> %client_first_name% %client_last_name%\n"
			. "<b>E-mail:</b> %client_email%\n"
			. "<b>Телефон:</b> %client_phone%\n"
			. "<b>Коментар:</b> %client_comment%\n";

		$telegram->send_message($this->replace_mail_variables($message), $urlButton);
	}

	/**
	 * Replaces mail variables
	 *
	 * @param $message
	 *
	 * @return array|string|string[]
	 */
	function replace_mail_variables($message)
	{
		return str_replace(array_map(function ($item) {
			return '%' . $item . '%';
		}, array_keys($this->replace)), array_values($this->replace), $message);
	}


	/**
	 * Pushes a new channel to the list of channels
	 *
	 * @param $channel
	 *
	 * @return void
	 */
	function push_channel($channel)
	{
		$this->channels[] = $channel;
	}
}
