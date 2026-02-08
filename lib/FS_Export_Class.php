<?php

namespace FS;

if (! defined('ABSPATH')) {
	exit;
} // Exit if accessed directly
class FS_Export_Class
{
	public $feed_name = 'fs-yml-export';
	public static $base_price;
	public static $action_price;

	public function __construct()
	{
		add_action('template_redirect', array($this, 'http_get_export'));
		add_action('init', array($this, 'products_feed'));
	}

	/**
	 * Создание XML фида товаров
	 */
	function products_feed()
	{
		add_feed($this->feed_name, array($this, 'products_to_yml'));
	}

	/**
	 * Экспорт товаров по GET запросу
	 */
	function http_get_export()
	{
		if (! isset($_GET['fs-yml-export'])) {
			return;
		}

		$this->products_to_yml();
	}

	/**
	 * Додає мультиязичні поля до товару в YML фіді з використанням wp-multilang плагіну
	 *
	 * @param \DomDocument $xml
	 * @param \DOMElement $offer
	 * @param int $post_id
	 */
	public function add_multilingual_fields($xml, $offer, $post_id)
	{
		// Перевіряємо чи активний wp-multilang плагін
		if (!function_exists('wpm_string_to_ml_array')) {
			return;
		}
		
		$default_lang = fs_option('_fs_default_language', 'ru');
		$languages = \FS\FS_Config::get_languages();
		
		// Отримуємо пост
		$post = get_post($post_id);
		if (!$post) {
			return;
		}
		
		// Отримуємо мультиязичні розміри для назви
		$title_ml_array = wpm_string_to_ml_array($post->post_title);
		if (!is_array($title_ml_array)) {
			$title_ml_array = [$default_lang => $post->post_title];
		}
		
		// Додаємо name_ua, name_ru тощо за допомогою wp-multilang даних
		foreach ($languages as $lang_code => $lang_data) {
			if ($lang_code === $default_lang) {
				continue; // Пропускаємо мову за замовчуванням
			}
			
			// Додаємо переклад назви якщо він існує
			if (isset($title_ml_array[$lang_code]) && !empty($title_ml_array[$lang_code])) {
				$name_element = $xml->createElement('name_' . $lang_code, $title_ml_array[$lang_code]);
				$offer->appendChild($name_element);
			}
		}
		
		// Отримуємо мультиязичні розміри для опису
		$content_ml_array = wpm_string_to_ml_array($post->post_content);
		if (!is_array($content_ml_array)) {
			$content_ml_array = [$default_lang => $post->post_content];
		}
		
		// Додаємо description_ua, description_ru тощо за допомогою wp-multilang даних
		foreach ($languages as $lang_code => $lang_data) {
			if ($lang_code === $default_lang) {
				continue; // Пропускаємо мову за замовчуванням
			}
			
			// Додаємо переклад опису якщо він існує
			if (isset($content_ml_array[$lang_code]) && !empty($content_ml_array[$lang_code])) {
				$content_translated = $content_ml_array[$lang_code];
				
				// Перевіряємо, чи активована опція експорту повного опису з HTML
				if (fs_option('_fs_full_description_feed')) {
					// Експортуємо повний опис з HTML тегами у CDATA
					$desc_element = $xml->createElement('description_' . $lang_code);
					$desc_element->appendChild($xml->createCDATASection($content_translated));
				} else {
					// Очищуємо опис (видаляємо HTML теги та HTML entities)
					$clean_description = str_replace('&nbsp;', ' ', $content_translated);
					$clean_description = str_replace('&mdash;', '—', $clean_description);
					$clean_description = str_replace('&ndash;', '–', $clean_description);
					$clean_description = str_replace('&laquo;', '«', $clean_description);
					$clean_description = str_replace('&raquo;', '»', $clean_description);
					$clean_description = wp_strip_all_tags($clean_description);
					$clean_description = trim(preg_replace('/\s+/', ' ', $clean_description));
					
					if (strlen($clean_description) > 9000) {
						$clean_description = substr($clean_description, 0, 9000);
					}
					
					$desc_element = $xml->createElement('description_' . $lang_code, $clean_description);
				}
				
				if (!empty($desc_element)) {
					$offer->appendChild($desc_element);
				}
			}
		}
		
		// Додаємо параметри з мультиязичними значеннями
		$product_attributes = get_the_terms($post_id, 'product-attributes');
		if ($product_attributes) {
			global $wpdb;
			
			foreach ($product_attributes as $attribute) {
				// Отримуємо ім'я атрибута з БД напрямку
				$attr_name_raw = $wpdb->get_var($wpdb->prepare(
					"SELECT name FROM {$wpdb->terms} WHERE term_id = %d",
					$attribute->term_id
				));
						
				// Отримуємо parent name з БД напрямку для уникнуття фільтрів
				$parent_name_raw = $wpdb->get_var($wpdb->prepare(
					"SELECT tm.name FROM {$wpdb->term_taxonomy} t JOIN {$wpdb->terms} tm ON t.term_id = tm.term_id WHERE t.term_id = %d",
					$attribute->parent
				));
						
				if ($attr_name_raw && $parent_name_raw) {
					// Отримуємо мультиязичні розміри для назви атрибута значення
					$attr_ml_array = wpm_string_to_ml_array($attr_name_raw);
					if (!is_array($attr_ml_array)) {
						$attr_ml_array = [$default_lang => $attr_name_raw];
					}
									
					// Отримуємо мультиязичні розміри для назви категорії параметра
					$parent_ml_array = wpm_string_to_ml_array($parent_name_raw);
					if (!is_array($parent_ml_array)) {
						$parent_ml_array = [$default_lang => $parent_name_raw];
					}
									
					// Отримуємо основне ім'я параметра (для мови за замовчуванням)
					$param_name = !empty($parent_ml_array[$default_lang]) ? $parent_ml_array[$default_lang] : $parent_name_raw;
									
					// Не додаємо text node, усі вин в value элементах
					$param = $xml->createElement('param');
					$param->setAttribute("name", $param_name);
									
					// Додаємо value для кожної мови
					// Маппінг кодів мов з wp-multilang на ISO коди для Rozetka
					$lang_code_map = [
						'ua' => 'uk',  // Ukrainian
						'ru' => 'ru',   // Russian
					];
									
					foreach ($languages as $lang_code => $lang_data) {
						// Отримуємо значення атрибута для даного языка
						if (isset($attr_ml_array[$lang_code]) && !empty($attr_ml_array[$lang_code])) {
							$value = $xml->createElement('value', $attr_ml_array[$lang_code]);
							// Завжди додаємо lang атрибут з маппованим кодом
							$mapped_lang = isset($lang_code_map[$lang_code]) ? $lang_code_map[$lang_code] : $lang_code;
							$value->setAttribute('lang', $mapped_lang);
							$param->appendChild($value);
						}
					}
						
					$offer->appendChild($param);
				}
			}
		}
	}


