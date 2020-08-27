<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 19.08.2017
 * Time: 18:16
 */

namespace FS;


class FS_Product {
	public $variation = null;
	public $id = 0;
	public $item_id = 0;
	public $title;
	public $price;
	public $base_price;
	public $base_price_display;
	public $price_format = '%s <span>%s</span>';
	public $price_display;
	public $currency;
	public $sku;
	public $permalink;
	public $thumbnail;
	public $thumbnail_url;
	public $cost;
	public $cost_display;
	public $count = 1;
	public $attributes = [];
	public $post_type = 'product';


	/**
	 * FS_Product_Class constructor.
	 */
	public function __construct() {
		add_action( 'save_post', array( $this, 'save_product_fields' ), 10, 3 );

		add_action( 'init', array( $this, 'init' ), 12 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'fs_before_product_meta', array( $this, 'before_product_meta' ) );

		/* We set the real price with the discount and currency */
		add_action( 'fs_after_save_meta_fields', array( $this, 'set_real_product_price' ), 10, 1 );

		add_filter( 'pre_get_posts', array( $this, 'pre_get_posts_product' ), 10, 1 );

		// Redirect to a localized url
		add_action( 'template_redirect', array( $this, 'redirect_to_localize_url' ) );

		add_filter( 'post_type_link', [ $this, 'product_link_localize' ], 99, 4 );


	}

	/**
	 * Локализируем ссылки товаров
	 *
	 * @param $post_link
	 * @param $post
	 * @param $leavename
	 * @param $sample
	 *
	 * @return string
	 */
	function product_link_localize( $post_link, $post, $leavename, $sample ) {
	    if (!class_exists('WPGlobus_Utils') && !class_exists('WPGlobus')){
		    return $post_link;
        }

		if ( $post->post_type != $this->post_type || FS_Config::is_default_locale() ) {
			return $post_link;
		}

		$custom_slug = get_post_meta( $post->ID, 'fs_seo_slug__' . get_locale(), 1 );

		if ( ! $custom_slug ) {
			return $post_link;
		}

		$post_link = str_replace( $post->post_name, $custom_slug, $post_link );

		return \WPGlobus_Utils::localize_url( $post_link, \WPGlobus::Config()->language );
	}


	/**
	 * Redirect to a localized url
	 */
	function redirect_to_localize_url() {
		global $post;
		// Leave if the request came not from the product category
		if ( ! is_singular( $this->post_type ) || get_locale() == FS_Config::default_locale() ) {
			return;
		}

		$meta_key = 'fs_seo_slug__' . get_locale();
		$slug     = get_post_meta( $post->ID, $meta_key, 1 );

		if ( ! $slug ) {
			return;
		}

		$uri            = $_SERVER['REQUEST_URI'];
		$uri_components = explode( '/', $uri );
		$lang           = $uri_components[1];

		$localized_url = sprintf( '/%s/%s/%s/', $lang, $this->post_type, $slug );


		if ( $uri !== $localized_url ) {
			wp_redirect( home_url( $localized_url ) );
			exit;
		}

	}

