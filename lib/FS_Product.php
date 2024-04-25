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
	private $price_format = '%s <span>%s</span>';
	public $price_display;
	public $currency;
	public $sku;
	public $permalink;
	public $thumbnail_url;
	public $cost;
	public $cost_display;
	public $count = 1;
	public $attributes = [];
	public $post_type = 'product';
	public $product_id = null;


	/**
	 * FS_Product_Class constructor.
	 */
	public function __construct() {
//		add_action( 'save_post', array( $this, 'save_product_fields' ), 10, 3 );
		$this->post_type = FS_Config::get_data( 'post_type' );
	}

	public static function product_comment_likes( $comment_id = 0 ) {
		$comment_like_count = (int) get_comment_meta( $comment_id, 'fs_like_count', 1 ); ?>
        <div class="comment-like" data-fs-element="comment-like"
             data-comment-id="<?php echo esc_attr( $comment_id ); ?>">
            <i class="icon icon-like"></i> <span class="comment-like__count"
                                                 data-fs-element="comment-like-count"><?php echo esc_html( $comment_like_count ); ?></span>
        </div>
	<?php }


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

		$args = wp_parse_args( $args, array(
			'wrapper_class'     => 'fs-rating',
			'before'            => '',
			'after'             => '',
			'stars'             => 5,
			'default_value'     => self::get_average_rating( $product_id ),
			'star_class'        => 'far fa-star',
			'star_active_class' => 'fas fa-star',
			'echo'              => true
		) );
		if ( ! $args['echo'] ) {
			ob_start();
		}
		?>
        <div class="<?php echo esc_attr( $args['wrapper_class'] ) ?>">
			<?php if ( $args['before'] )
				echo $args['before'] ?>
            <div class="star-rating" data-fs-element="rating">
				<?php if ( $args['stars'] ) {
					for ( $count = 1; $count <= $args['stars']; $count ++ ) {
						if ( $count <= $args['default_value'] ) {
							$star_class = $args['star_active_class'] . ' active';
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
			<?php if ( $args['after'] )
				echo $args['after'] ?>
        </div>
		<?php
		if ( ! $args['echo'] ) {
			return ob_get_clean();
		}
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

		return apply_filters( 'fs_price_filter', $price, $product_id );
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
	 * Displays the current price of the product for the basket
	 *
	 * @param string $format
	 */
	function the_price( $format = '%s <span>%s</span>' ) {
		$format = ! empty( $format ) ? $format : $this->price_format;

		printf( '<span class="fs-price">' . $format . '</span>', apply_filters( 'fs_price_format', $this->price ), $this->currency );
	}

	/**
	 * Displays the old, base price (provided that the promotional price is established)
	 *
	 * @param string $format
	 */
	function the_base_price( $format = '%s <span>%s</span>' ) {
		$format = ! empty( $format ) ? $format : $this->price_format;
		if ( $this->base_price > $this->price ) {
			printf( '<del class="fs-base-price">' . $format . '</del>', apply_filters( 'fs_price_format', $this->base_price ), $this->currency );
		}
	}


	/**
	 * Displays the cost of one item of the basket of goods (the quantity multiplied by the price of the 1st product)
	 *
	 * @param string $format
	 */
	function the_cost( $format = '%s %s' ) {
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

		$this->title              = ! empty( $product['name'] ) ? apply_filters( 'the_title', $product['name'] ) : $this->get_title();
		$this->sku                = $this->get_sku();
		$this->price              = fs_get_price( $this->id );
		$this->base_price         = fs_get_base_price( $this->id );
		$this->base_price_display = apply_filters( 'fs_price_format', $this->base_price );
		$this->price_display      = apply_filters( 'fs_price_format', $this->price );
		$this->permalink          = $this->get_permalink();
		$this->count              = floatval( $product['count'] );
		$this->cost               = floatval( $this->count * $this->price );
		$this->cost_display       = apply_filters( 'fs_price_format', $this->cost );
		$this->currency           = fs_currency( $this->id );
		$this->attributes         = [];
		$this->thumbnail_url      = has_post_thumbnail( $this->id ) ? get_the_post_thumbnail_url( $this->id ) : null;


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
	 * Выводит ссылку на странцу товара
	 *
	 * @param int $product_id
	 */
	public function the_permalink( $product_id = 0 ) {
		echo esc_url( $this->get_permalink( $product_id ) );
	}

	/**
	 * @param int $item_id
	 */
	public function set_item_id( $item_id = 0 ) {
		$this->item_id = $item_id;
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

		$save_meta_keys = self::get_product_tabs();

		if ( ! empty( $save_meta_keys ) ) {
			foreach ( $save_meta_keys as $fields ) {
				if ( empty( $fields['fields'] ) ) {
					continue;
				}

				foreach ( $fields['fields'] as $key => $field ) {
					$is_multilang = isset( $field['multilang'] ) && $field['multilang'] && fs_option( 'fs_multi_language_support' );
					if ( ! in_array( $field['type'], [ 'checkbox' ] ) && $is_multilang ) {
						foreach ( FS_Config::get_languages() as $code => $language ) {
							$meta_key = $key . '__' . $language['locale'];
							if ( ( isset( $_POST[ $meta_key ] ) && $_POST[ $meta_key ] != '' )
							     || ( $key == 'fs_seo_slug' && isset( $_POST[ $meta_key ] ) ) ) {
								$value = apply_filters( 'fs_transform_meta_value', $_POST[ $meta_key ], $key, $code, $post_id );
								update_post_meta( $post_id, $key . '__' . $language['locale'], $value );
							} else {
								delete_post_meta( $post_id, $key . '__' . $language['locale'] );
							}
						}
					} else {
						if ( isset( $_POST[ $key ] ) && $_POST[ $key ] != '' ) {
							$value = apply_filters( 'fs_transform_meta_value', $_POST[ $key ], $key, null, $post_id );
							update_post_meta( $post_id, $key, $value );
						} else {
							delete_post_meta( $post_id, $key );
						}
					}
				}
			}
		}

		// Set the number of views for a new product 0
		if ( ! get_post_meta( $post_id, 'views', 1 ) ) {
			update_post_meta( $post_id, 'views', 0 );
		}

		// Set product features
		if ( ! empty( $_POST['fs_product_attributes'] ) && is_array( $_POST['fs_product_attributes'] ) ) {
			wp_set_post_terms( $post_id, array_map( 'intval', $_POST['fs_product_attributes'] ), FS_Config::get_data( 'features_taxonomy' ), true );
		} else {
			wp_set_post_terms( $post_id, array(), FS_Config::get_data( 'features_taxonomy' ), false );
		}

		do_action( 'fs_after_save_meta_fields', $post_id );
	}


	/**
	 *  Выводит диалог для выбора товаров upsell
	 *
	 * todo: эта функция замедляет загрузку формы редактирования товара, нужно сделать подгрузку товаров ajax
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	function after_product_tabs( \WP_Post $post ) {
		?>
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
					the_title( '<li><span>', '</span><button class="button add-product" data-id="' . esc_attr( $post->ID ) . '" data-field="fs_up_sell" data-name="' . esc_attr( get_the_title() ) . '">' . esc_html__( 'choose', 'f-shop' ) . '</button></li>' );
				}
				echo '</ul>';
			}
			wp_reset_query();
			?>
        </div>

        <div class="fs-mb-preloader"></div>
		<?php
	}


	public function the_title() {
		echo esc_html( $this->get_title() );
	}

	public function the_thumbnail( $size = 'thumbnail', $args = array() ) {
		fs_product_thumbnail( $this->getId(), $size, $args );
	}

	public function getCount() {
		return (int) $this->count;
	}

	/**
	 * Выводит форму отзывов, комментариев на странице товара
	 *
	 * @return void
	 */
	public static function product_comments_form(): void {
		echo fs_frontend_template( 'product/tabs/comments' );
	}

	// get minimal price of all products in the category
	public static function get_min_price_in_category() {
		$min_price = 0;
		if ( fs_is_product_category() ) {
			$taxonomy = \FS\FS_Config::get_data( 'product_taxonomy' );
			$term     = get_queried_object();
			$args     = [
				'post_type'      => \FS\FS_Config::get_data( 'post_type' ),
				'posts_per_page' => - 1,
				'tax_query'      => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					]
				]
			];
			$query    = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$price = fs_get_price();
					if ( $price > 0 && $price < $min_price || $min_price == 0 ) {
						$min_price = $price;
					}
				}
			}
			wp_reset_postdata();
		}

		return floatval( $min_price );
	}

