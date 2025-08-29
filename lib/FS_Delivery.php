<?php

namespace FS;

if (! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

/**
 * Delivery class
 */
class FS_Delivery
{

	public $name = '';
	public $city = '';
	public $cost = 0;
	public $delivery_address = '';
	public $delivery_service_number = 0;
	public $term_id = 0;

	/**
	 * FS_Delivery constructor.
	 *
	 * @param int|null $order_id ID заказа
	 */
	public function __construct(?int $order_id = null)
	{
		// Если ID заказа не передан, выходим
		if (!$order_id) {
			return;
		}

		$delivery_method = get_post_meta($order_id, '_delivery', 1);

		// Проверяем корректность метода доставки
		$is_invalid_delivery = !isset($delivery_method['method'])
			|| !is_numeric($delivery_method['method'])
			|| is_wp_error(
				get_term(
					absint($delivery_method['method']),
					FS_Config::get_data('product_del_taxonomy')
				)
			);

		if ($is_invalid_delivery) {
			return;
		}

		// Получаем термин доставки
		$term = get_term(
			$delivery_method['method'],
			FS_Config::get_data('product_del_taxonomy')
		);

		if (!$term || is_wp_error($term)) {
			return;
		}

		// Инициализируем данные доставки
		$this->term_id = absint($term->term_id);
		$this->name = $term->name;

		// Получаем и форматируем стоимость доставки
		$cost = get_term_meta($term->term_id, '_fs_delivery_cost', 1);
		$this->cost = $cost && is_numeric($cost) ? (float)$cost : 0;

		// Устанавливаем адресные данные
		$this->city = get_post_meta($order_id, 'city', 1);
		$this->delivery_address = !empty($delivery_method['address'])
			? $delivery_method['address']
			: '';
		$this->delivery_service_number = !empty($delivery_method['secession'])
			? $delivery_method['secession']
			: '';
	}

	/**
	 * Retrieves the available shipping methods based on the specified arguments.
	 *
	 * @param array $args Optional. Arguments to filter the shipping methods. Default values include:
	 *                    'taxonomy'   => The taxonomy to query (default: FS_Config::get_data('product_del_taxonomy')).
	 *                    'hide_empty' => Whether to hide terms not assigned to any posts (default: false).
	 *                    'meta_key'   => Meta key for sorting (default: '_fs_term_order').
	 *                    'orderby'    => Field to order terms by (default: 'meta_value_num').
	 *                    'order'      => Order to retrieve terms in (default: 'ASC').
	 *
	 * @return array An array of shipping method objects with added price details from the term meta.
	 */
	public static function get_shipping_methods($args = [])
	{

		$args             = wp_parse_args(
			$args,
			[
				'taxonomy'   => FS_Config::get_data('product_del_taxonomy'),
				'hide_empty' => false,
				'meta_key'   => '_fs_term_order',
				'orderby'    => 'meta_value_num',
				'order'      => 'ASC',
			]
		);
		
		$methods          = [];
		$shipping_methods = get_terms($args);

		foreach ($shipping_methods as $shipping_method) {
			// Check if delivery method is inactive
			$is_inactive = get_term_meta($shipping_method->term_id, '_fs_delivery_inactive', true);
			if ($is_inactive === 'yes') {
				continue; // Skip inactive delivery methods
			}
			
			$method        = $shipping_method;
			$method->price = floatval(get_term_meta($shipping_method->term_id, '_fs_delivery_cost', 1));
			$methods[]     = $method;
		}

		return apply_filters('fs_shipping_methods', $methods);
	}

	/**
	 * Возвращает стоимость доставки в текстовом формате
	 *
	 * @param string $format Формат вывода стоимости доставки. По умолчанию '%s <span>%s</span>'
	 *                      Первый %s - цена, второй %s - валюта
	 * 
	 * @return string Отформатированная стоимость доставки или текст о бесплатной доставке
	 */
	public function get_shipping_cost_text($format = '%s <span>%s</span>')
	{
		$text = '';

		// Получаем текст для бесплатной доставки из мета-данных
		$free_shipping_text = fs_get_term_meta('_fs_delivery_cost_text', $this->term_id, 1, true);

		// Если есть стоимость доставки и она больше 0
		if (get_term_meta($this->term_id, '_fs_delivery_cost', 1) && $this->cost > 0) {
			$text = sprintf(
				$format,
				apply_filters('fs_price_format', $this->cost),
				fs_currency()
			);
		}
		// Если есть текст для бесплатной доставки
		elseif ($free_shipping_text) {
			$text = $free_shipping_text;
		}
		// В остальных случаях выводим 0 в заданном формате
		else {
			$text = sprintf(
				$format,
				apply_filters('fs_price_format', 0),
				fs_currency()
			);
		}

		return $text;
	}

	/**
	 * Возвращает стоимость доставки в текстовом формате
	 * @return string
	 */
	public function shipping_cost_text($format = '%s <span>%s</span>')
	{
		echo $this->get_shipping_cost_text($format);
	}
}
