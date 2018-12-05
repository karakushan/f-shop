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
	public $title;
	public $price;
	public $price_format = '%d <span>%s</span>';
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
					if ( ! empty( $variation['deactive'] && $variation['deactive'] == 1 ) || ( ! empty( $variation['count'] ) && $variation['count'] == 0 ) ) {
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
	 * Изменяет запас товаров на складе
	 *  - если $variant == null, то отминусовка идет от общего поля иначе отнимается у поля запаса для указанного варианта
	 *
	 * @param int $product_id
	 * @param  int $count - сколько единиц товара будет отминусовано
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
	public function get_vote_counting( $product_id = 0 ) {
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
	public static function product_rating( $product_id = 0, $args = array() ) {
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
	 * @param bool $attachments - удалять вложения или нет (по умолчанию удаляет)
	 */
	public static function delete_products() {
		global $fs_config;
		$attachments = true;
		$posts       = new \WP_Query( array(
			'post_type'      => array( $fs_config->data['post_type'] ),
			'posts_per_page' => - 1
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
	public function get_variation_price( $product_id = 0, $variation_id ) {
		$product_id         = $product_id ? $product_id : $this->id;
		$variation_id       = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$product_variations = $this->get_product_variations( $product_id, false );
		if ( ! empty( $product_variations[ $variation_id ] ) ) {
			$base_price   = ! empty( $product_variations[ $variation_id ]['price'] ) ? floatval( $product_variations[ $variation_id ]['price'] ) : 0;
			$action_price = ! empty( $product_variations[ $variation_id ]['action_price'] ) ? floatval( $product_variations[ $variation_id ]['action_price'] ) : 0;
			if ( $action_price > 0 && $action_price < $base_price ) {
				$base_price = $action_price;
			}
			$base_price = apply_filters( 'fs_price_filter', $product_id, $base_price );

			return $base_price;
		} else {
			return fs_get_price( $product_id );
		}
	}

	/**
	 * Возвращает название товара или его вариации, если указан параметр $variation_id
	 *
	 * @param int $product_id
	 * @param null $variation_id
	 *
	 * @return string
	 */
	function get_title( $product_id = 0, $variation_id = null ) {
		$product_id   = $product_id ? $product_id : $this->id;
		$variation_id = ! is_null( $variation_id ) && is_numeric( $variation_id ) ? $variation_id : $this->variation;
		$title        = get_the_title( $product_id );
		if ( ! is_null( $variation_id ) && is_numeric( $variation_id ) ) {
			$variations = $this->get_product_variations( $product_id, false );
			if ( ! empty( $variations[ $variation_id ]['name'] ) ) {
				$title = $variations[ $variation_id ]['name'];
			}
		}

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
			$price = $this->get_variation_price( $product_id, $variation_id );
		}

		return $price;
	}


	/**
	 * Возвращает данные товара в виде объекта текущего класа
	 *
	 * @param array $product
	 *
	 * @return $this
	 */
	public function set_product( $product = [] ) {
		global $fs_config;
		$this->setId( intval( $product['ID'] ) );

		$this->attributes = $product['atts'];
		if ( isset( $product['variation'] ) && is_numeric( $product['variation'] ) ) {
			$this->setVariation( $product['variation'] );
			$variation        = $this->get_variation();
			$this->attributes = ! empty( $variation['attr'] ) ? $variation['attr'] : [];
		}

		$this->title         = $this->get_title();
		$this->sku           = $this->get_sku();
		$this->price         = $this->get_price();
		$this->price_display = apply_filters( 'fs_price_format', $this->price );
		$this->permalink     = $this->get_permalink();
		$this->count         = intval( $product['count'] );
		$this->cost          = floatval( $this->count * $this->price );
		$this->cost_display  = apply_filters( 'fs_price_format', $this->cost );
		$this->currency      = fs_currency();

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
	 * Возваращает данные вариации товара в виде массива
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
	 * Возвращает ссылку на товар
	 *
	 * @param int $product_id
	 *
	 * @return mixed
	 */
	public function get_permalink( $product_id = 0 ) {
		$product_id = $product_id ? $product_id : $this->id;

		return get_the_permalink( $product_id );
	}
}