// get max price of all products in the category
	public static function get_max_price_in_category() {
		$max_price = 0;
		if ( fs_is_product_category() ) {
			$taxonomy = \FS\FS_Config::get_data( 'product_taxonomy' );
			$term     = get_queried_object();
			$args     = [
				'post_type'      => \FS\FS_Config::get_data( 'post_type' ),
				'posts_per_page' => - 1,
				'tax_query'      => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					]
				]
			];
			$query    = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$price = fs_get_price();
					if ( $price && $price > $max_price ) {
						$max_price = $price;
					}
				}
			}
			wp_reset_postdata();
		}

		return floatval( $max_price );
	}

// count all products in the category
	public static function get_count_products_in_category() {
		$count = 0;
		if ( fs_is_product_category() ) {
			$taxonomy = \FS\FS_Config::get_data( 'product_taxonomy' );
			$term     = get_queried_object();
			$args     = [
				'post_type'      => \FS\FS_Config::get_data( 'post_type' ),
				'posts_per_page' => - 1,
				'tax_query'      => [
					[
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $term->term_id,
					]
				]
			];
			$query    = new \WP_Query( $args );
			if ( $query->have_posts() ) {
				$count = $query->post_count;
			}
			wp_reset_postdata();
		}

		return intval( $count );
	}

	/**
	 * Выводит вкладки товаров в лицевой части сайта
	 *
	 * @param int $product_id
	 * @param array $args
	 */
	public static function product_tabs( $product_id = 0, $args = array() ) {
		$product_id = fs_get_product_id( $product_id );

		$args = wp_parse_args( $args, array(
			'wrapper_class'   => 'fs-product-tabs',
			'before'          => '',
			'after'           => '',
			'attributes_args' => array()

		) );

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
				'content' => apply_filters( 'the_content', get_the_content() )
			),
			'delivery'    => array(
				'title'   => __( 'Shipping and payment', 'f-shop' ),
				'content' => fs_frontend_template( 'product/tabs/delivery' )
			),
			'reviews'     => array(
				'title'   => __( 'Reviews', 'f-shop' ),
				'content' => fs_frontend_template( 'product/tabs/comments' )
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
				$class = ! $counter ? ' active' : '';
				$html  .= '<li class="nav-item ' . $class . '">';
				$html  .= '<a class="nav-link' . esc_attr( $class ) . '" id="fs-product-tab-nav-' . esc_attr( $id ) . '" data-toggle="tab" href="#fs-product-tab-' . esc_attr( $id ) . '" role="tab" aria-controls="' . esc_attr( $id ) . '" aria-selected="true">' . esc_html( $tab['title'] ) . '</a>';
				$html  .= '</li>';
				$counter ++;
			}

			$html .= '</ul><!-- END #fs-product-tabs-nav -->';

			$html .= '<div class="tab-content" id="fs-product-tabs-content">';

			// Display the contents of the tabs
			$counter = 0;
			foreach ( $default_tabs as $id => $tab ) {
				$class = ! $counter ? ' active' : '';
				$html  .= '<div class="tab-pane' . esc_attr( $class ) . '" id="fs-product-tab-' . esc_attr( $id ) . '" role="tabpanel" aria-labelledby="' . esc_attr( $id ) . '-tab">';
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
	 * Returns a list of product attributes as a hierarchical list
	 *
	 * @param $post_id
	 *
	 * @return array
	 */
	public static function get_attributes_hierarchy( $post_id ) {
		$tax        = FS_Config::get_data( 'features_taxonomy' );
		$attributes = get_the_terms( $post_id, $tax );

		if ( ! is_array( $attributes ) || empty( $attributes ) || is_wp_error($attributes)) {
			return [];
		}

		$parents = [];
		$parents_ids=[];
		foreach ( $attributes as $attribute ) {
            if ( !$attribute->parent  && !in_array($attribute->term_id, $parents_ids)) {
				$parents[] = $attribute;
	            $parents_ids[]=$attribute->term_id;
				continue;
			}

            if ($attribute->parent  && !in_array($attribute->parent, $parents_ids)) {
	            $parent=get_term( $attribute->parent );
	            if(is_wp_error($parent)) {
		            continue;
	            }
	            $parents[] = $parent;
	            $parents_ids[]=$parent->term_id;
            }

		}

		$groped = array_map( function ( $attribute ) use ( $attributes, $tax ) {
			$children = array_values( array_filter( $attributes, function ( $child ) use ( $attribute ) {
				return $child->parent === $attribute->term_id;
			} ) );

			return [
				'id'           => $attribute->term_id,
				'name'         =>str_replace(['\'','"'],['ʼ'], $attribute->name),
				'parent'       => $attribute->parent,
				'children'     => array_map( function ( $child ) {
					return [
						'id'     => $child->term_id,
						'name'   => str_replace(['\'','"'],['ʼ'],$child->name),
						'parent' => $child->parent,
					];
				}, $children ),
				'children_all' => array_map( function ( $child ) {
					return [
						'id'     => $child->term_id,
						'name'   => str_replace(['\'','"'],['ʼ'],$child->name),
						'parent' => $child->parent,
					];
				}, get_terms( [ 'parent' => $attribute->term_id, 'hide_empty' => false, 'taxonomy' => $tax ] ) )
			];
		}, $parents );

		return array_values( $groped );
	}
}