	function products_to_yml($admin_notices = false)
	{
		$fs_config = new FS_Config();

		header('Content-type: text/xml');

		$xml                     = new \DomDocument('1.0', get_bloginfo('charset'));
		$xml->formatOutput       = true;
		$xml->preserveWhiteSpace = false;


		$gallery            = new FS_Images_Class();
		self::$base_price   = apply_filters('fs_export_base_price', 'price');
		self::$action_price = apply_filters('fs_export_action_price', 'action_price');


		/*yml_catalog*/
		$yml_catalog = $xml->createElement('yml_catalog');
		$yml_catalog->setAttribute("date", date('Y-m-d H:i'));
		$xml->appendChild($yml_catalog);
		/*yml_catalog->shop*/
		$shop = $xml->createElement('shop');
		$yml_catalog->appendChild($shop);
		/*yml_catalog->shop->name*/
		$shop_name = $xml->createElement('name', get_bloginfo('name'));
		$shop->appendChild($shop_name);
		/*yml_catalog->shop->company*/
		$shop_company = $xml->createElement('company', fs_option('company_name', get_bloginfo('name')));
		$shop->appendChild($shop_company);
		/*yml_catalog->shop->url*/
		$shop_url = $xml->createElement('url', get_bloginfo('url'));
		$shop->appendChild($shop_url);
		/*yml_catalog->shop->currencies*/
		$currencies = $xml->createElement('currencies');
		$shop->appendChild($currencies);
		/*yml_catalog->shop->currencies->currency*/
		$currency = $xml->createElement('currency');
		$currency->setAttribute("id", 'UAH');
		$currency->setAttribute('rate', '1');
		$currencies->appendChild($currency);

		//  КАТЕГОРИИ
		/*yml_catalog->shop->currencies*/
		$categories = $xml->createElement('categories');
		$shop->appendChild($categories);
		/*yml_catalog->shop->category*/
		$terms = get_terms(array('taxonomy' => 'catalog', 'hide_empty' => false));
		if ($terms) {
			foreach ($terms as $key => $term) {
				$category = $xml->createElement('category', $term->name);
				$category->setAttribute("id", $term->term_id);
				if ($term->parent) {
					$category->setAttribute("parentId", $term->parent);
				}
				$categories->appendChild($category);
			}
		}

		//  ТОВАРЫ
		/*yml_catalog->shop->offers*/
		$offers = $xml->createElement('offers');
		$shop->appendChild($offers);

		$posts = get_posts(array('post_type' => $fs_config->data['post_type'], 'posts_per_page' => -1));
		if ($posts) {
			foreach ($posts as $key => $post) {
				setup_postdata($post);
				$offer_id = apply_filters('fs_product_id', $post->ID);
				/*yml_catalog->shop->offers->offer*/
				$offer = $xml->createElement('offer');

				$offer->setAttribute("id", $offer_id);
				$offer->setAttribute("available", 'true');
				$offers->appendChild($offer);
				/*yml_catalog->shop->offers->offer->url*/
				$url = $xml->createElement('url', get_permalink($post->ID));
				$offer->appendChild($url);

				/*yml_catalog->shop->offers->offer->price*/
				// Отримуємо ціни товару з мета-полів
				$base_price = get_post_meta($post->ID, $fs_config->meta['price'], 1); // базова ціна
				$action_price = get_post_meta($post->ID, $fs_config->meta['action_price'], 1); // акційна ціна
						
				// Конвертуємо в float (замінюємо кому на крапку)
				$base_price = floatval(str_replace(',', '.', $base_price));
				$action_price = floatval(str_replace(',', '.', $action_price));
						
				// Застосовуємо фільтр конвертації валют (як в fs_get_price)
				$base_price = apply_filters('fs_price_filter', $base_price, $post->ID);
				$action_price = apply_filters('fs_price_filter', $action_price, $post->ID);
						
				// Застосовуємо додаткові фільтри експорту (якщо потрібні)
				$base_price = apply_filters('fs_export_price', $base_price);
				$action_price = apply_filters('fs_export_price_promo', $action_price);
						
				// Визначаємо поточну ціну (з урахуванням знижки)
				$current_price = ($action_price > 0 && $action_price < $base_price) ? $action_price : $base_price;
						
				// Пропускаємо товари без ціни або з ціною 0 (Rozetka не приймає такі товари)
				if (!$current_price || $current_price <= 0) {
					continue; // Пропускаємо цей товар
				}
						
				// Округлюємо ціни до 2 знаків (як в настройках плагіну)
				$use_pennies = fs_option('price_cents') ? 2 : 0;
				$base_price = round($base_price, $use_pennies);
				$action_price = round($action_price, $use_pennies);
						
				// Якщо є акційна ціна і вона менша за базову
				if ($action_price > 0 && $action_price < $base_price) {
					/*yml_catalog->shop->offers->offer->price*/
					$price = $xml->createElement('price', $action_price);
					$offer->appendChild($price);
						
					/*yml_catalog->shop->offers->offer->oldprice*/
					$oldprice = $xml->createElement('oldprice', $base_price);
					$offer->appendChild($oldprice);
				} else {
					/*yml_catalog->shop->offers->offer->price*/
					$price = $xml->createElement('price', $base_price);
					$offer->appendChild($price);
				}

				/*yml_catalog->shop->offers->offer->currencyId*/
				$currencyId = $xml->createElement('currencyId', 'UAH');
				$offer->appendChild($currencyId);
				/*yml_catalog->shop->offers->offer->name*/
				// Отримуємо назву для мови за замовчуванням
				// Отримуємо роботу з ровним данним з БД для уникнуття всіх фільтрів
				global $wpdb;
				$post_title_raw = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $post->ID));
				$post_title = $post_title_raw ?: $post->post_title;
				$default_lang = fs_option('_fs_default_language', 'ru');
										
				// Якщо мультиязичність ввімкнена, витягуємо переклад для мови за замовчуванням
				if (fs_option('_fs_multilingual_feed') && function_exists('wpm_string_to_ml_array')) {
					$title_ml_array = wpm_string_to_ml_array($post_title);
					if (is_array($title_ml_array) && !empty($title_ml_array[$default_lang])) {
						$post_title = $title_ml_array[$default_lang];
					} else if (is_array($title_ml_array)) {
						// Для мови за замовчуванням пуста, вибираємо першу доступну мову
						foreach ($title_ml_array as $lang => $text) {
							if (!empty($text)) {
								$post_title = $text;
								break;
							}
						}
					}
				}
										
				$name = $xml->createElement('name', $post_title);
				$offer->appendChild($name);
				
				// Додаємо мультиязичні поля, якщо ввімкнено
				if (fs_option('_fs_multilingual_feed')) {
					$this->add_multilingual_fields($xml, $offer, $post->ID);
				}
							
				/*yml_catalog->shop->offers->offer->vendor*/
				// Отримуємо атрибут для vendor з налаштувань
				$vendor_attribute_slug = fs_option('_fs_vendor_attribute');
				if ($vendor_attribute_slug) {
					// Отримуємо першу значення атрибута товару з БД напрямку
					global $wpdb;
									
					// Отримуємо ID родительського атрибута по слагу
					$parent_term_id = $wpdb->get_var($wpdb->prepare(
						"SELECT t.term_id FROM {$wpdb->terms} t
						 JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
						 WHERE t.slug = %s AND tt.taxonomy = 'product-attributes' AND tt.parent = 0
						 LIMIT 1",
						$vendor_attribute_slug
					));
									
					if ($parent_term_id) {
						// Отримуємо першу дочірню значення атрибута для цього товару
						$vendor_name = $wpdb->get_var($wpdb->prepare(
							"SELECT t.name FROM {$wpdb->terms} t
							 JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
							 JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
							 WHERE tr.object_id = %d AND tt.parent = %d AND tt.taxonomy = 'product-attributes'
							 LIMIT 1",
							$post->ID,
							$parent_term_id
						));
										
						if ($vendor_name) {
							// Якщо є мультиязичність, витягуємо для мови за замовчуванням
							if (fs_option('_fs_multilingual_feed') && function_exists('wpm_string_to_ml_array')) {
								$vendor_ml_array = wpm_string_to_ml_array($vendor_name);
								if (is_array($vendor_ml_array) && !empty($vendor_ml_array[$default_lang])) {
									$vendor_name = $vendor_ml_array[$default_lang];
								} else if (is_array($vendor_ml_array)) {
									foreach ($vendor_ml_array as $lang => $text) {
										if (!empty($text)) {
											$vendor_name = $text;
											break;
										}
									}
								}
							}
							$vendor = $xml->createElement('vendor', $vendor_name);
							$offer->appendChild($vendor);
						}
					}
				}
							
				/*yml_catalog->shop->offers->offer->vendorCode*/
				// Виводимо vendorCode тільки якщо артикул не пустий
				$product_code = fs_get_product_code($post->ID);
				if (!empty($product_code)) {
					$vendorCode = $xml->createElement('vendorCode', $product_code);
					$offer->appendChild($vendorCode);
				}
				/*yml_catalog->shop->offers->offer->description*/
				// Підготовка опису
				// Отримуємо роботу з ровним данним з БД для уникнуття всіх фільтрів
				$description_text = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $post->ID));
				if (!$description_text) {
					$description_text = $post->post_content;
				}
							
				// Якщо мультиязичність ввімкнена, витягуємо переклад для мови за замовчуванням
				if (fs_option('_fs_multilingual_feed') && function_exists('wpm_string_to_ml_array')) {
					$content_ml_array = wpm_string_to_ml_array($description_text);
					if (is_array($content_ml_array) && !empty($content_ml_array[$default_lang])) {
						$description_text = $content_ml_array[$default_lang];
					} else if (is_array($content_ml_array)) {
						// Для мови за замовчуванням пуста, вибираємо першу доступну мову
						foreach ($content_ml_array as $lang => $text) {
							if (!empty($text)) {
								$description_text = $text;
								break;
							}
						}
					}
				}
							
				// Перевіряємо, чи активована опція експорту повного опису з HTML
				if (fs_option('_fs_full_description_feed')) {
					// Експортуємо повний опис з HTML тегами у CDATA
					$description = $xml->createElement('description');
					$description->appendChild($xml->createCDATASection($description_text));
				} else {
					// Видаляємо HTML entities та теги, експортуємо чистий текст
					$description_text = str_replace('&nbsp;', ' ', $description_text);
					$description_text = str_replace('&mdash;', '—', $description_text);
					$description_text = str_replace('&ndash;', '–', $description_text);
					$description_text = str_replace('&laquo;', '«', $description_text);
					$description_text = str_replace('&raquo;', '»', $description_text);
					// Видаляємо HTML теги та очищуємо текст
					$description_text = wp_strip_all_tags($description_text);
					// Видаляємо зайві пробіли
					$description_text = trim(preg_replace('/\s+/', ' ', $description_text));
					// Обмежуємо довжину опису (Rozetka рекомендує до 9000 символів)
					if (strlen($description_text) > 9000) {
						$description_text = substr($description_text, 0, 9000);
					}
					$description = $xml->createElement('description', $description_text);
				}
				$offer->appendChild($description);


				/*yml_catalog->shop->offers->offer->categoryId*/
				$product_terms = get_the_terms($post->ID, 'catalog');
				if ($product_terms) {
					$count_terms = 0;
					foreach ($product_terms as $key => $product_term) {
						$count_terms++;
						if ($count_terms > 1) {
							break;
						}
						$categoryId = $xml->createElement('categoryId', $product_term->term_id);
						$offer->appendChild($categoryId);
					}
				}
				
				// Додаємо параметри (звичайні або мультиязичні)
				if (!fs_option('_fs_multilingual_feed')) {
					/*yml_catalog->shop->offers->offer->param*/
					$product_attributes = get_the_terms($post->ID, 'product-attributes');
					if ($product_attributes) {
						foreach ($product_attributes as $key => $product_attribut) {
							$parent_name = get_term_field('name', $product_attribut->parent, 'product-attributes');
							if (! is_wp_error($parent_name)) {
								$param = $xml->createElement('param', $product_attribut->name);
								$param->setAttribute("name", $parent_name);
								$offer->appendChild($param);
							}
						}
					}
				}
				/*yml_catalog->shop->offers->offer->picture*/
				$gallery_images = $gallery->gallery_images_url($post->ID);
				if (! empty($gallery_images)) {
					foreach ($gallery_images as $key => $gallery_image) {
						if (is_numeric($gallery_image)) {
							$picture = $xml->createElement('picture', wp_get_attachment_url($gallery_image));
						} else {
							$picture = $xml->createElement('picture',  $gallery_image);
						}
						$offer->appendChild($picture);
					}
				}
			}
		}
		//  сохраняем результат
		echo $xml->saveXML();

		exit;
	}
}
