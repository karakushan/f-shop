<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 19.08.2017
 * Time: 18:16
 */

namespace FS;


class FS_Product_Class {
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


	/**
	 * FS_Product_Class constructor.
	 */
	public function __construct() {

		/** set the global variable $fs_product */
		$GLOBALS['fs_product'] = $this;
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
	function get_product_variations( $product_id = 0, $hide_disabled = true ) {
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
		global $fs_config;
		$variants = $this->get_product_variations( $product_id, false );

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
	public static function get_vote_counting( $product_id = 0 ) {
		$product_id = fs_get_product_id( $product_id );
		$rate       = 0;
		$total_vote = get_post_meta( $product_id, 'fs_product_rating', 0 );
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
			'default_value' => self::get_vote_counting( $product_id ),
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
		global $fs_config;
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
			printf( $format, esc_html( $this->get_sku() ) );
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


		if ( ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
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
	 * @param array $args
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
		global $fs_config;
		$this->setId( intval( $product['ID'] ) );
		$this->set_item_id( $item_id );
		$this->attributes = ! empty( $product['atts'] ) ? $product['atts'] : [];

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
		$this->count              = intval( $product['count'] );
		$this->cost               = floatval( $this->count * $this->price );
		$this->cost_display       = apply_filters( 'fs_price_format', $this->cost );
		$this->currency           = fs_currency( $this->id );

		// Если указаны свойства товара
		$attributes = [];
		if ( ! empty( $this->attributes ) ) {

			foreach ( $this->attributes as $key => $att ) {
				if ( empty( $att ) ) {
					continue;
				}
				$attribute              = get_term( intval( $att ), $fs_config->data['product_att_taxonomy'] );
				$attribute->parent_name = '';
				if ( $attribute->parent ) {
					$attribute->parent_name = get_term_field( 'name', $attribute->parent, $fs_config->data['product_att_taxonomy'] );
				}
				$attributes[] = $attribute;
			}
		}
		$this->attributes = $attributes;

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
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$variation    = $this->get_variation( $product_id, $variation_id );
		$base_price   = apply_filters( 'fs_price_filter', $product_id, $variation['price'] );

		return floatval( $base_price );
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
			'wrapper_class' => 'fs-product-tabs'
		) );

		// Get the comment template
		ob_start();
		comments_template();
		$comments_template = ob_get_clean();

		// Вкладки по умолчанию
		$default_tabs = array(
			'attributes'  => array(
				'title'   => __( 'Characteristic', 'f-shop' ),
				'content' => ''
			),
			'description' => array(
				'title'   => __( 'Description', 'f-shop' ),
				'content' => get_the_content()
			),
			'delivery'    => array(
				'title'   => __( 'Shipping and payment', 'f-shop' ),
				'content' => ''
			),
			'reviews'     => array(
				'title'   => __( 'Reviews', 'f-shop' ),
				'content' => $comments_template
			)

		);

		$default_tabs = apply_filters( 'fs_product_tabs_items', $default_tabs );

		if ( is_array( $default_tabs ) && ! empty( $default_tabs ) ) {

			$html = '<div class="' . esc_attr( $args['wrapper_class'] ) . '">';
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

			$html .= ' </div><!-- END .product-meta__row -->';

			echo apply_filters( 'fs_product_tabs_html', $html );
		}


	}
}