	/**
	 * Micro-marking of product card
	 */
	function product_microdata() {
		$product_taxonomy = FS_Config::get_data( 'post_type' );
		if ( is_admin() || ! is_singular( $product_taxonomy ) ) {
			return;
		}
		global $post;
		$product_id   = intval( $post->ID );
		$categories   = get_the_terms( $product_id, $product_taxonomy );
		$manufacturer = get_the_terms( $product_id, 'brands' );
		$brand        = ! is_wp_error( $manufacturer ) && ! empty( $manufacturer[0]->name ) ? $manufacturer[0]->name : get_bloginfo( 'name' );
		$description  = $post->post_excerpt
			? apply_filters( 'the_content', $post->post_excerpt )
			: apply_filters( 'the_content', $post->post_content );
		$description  = strip_tags( $description );

		$schema = array(
			"@context"     => "https://schema.org",
			"@type"        => "Product",
			"url"          => get_the_permalink(),
			"category"     => ! is_wp_error( $categories ) && ! empty( $categories[0]->name ) ? esc_attr( $categories[0]->name ) : '',
			"image"        => esc_url( fs_get_product_thumbnail_url( 0, 'full' ) ),
			"brand"        => $brand,
			"manufacturer" => $brand,
			"model"        => get_the_title(),
			"sku"          => fs_get_product_code(),
			"mpn"          => fs_get_product_code(),
			"productID"    => $product_id,
			"description"  => $description,
			"name"         => get_the_title( $product_id ),
			"offers"       => [
				"@type"           => "Offer",
				"availability"    => fs_aviable_product() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
				"price"           => fs_get_price(),
				"priceCurrency"   => fs_option( 'fs_currency_code', 'UAH' ),
				"url"             => get_the_permalink(),
				"priceValidUntil" => date( "Y-m-d", strtotime( 'tomorrow' ) ),
			]
		);


		// -->aggregateRating
		$total_vote = get_post_meta( $product_id, 'fs_product_rating' );
		if ( count( $total_vote ) ) {
			$schema["aggregateRating"] = [
				"@type"       => "AggregateRating",
				"ratingCount" => count( $total_vote ),
				"ratingValue" => self::get_average_rating( $product_id )
			];
		}

		// -->review
		$comments = get_comments( [ 'post_id' => $product_id ] );
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				$schema['review'] = [
					"@type"        => "Review",
					"reviewRating" => [
						"@type"       => "Rating",
						"ratingValue" => "5",
						"bestRating"  => "5"
					],
					"author"       => [
						"@type" => "Person",
						"name"  => $comment->comment_author
					]
				];
			}
		}

		echo ' <script type="application/ld+json">';
		echo json_encode( $schema );
		echo ' </script>';

	}

	/**
	 * hook into WP's init action hook
	 */
	public function init() {
		// Initialize Post Type
		$this->create_post_type();


	} // END public function init()


	/**
	 * Получаем пост по мета полю - оно же слаг для любого языка кроме установленого по умолчанию
	 *
	 * @param $query
	 */
	function pre_get_posts_product( $query ) {
		// Если это админка или не главный запрос
		if ( $query->is_admin || ! $query->is_main_query() || ! $query->is_singular ) {
			return $query;
		}

		// Разбиваем текущий урл на компоненты
		$url_components = explode( '/', $_SERVER['REQUEST_URI'] );

		// нам нужно чтобы было как миннимум 4 компонента
		if ( count( $url_components ) < 4 ) {
			return $query;
		}

		$lang      = $url_components[1];
		$post_type = $url_components[2];
		$slug      = $url_components[3];

		if ( $post_type != $this->post_type || empty( $slug ) ) {
			return $query;
		}

		// Получаем ID поста по метаполю
		global $wpdb;
		$meta_key = 'fs_seo_slug__' . get_locale();
		$post_id  = $wpdb->get_var( "SELECT post_id  FROM $wpdb->postmeta WHERE meta_key='$meta_key' AND meta_value='$slug'" );
		if ( ! $post_id ) {
			return $query;
		}

		// Получаем слаг по ID
		$post_name = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE ID=$post_id" );
		if ( $post_name ) {
			$query->set( 'name', $post_name );
			$query->set( 'product', $post_name );
			$query->set( 'post_type', $post_type );
			$query->set( 'do_not_redirect', 1 );
		}

		return $query;

	}

	// Выводит скрытый прелоадер перед полями метабокса
	function before_product_meta() {
		echo '<div class="fs-mb-preloader"></div>';
	}

	/**
	 * Возвращает массив вариаций товара
	 *
	 * @param int $product_id идентификатор товара
	 *
	 * @param bool $hide_disabled не возвращать выключенные
	 *
	 * @return array
	 */
	public static function get_product_variations( $product_id = 0, $hide_disabled = true ) {
		$product_id = fs_get_product_id( $product_id );
		$variations = get_post_meta( $product_id, 'fs_variant', 0 );

		if ( ! empty( $variations[0] ) ) {
			if ( $hide_disabled ) {
				foreach ( $variations[0] as $key => $variation ) {
					if ( ( ! empty( $variation['deactive'] ) && $variation['deactive'] == 1 ) || ( ! empty( $variation['count'] ) && $variation['count'] == 0 ) ) {
						unset( $variations[0][ $key ] );
					}
				}
			}

			return $variations[0];

		} else {
			return array();
		}
	}

	/**
	 * Проверяет является ли вариативным товар
	 * логическая функции
	 * проверка идет по поличию добавленных вариаций товара
	 *
	 * @param int $product_id
	 *
	 * @return bool
	 */
	public function is_variable_product( $product_id = 0 ) {
		$product_id = fs_get_product_id( $product_id );
		$variations = $this->get_product_variations( $product_id );

		return count( $variations ) ? true : false;
	}

	/**
	 * Возвращает массив всех атрибутов товара, которые добавляются в вариациях товара
	 *
	 * @param int $product_id
	 *
	 * @param bool $parents если true, то будут возвращены только родидители
	 *
	 * @return array|int
	 */
	function get_all_variation_attributes( $product_id = 0, $parents = false ) {
		$product_id   = fs_get_product_id( $product_id );
		$variations   = $this->get_product_variations( $product_id );
		$attributes   = [];
		$parents_atts = [];
		if ( ! count( $variations ) ) {
			return $attributes;
		}
		foreach ( $variations as $variation ) {
			if ( ! empty( $variation['attr'] ) && is_array( $variation['attr'] ) ) {
				foreach ( $variation['attr'] as $att ) {
					$att = intval( $att );
					if ( $parents ) {
						$parents_atts_get = get_term_field( 'parent', $att );
						if ( ! is_wp_error( $parents_atts_get ) ) {
							$parents_atts[] = $parents_atts_get;
						}
					} else {
						$attributes [] = $att;
					}

				}

			}
		}

		if ( $parents ) {
			return array_unique( $parents_atts );
		} else {
			return array_unique( $attributes );
		}
	}

	/**
	 * Изменяет запас товаров на складе
	 *  - если $variant == null, то отминусовка идет от общего поля иначе отнимается у поля запаса для указанного варианта
	 *
	 * @param int $product_id
	 * @param int $count - сколько единиц товара будет отминусовано
	 * @param null $variant - если товар вариативный, то здесь нужно указывать номер варианта покупки
	 */
	function fs_change_stock_count( $product_id = 0, $count = 0, $variant = null ) {
		$fs_config = new FS_Config();
		$variants  = $this->get_product_variations( $product_id, false );

		//если указан вариант покупки
		if ( count( $variants ) && ! is_null( $variant ) && is_numeric( $variant ) ) {
			$variants                      = $this->get_product_variations( $product_id, false );
			$variants[ $variant ]['count'] = max( 0, $variants[ $variant ]['count'] - $count );
			update_post_meta( $product_id, $fs_config->meta['variants'], $variants );
		} else {
			// по всей видимости товар не вариативный
			$max_count = get_post_meta( $product_id, $fs_config->meta['remaining_amount'], 1 );
			if ( is_numeric( $count ) && $count != 0 ) {
				$max_count = max( 0, $max_count - $count );
				update_post_meta( $product_id, $fs_config->meta['remaining_amount'], $max_count );
			}

		}

	}


	/**
	 * Calculates the overall rating of the product
	 *
	 * @param int $product_id
	 *
	 * @return float|int
	 */
	public static function get_average_rating( $product_id = 0 ) {
		$product_id = fs_get_product_id( $product_id );
		$rate       = 0;
		$total_vote = get_post_meta( $product_id, 'fs_product_rating' );
		if ( $total_vote ) {
			$sum_votes   = array_sum( $total_vote );
			$count_votes = count( $total_vote );
			$rate        = round( $sum_votes / $count_votes, 2 );
		}

		return $rate;
	}

	/**
	 * Displays the item rating block in the form of icons
	 *
	 * @param int $product_id
	 * @param array $args
	 */
	public function product_rating( $product_id = 0, $args = array() ) {
		$product_id = fs_get_product_id( $product_id );
		$args       = wp_parse_args( $args, array(
			'wrapper_class' => 'fs-rating',
			'stars'         => 5,
			'default_value' => self::get_average_rating( $product_id ),
			'star_class'    => 'fa fa-star'
		) );
		?>
        <div class="<?php echo esc_attr( $args['wrapper_class'] ) ?>">
            <div class="star-rating" data-fs-element="rating">
				<?php if ( $args['stars'] ) {
					for ( $count = 1; $count <= $args['stars']; $count ++ ) {
						if ( $count <= $args['default_value'] ) {
							$star_class = $args['star_class'] . ' active';
						} else {
							$star_class = $args['star_class'];
						}
						echo '<span class="' . esc_attr( $star_class ) . '" data-fs-element="rating-item" data-rating="' . esc_attr( $count ) . '"></span>';
					}
				} ?>
                <input type="hidden" name="fs-rating-value" data-product-id="<?php echo esc_attr( $product_id ) ?>"
                       class="rating-value"
                       value="<?php echo esc_attr( $args['default_value'] ) ?>">
            </div>
        </div>
		<?php
	}

	/**
	 * удаляет все товары
	 *
	 */
	public static function delete_products() {
		$fs_config   = new FS_Config();
		$attachments = true;
		$posts       = new \WP_Query( array(
			'post_type'      => array( $fs_config->data['post_type'] ),
			'posts_per_page' => - 1,
			'post_status'    => 'any'
		) );
		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				global $post;
				if ( $attachments ) {
					$childrens = get_children( array( 'post_type' => 'attachment', 'post_parent' => $post->ID ) );
					if ( $childrens ) {
						foreach ( $childrens as $children ) {
							wp_delete_post( $children->ID, true );
						}
					}
				}
				wp_delete_post( $post->ID, true );
			}
		}
	}

	/**
	 * Возвращает вариативную цену
	 * в случае если поле вариативной цены не заполнено, то будет возвращена обычная цена
	 *
	 * @param int $product_id
	 * @param int $variation_id
	 *
	 * @return float
	 */
	public function get_variation_price( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$variation    = $this->get_variation( $product_id, $variation_id );
		$price        = floatval( $variation['price'] );

		if ( ! empty( $variation['action_price'] ) && $price > floatval( $variation['action_price'] ) ) {
			$price = floatval( $variation['action_price'] );
		}

		return apply_filters( 'fs_price_filter', $product_id, $price );
	}

	/**
	 * Возвращает название товара или его вариации, если указан параметр $variation_id
	 *
	 * @param int $product_id
	 *
	 * @param null $variation_id
	 *
	 * @return string
	 */
	function get_title( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$variation    = $this->get_variation( $product_id, $variation_id );
		$title        = ! empty( $variation['name'] ) ? $variation['name'] : get_the_title( $product_id );

		return $title;
	}

	/**
	 * Возвращает артикул (код производителя)  товара или его вариации, если указан параметр $variation_id
	 *
	 * @param int $product_id
	 * @param null $variation_id
	 *
	 * @return string
	 */
	function get_sku( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$sku          = fs_get_product_code( $product_id );
		if ( ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
			$variations = $this->get_product_variations( $product_id, false );
			if ( ! empty( $variations[ $variation_id ]['sku'] ) ) {
				$sku = $variations[ $variation_id ]['sku'];
			}
		}

		return $sku;
	}

	/**
	 * Отображает артикул товара в корзине
	 *
	 * @param string $format
	 */
	function the_sku( $format = '%s' ) {
		if ( $this->get_sku( $this->id, $this->variation ) ) {
			printf( '<span data-fs-element="sku" class="fs-sku">' . $format . '</span>', esc_html( $this->get_sku() ) );
		}
	}

	/**
	 * Возвращает цену  товара или его вариации, если указан параметр $variation_id
	 *
	 * @param int $product_id
	 * @param null $variation_id
	 *
	 * @return string
	 */
	function get_price( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$price        = fs_get_price( $product_id );

		$variations = $this->get_product_variations( $product_id, true );

		if ( count( $variations ) && ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
			$variation    = $this->get_variation( $product_id, $variation_id );
			$price        = floatval( $variation['price'] );
			$action_price = floatval( $variation['action_price'] );

			// если забыли установить главную цену
			if ( $price == 0 && $action_price > 0 ) {
				$price = $action_price;
			}
			if ( ! empty( $variation['action_price'] ) && $action_price < $price ) {
				$price = $action_price;
			}
			$price = apply_filters( 'fs_price_filter', $product_id, $price );
		}

		return $price;
	}


	/**
	 * Displays the current price of the product for the basket
	 *
	 * @param string $format
	 */
	function the_price( $format = '' ) {
		$format = ! empty( $format ) ? $format : $this->price_format;

		printf( $format, apply_filters( 'fs_price_format', $this->get_price() ), $this->currency );
	}

	/**
	 * Displays the old, base price (provided that the promotional price is established)
	 *
	 * @param string $format
	 */
	function the_base_price( $format = '<del>%s <span>%s</span></del>' ) {
		$format = ! empty( $format ) ? $format : $this->price_format;
		if ( $this->get_base_price() > $this->get_price() ) {
			printf( $format, apply_filters( 'fs_price_format', $this->get_base_price() ), $this->currency );
		}
	}


	/**
	 * Displays the cost of one item of the basket of goods (the quantity multiplied by the price of the 1st product)
	 *
	 * @param string $format
	 */
	function the_cost( $format = '' ) {
		$format = ! empty( $format ) ? $format : $this->price_format;
		printf( $format, esc_html( $this->cost_display ), esc_html( $this->currency ) );
	}

	/**
	 * Displays the item quantifier in the cart
	 *
	 * @param array $args
	 */
	function cart_quantity( $args = array() ) {
		fs_cart_quantity( $this->item_id, $this->count, $args );
	}

	/**
	 * Displays the button for removing goods from the cart
	 *
	 * @param array $args
	 */
	function delete_position( $args = array() ) {
		fs_delete_position( $this->item_id, $args );
	}


	/**
	 * Returns item data as a current class object
	 *
	 * @param array $product
	 *
	 * @param int $item_id
	 *
	 * @return $this
	 */
	public function set_product( $product = [], $item_id = 0 ) {
		$fs_config = new FS_Config();
		$this->setId( intval( $product['ID'] ) );
		$this->set_item_id( $item_id );

		if ( isset( $product['variation'] ) && is_numeric( $product['variation'] ) ) {
			$this->setVariation( $product['variation'] );
			$variation        = $this->get_variation();
			$this->attributes = ! empty( $variation['attr'] ) ? $variation['attr'] : [];
		}

		$this->title              = $this->get_title();
		$this->sku                = $this->get_sku();
		$this->price              = $this->get_price();
		$this->base_price         = $this->get_base_price();
		$this->base_price_display = apply_filters( 'fs_price_format', $this->base_price );
		$this->price_display      = apply_filters( 'fs_price_format', $this->price );
		$this->permalink          = $this->get_permalink();
		$this->count              = floatval( $product['count'] );
		$this->cost               = floatval( $this->count * $this->price );
		$this->cost_display       = apply_filters( 'fs_price_format', $this->cost );
		$this->currency           = fs_currency( $this->id );
		$this->attributes         = [];

		// Если указаны свойства товара
		if ( ! empty( $product['attr'] ) ) {
			foreach ( $product['attr'] as $key => $att ) {
				if ( empty( $att ) ) {
					continue;
				}
				$attribute              = get_term( intval( $att ), $fs_config->data['features_taxonomy'] );
				$attribute->parent_name = '';
				if ( $attribute->parent ) {
					$attribute->parent_name = get_term_field( 'name', $attribute->parent, $fs_config->data['features_taxonomy'] );
				}
				$this->attributes[] = $attribute;
			}
		}

		return $this;

	}

	/**
	 * @return mixed
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns the product variation data as an array
	 *
	 * @param int $product_id
	 * @param null $variation_id
	 *
	 * @return null
	 */
	public function get_variation( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$variation    = [];
		if ( ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
			$variations = $this->get_product_variations( $product_id, false );
			if ( ! empty( $variations[ $variation_id ] ) ) {
				$variation = $variations[ $variation_id ];
			}
		}

		return $variation;
	}

	/**
	 * @param mixed $variation
	 */
	public function setVariation( $variation ) {
		$this->variation = $variation;
	}

	/**
	 * Returns a link to the product
	 *
	 * @param int $product_id
	 *
	 * @return mixed
	 */
	public function get_permalink( $product_id = 0 ) {
		$product_id = $product_id ? $product_id : $this->id;

		return get_the_permalink( $product_id );
	}

	/**
	 * Returns the base price of the item.
	 *
	 * @param int $product_id
	 *
	 * @param null $variation_id
	 *
	 * @return mixed
	 */
	public function get_base_price( $product_id = 0, $variation_id = null ) {
		$fs_config = new FS_Config();

		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;

		$variations = $this->get_product_variations( $product_id, true );

		if ( count( $variations ) && ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
			$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
			$variation    = $this->get_variation( $product_id, $variation_id );
			$base_price   = apply_filters( 'fs_price_filter', $product_id, $variation['price'] );

			return floatval( $base_price );
		} else {
			$price = get_post_meta( $product_id, $fs_config->meta['price'], 1 );

			return apply_filters( 'fs_price_filter', $product_id, $price );
		}
	}

	/**
	 * @param int $item_id
	 */
	public function set_item_id( $item_id = 0 ) {
		$this->item_id = $item_id;
	}

	/**
	 * Выводит вкладки товаров в лицевой части сайта
	 *
	 * @param int $product_id
	 * @param array $args
	 */
	function product_tabs( $product_id = 0, $args = array() ) {
		$product_id = fs_get_product_id( $product_id );

		$args = wp_parse_args( $args, array(
			'wrapper_class'   => 'fs-product-tabs',
			'before'          => '',
			'after'           => '',
			'attributes_args' => array()

		) );

		// Get the comment template
		ob_start();
		comments_template();
		$comments_template = ob_get_clean();

		// Get the product attributes
		ob_start();
		fs_the_atts_list( $product_id, $args['attributes_args'] );
		$attributes = ob_get_clean();

		// Вкладки по умолчанию
		$default_tabs = array(
			'attributes'  => array(
				'title'   => __( 'Characteristic', 'f-shop' ),
				'content' => $attributes
			),
			'description' => array(
				'title'   => __( 'Description', 'f-shop' ),
				'content' => get_the_content()
			),
			'delivery'    => array(
				'title'   => __( 'Shipping and payment', 'f-shop' ),
				'content' => apply_filters( 'the_content', get_post_meta( $product_id, '_fs_delivery_description', 1 ) )
			),
			'reviews'     => array(
				'title'   => __( 'Reviews', 'f-shop' ),
				'content' => $comments_template
			)

		);

		$default_tabs = apply_filters( 'fs_product_tabs_items', $default_tabs, $product_id );

		if ( is_array( $default_tabs ) && ! empty( $default_tabs ) ) {

			$html = '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
			$html .= $args['before'];
			$html .= '<ul class="nav nav-tabs" id="fs-product-tabs-nav" role="tablist">';

			// Display tab switches
			$counter = 0;
			foreach ( $default_tabs as $id => $tab ) {
				$class = ! $counter ? 'active' : '';
				$html  .= '<li class="nav-item">';
				$html  .= '<a class="nav-link ' . esc_attr( $class ) . '" id="fs-product-tab-nav-' . esc_attr( $id ) . '" data-toggle="tab" href="#fs-product-tab-' . esc_attr( $id ) . '" role="tab" aria-controls="' . esc_attr( $id ) . '" aria-selected="true">' . esc_html( $tab['title'] ) . '</a>';
				$html  .= '</li>';
				$counter ++;
			}

			$html .= '</ul><!-- END #fs-product-tabs-nav -->';

			$html .= '<div class="tab-content" id="fs-product-tabs-content">';

			// Display the contents of the tabs
			$counter = 0;
			foreach ( $default_tabs as $id => $tab ) {
				$class = ! $counter ? 'show active' : '';
				$html  .= '<div class="tab-pane fade ' . esc_attr( $class ) . '" id="fs-product-tab-' . esc_attr( $id ) . '" role="tabpanel" aria-labelledby="' . esc_attr( $id ) . '-tab">';
				$html  .= $tab['content'];
				$html  .= '</div>';
				$counter ++;
			}

			$html .= '</div><!-- END #fs-product-tabs-content -->';
			$html .= $args['after'];
			$html .= ' </div><!-- END .product-meta__row -->';

			echo apply_filters( 'fs_product_tabs_html', $html );
		}


	}

	/**
	 * Create the post type
	 */
	public function create_post_type() {
		/* регистрируем тип постов  - товары */
		register_post_type( FS_Config::get_data( 'post_type' ),
			array(
				'labels'             => array(
					'name'               => __( 'Products', 'f-shop' ),
					'singular_name'      => __( 'Product', 'f-shop' ),
					'add_new'            => __( 'Add product', 'f-shop' ),
					'add_new_item'       => '',
					'edit_item'          => __( 'Edit product', 'f-shop' ),
					'new_item'           => '',
					'view_item'          => '',
					'search_items'       => '',
					'not_found'          => '',
					'not_found_in_trash' => '',
					'parent_item_colon'  => '',
					'menu_name'          => __( 'Products', 'f-shop' ),
				),
				'public'             => true,
				'show_in_menu'       => true,
				'yarpp_support'      => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'capability_type'    => 'post',
				'menu_icon'          => 'dashicons-cart',
				'map_meta_cap'       => true,
				'show_in_nav_menus'  => true,
				'show_in_rest'       => true,
				'menu_position'      => 5,
				'can_export'         => true,
				'has_archive'        => true,
				'rewrite'            => apply_filters( 'fs_product_slug', true ),
				'query_var'          => true,
				'taxonomies'         => array( 'catalog', 'product-attributes' ),
				'description'        => __( "Here are the products of your site.", 'f-shop' ),

				'supports' => array(
					'title',
					'editor',
					'excerpt',
					'thumbnail',
					'comments',
					'page-attributes',
					'revisions',
					'gutenburg'
				)
			)
		);
	}

	/**
	 * Save product fields
	 *
	 * @param $post_id
	 */
	function save_product_fields( $post_id, $post, $update ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['post_type'] ) || ( isset( $_POST['post_type'] ) && $_POST['post_type'] != FS_Config::get_data( 'post_type' ) ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		do_action( 'fs_before_save_meta_fields', $post_id );


		$save_meta_keys = $this->get_save_fields();


		foreach ( $save_meta_keys as $key => $field_name ) {
			$name = $key;
			if ( is_array( $field_name ) && ! empty( $field_name['multilang'] ) ) {
				$name = $key . '__' . get_locale();
			}

			// Skip fields that do not exist in the global variable $_POST
			if ( ! isset( $_POST[ $name ] ) ) {
				delete_post_meta( $post_id, $name );
				continue;
			}

			// Modify the saved field through the filter'fs_filter_meta_field'
			$value = apply_filters( 'fs_filter_meta_field', $_POST[ $name ], $name, $post );
			$value = apply_filters( 'fs_filter_meta_field__' . $field_name, $value, $name, $post );

			update_post_meta( $post_id, $name, $value );
		}

		do_action( 'fs_after_save_meta_fields', $post_id );
	}

	/**
	 * hook into WP's admin_init action hook
	 */
	public function admin_init() {
		// Add metaboxes
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
	} // END public function admin_init()

	/**
	 * hook into WP's add_meta_boxes action hook
	 */
	public function add_meta_boxes() {

		remove_meta_box( 'order-statusesdiv', 'orders', 'side' );
		// Add this metabox to every selected post
		add_meta_box(
			sprintf( 'fast_shop_%s_metabox', FS_Config::get_data( 'post_type' ) ),
			__( 'Product settings', 'f-shop' ),
			array( &$this, 'add_inner_meta_boxes' ),
			FS_Config::get_data( 'post_type' ),
			'normal',
			'high'
		);

		// добавляем метабокс списка товаров в заказе
		add_meta_box(
			sprintf( 'fast_shop_%s_metabox', 'orders' ),
			__( 'List of products', 'f-shop' ),
			array( &$this, 'add_order_products_meta_boxes' ),
			'orders',
			'normal',
			'high'
		);

		// добавляем метабокс списка товаров в заказе
		add_meta_box(
			sprintf( 'fast_shop_%s_user_metabox', 'orders' ),
			__( 'Customer data', 'f-shop' ),
			array( &$this, 'add_order_user_meta_boxes' ),
			'orders',
			'normal',
			'default'
		);
		// Add this metabox to every selected post

	} // END public function add_meta_boxes()


	/**
	 *  Registers new metafields dynamically from tabs
	 *
	 * @param bool $data если поставить true до будут добавлены дополнительные данные к полю
	 *
	 * @return array
	 */
	function get_save_fields( $data = false ) {
		$product_tabs = $this->get_product_tabs();
		$meta_fields  = [];

		foreach ( $product_tabs as $product_tab ) {
			if ( empty( $product_tab['fields'] ) ) {
				continue;
			}
			foreach ( $product_tab['fields'] as $key => $field ) {
				$key = apply_filters( 'fs_product_tab_admin_meta_key', $key, $field );
				if ( $data ) {
					$meta_fields[ $key ] = $key;
				} else {
					$meta_fields[ $key ] = $key;
				}


			}
		}

		return array_merge( FS_Config::get_meta(), $meta_fields );
	}

	/**
	 * Gets the array that contains the list of product settings tabs.
	 *
	 * @return array
	 */
	public static function get_product_tabs() {
		$product_fields = FS_Config::get_product_field();
		$tabs           = array(
			'basic'        => array(
				'title'       => __( 'Basic', 'f-shop' ),
				'on'          => true,
				'description' => __( 'In this tab you can adjust the prices of goods.', 'f-shop' ),
				'fields'      => array(
					FS_Config::get_meta( 'price' )        => array(
						'label'      => __( 'Base price', 'f-shop' ),
						'type'       => 'number',
						'attributes' => [
							'min'  => 0,
							'step' => 0.01
						],
						'help'       => __( 'This is the main price on the site. Required field!', 'f-shop' )
					),
					FS_Config::get_meta( 'action_price' ) => array(
						'label' => __( 'Promotional price', 'f-shop' ),
						'type'  => 'number',
						'help'  => __( 'If this field is filled, the base price loses its relevance. But you can display it on the site.', 'f-shop' )
					),

					$product_fields['sku']['key']      => $product_fields['sku'],
					$product_fields['quantity']['key'] => $product_fields['quantity'],
					FS_Config::get_meta( 'currency' )  => array(
						'label'    => __( 'Item Currency', 'f-shop' ),
						'on'       => fs_option( 'multi_currency_on' ) ? true : false,
						'type'     => 'dropdown_categories',
						'help'     => __( 'The field is active if you have enabled multicurrency in settings.', 'f-shop' ),
						'taxonomy' => FS_Config::get_data( 'currencies_taxonomy' )
					),
				)
			),
			'gallery'      => array(
				'title'    => __( 'Gallery', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'gallery'
			),
			'attributes'   => array(
				'title'    => __( 'Attributes', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'attributes'
			),
			'related'      => array(
				'title'    => __( 'Associated', 'f-shop' ),
				'on'       => false, // Сейчас в разработке
				'body'     => '',
				'template' => 'related'
			),
			'up_sell'      => array(
				'title'    => __( 'Up-sell', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'up-sell'
			),
			'cross_sell'   => array(
				'title'    => __( 'Cross-sell', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'cross-sell'
			),
			'variants'     => array(
				'title'    => __( 'Variation', 'f-shop' ),
				'on'       => true,
				'body'     => '',
				'template' => 'variants'
			),
			'delivery'     => array(
				'title'  => __( 'Shipping and payment', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					'_fs_delivery_description' => array(
						'label' => __( 'Shipping and Payment Details', 'f-shop' ),
						'type'  => 'editor',
						'help'  => ''
					),

				)
			),
			'seo'          => array(
				'title'  => __( 'SEO', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					'fs_seo_slug' => array(
						'label'     => __( 'SEO slug', 'f-shop' ),
						'type'      => 'text',
						'multilang' => true,
						'help'      => __( 'Allows you to set multilingual url', 'f-shop' )
					),

				)
			),
			'additionally' => array(
				'title'  => __( 'Additionally', 'f-shop' ),
				'on'     => true,
				'body'   => '',
				'fields' => array(
					$product_fields['exclude_archive']['key']  => $product_fields['exclude_archive'],
					$product_fields['label_bestseller']['key'] => $product_fields['label_bestseller'],
					$product_fields['label_promotion']['key']  => $product_fields['label_promotion'],
					$product_fields['label_novelty']['key']    => $product_fields['label_novelty'],

				)
			),
		);

		return apply_filters( 'fs_product_tabs_admin', $tabs );
	}

	/**
	 * called off of the add meta box
	 *
	 * @param $post
	 */
	public function add_inner_meta_boxes( $post ) {
		$form_class       = new FS_Form();
		$product_tabs     = self::get_product_tabs();
		$this->product_id = $post->ID;
		$cookie           = isset( $_COOKIE['fs_active_tab'] ) ? $_COOKIE['fs_active_tab'] : 'prices';
		echo '<div class="fs-metabox" id="fs-metabox">';
		do_action( 'fs_before_product_meta' );
		if ( count( $product_tabs ) ) {
			echo '<ul class="tab-header">';
			foreach ( $product_tabs as $key => $tab ) {
				if ( ! $tab['on'] ) {
					continue;
				}
				if ( $cookie ) {
					if ( $cookie == $key ) {
						$class = 'fs-link-active';
					} else {
						$class = '';
					}
				} else {
					$class = $key == 0 ? 'fs-link-active' : '';
				}
				echo '<li class="' . esc_attr( $class ) . '"><a href="#tab-' . esc_attr( $key ) . '" data-tab="' . esc_attr( $key ) . '">' . esc_html__( $tab['title'], 'f-shop' ) . '</a></li>';
			}
			echo '</ul>';
			echo "<div class=\"fs-tabs\">";
			foreach ( $product_tabs as $key_body => $tab_body ) {
				if ( ! $tab_body['on'] ) {
					continue;
				}

				if ( $key_body == $cookie ) {
					$class_tab = 'fs-tab-active';
				} else {
					$class_tab = '';
				}


				echo '<div class="fs-tab ' . esc_attr( $class_tab ) . '" id="tab-' . esc_attr( $key_body ) . '">';
				if ( ! empty( $tab_body['fields'] ) ) {
					if ( ! empty( $tab_body['title'] ) ) {
						echo '<h3>' . esc_html( $tab_body['title'] ) . '</h3>';
					}
					if ( ! empty( $tab_body['description'] ) ) {
						echo '<p class="description">' . esc_html( $tab_body['description'] ) . '</p>';
					}
					foreach ( $tab_body['fields'] as $key => $field ) {
						$filter_meta[ $key ] = $key;
					}

					foreach ( $tab_body['fields'] as $key => $field ) {
						// если у поля есть атрибут "on" и он выключён то выходим из цикла
						if ( isset( $field['on'] ) && $field['on'] != true ) {
							continue;
						}
						// если не указан атрибут type
						if ( empty( $field['type'] ) ) {
							$field['type'] = 'text';
						}
						echo '<div class="fs-field-row clearfix">';

						$key            = apply_filters( 'fs_product_tab_admin_meta_key', $key, $field );
						$field['value'] = get_post_meta( $post->ID, $key, true );
						$form_class->render_field( $key, $field['type'], $field );
						echo '</div>';
					}
				} elseif ( ! empty( $tab_body['template'] ) ) {
					$template_file = sprintf( FS_PLUGIN_PATH . 'templates/back-end/metabox/%s.php', $tab_body['template'] );
					if ( file_exists( $template_file ) ) {
						include( $template_file );
					} else {
						esc_html_e( 'Template file not found', 'f-shop' );
					}
				} elseif ( ! empty( $tab_body['body'] ) ) {
					echo $tab_body['body'];
				}
				echo '</div>';

			}
			echo "</div>";
			echo '<div class="clearfix"></div>';

		}
		?>
        <!-- The modal / dialog box, hidden somewhere near the footer -->
        <div id="fs-upsell-dialog" class="hidden fs-select-products-dialog" style="max-width:420px">
			<?php
			$args  = array(
				'posts_per_page' => - 1,
				'post_type'      => FS_Config::get_data( 'post_type' )
			);
			$query = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				echo '<ul>';
				while ( $query->have_posts() ) {
					$query->the_post();
					the_title( '<li><span>', '</span><button class="button add-product" data-id="' . esc_attr( get_the_ID() ) . '" data-field="fs_up_sell" data-name="' . esc_attr( get_the_title() ) . '">' . esc_html__( 'choose', 'f-shop' ) . '</button></li>' );
				}
				echo '</ul>';
			}
			wp_reset_query();
			?>
        </div>
		<?php
		echo '</div>';
	}

	/* метабокс списка товаров в редактировании заказа */
	public function add_order_products_meta_boxes( $post ) {
		$products = get_post_meta( $post->ID, '_products', 0 );
		$products = ! empty( $products[0] ) ? $products[0] : [];
		$amount   = get_post_meta( $post->ID, '_amount', 1 );
		$amount   = apply_filters( 'fs_price_format', $amount ) . ' ' . fs_currency();
		require FS_PLUGIN_PATH . 'templates/back-end/metabox/order/meta-box-0.php';
	}

	/* метабокс данных пользователя в редактировании заказа */
	public function add_order_user_meta_boxes( $post ) {
		$user     = get_post_meta( $post->ID, '_user', 0 );
		$user     = $user[0];
		$payment  = get_post_meta( $post->ID, '_payment', 1 );
		$delivery = get_post_meta( $post->ID, '_delivery', 0 );
		$delivery = $delivery[0];

		require FS_PLUGIN_PATH . 'templates/back-end/metabox/order/meta-box-1.php';
	}

	public function set_real_product_price( $product_id = 0 ) {
		update_post_meta( $product_id, '_fs_real_price', fs_get_price( $product_id ) );
	}

}