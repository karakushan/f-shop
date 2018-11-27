<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 19.08.2017
 * Time: 18:16
 */

namespace FS;


class FS_Product_Class {

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
					if (
						( ! empty( $variation['deactive'] ) && $variation['deactive'] == 1 )
						||
						( ! empty( $variation['count'] ) && $variation['count'] == 0 ) ) {
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

}