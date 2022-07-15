<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 *Класс рефтинга и статистики
 */
class FS_Rating_Class {

	function __construct() {
		add_action( 'wp_head', array( &$this, 'kama_postviews' ) );
		add_action( 'wp_head', array( &$this, 'viewed_posts' ) );
	}


	function kama_postviews() {

		/* ------------ Настройки -------------- */
		$meta_key     = 'views';  // Ключ мета поля, куда будет записываться количество просмотров.
		$who_count    = 0;            // Чьи посещения считать? 0 - Всех. 1 - Только гостей. 2 - Только зарегистрированных пользователей.
		$exclude_bots = 0;            // Исключить ботов, роботов, пауков и прочую нечесть :)? 0 - нет, пусть тоже считаются. 1 - да, исключить из подсчета.

		global $user_ID, $post;
		if ( is_singular() ) {
			$id = (int) $post->ID;
			static $post_views = false;
			if ( $post_views ) {
				return true;
			} // чтобы 1 раз за поток
			$post_views   = (int) get_post_meta( $id, $meta_key, true );
			$should_count = false;
			switch ( (int) $who_count ) {
				case 0:
					$should_count = true;
					break;
				case 1:
					if ( (int) $user_ID == 0 ) {
						$should_count = true;
					}
					break;
				case 2:
					if ( (int) $user_ID > 0 ) {
						$should_count = true;
					}
					break;
			}
			if ( (int) $exclude_bots == 1 && $should_count ) {
				$useragent = $_SERVER['HTTP_USER_AGENT'];
				$notbot    = "Mozilla|Opera"; //Chrome|Safari|Firefox|Netscape - все равны Mozilla
				$bot       = "Bot/|robot|Slurp/|yahoo"; //Яндекс иногда как Mozilla представляется
				if ( ! preg_match( "/$notbot/i", $useragent ) || preg_match( "!$bot!i", $useragent ) ) {
					$should_count = false;
				}
			}

			if ( $should_count ) {
				if ( ! update_post_meta( $id, $meta_key, ( $post_views + 1 ) ) ) {
					add_post_meta( $id, $meta_key, 1, true );
				}
			}
		}

		return true;
	}


	/**
	 * Метод позволяет зафиксировать в сессию $_SESSION['fs_user_settings']['viewed_product'] массив айдишников просмотренных товаровы
	 * @return bool
	 */
	function viewed_posts() {
		if ( is_singular() ) {
			global $post;
			$id                                               = (int) $post->ID;
			$_SESSION['fs_user_settings']['viewed_product'][] = $id;
			$_SESSION['fs_user_settings']['viewed_product']   = array_unique( $_SESSION['fs_user_settings']['viewed_product'] );
		}

		return true;
	}


	/**
	 * Get average rating of all products in the category
	 *
	 * @return float
	 */
	public static function get_average_rating_in_category() {
		$average_rating = 0;
		if ( fs_is_product_category() ) {
			$taxonomy = FS_Config::get_data( 'product_taxonomy' );
			$term     = get_queried_object();
			$args     = [
				'post_type'      => FS_Config::get_data( 'post_type' ),
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
				$count = 0;
				$sum   = 0;
				while ( $query->have_posts() ) {
					$query->the_post();
					$rating = FS_Product::get_average_rating( get_the_ID() );
					if ( $rating > 0 ) {
						$sum += $rating;
						$count ++;
					}
				}
				$average_rating = $sum / $count;
			}
			wp_reset_postdata();
		}

		return floatval( $average_rating );
	}


	/**
	 * Count all ratings of all products in the category
	 *
	 * @return int
	 */
	public static function get_count_ratings_in_category() {
		$count_ratings = 0;
		if ( fs_is_product_category() ) {
			$taxonomy = FS_Config::get_data( 'product_taxonomy' );
			$term     = get_queried_object();
			$args     = [
				'post_type'      => FS_Config::get_data( 'post_type' ),
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
					$rating        = FS_Product::get_average_rating( get_the_ID() );
					if ( $rating > 0 ) {
						$count_ratings ++;
					}
				}
			}
			wp_reset_postdata();
		}

		return intval( $count_ratings );
	}
}
