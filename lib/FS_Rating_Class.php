<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 *Класс рефтинга и статистики
 */
class FS_Rating_Class {
	private static $category_rating_summary_cache = [];


	function __construct() {
		add_action( 'wp_head', array( &$this, 'post_views_counter' ) );
		add_action( 'wp_head', array( &$this, 'viewed_posts' ) );
	}

	function post_views_counter() {

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
		$summary = self::get_category_rating_summary();

		return (float) $summary['average'];
	}


	/**
	 * Count all ratings of all products in the category
	 *
	 * @return int
	 */
	public static function get_count_ratings_in_category() {
		$summary = self::get_category_rating_summary();

		return (int) $summary['count'];
	}

	/**
	 * Aggregate product ratings for the current category with one bulk comment query.
	 *
	 * @return array{count: int, average: float}
	 */
	public static function get_category_rating_summary() {
		$empty_summary = [
			'count'   => 0,
			'average' => 0.0,
		];

		if ( ! fs_is_product_category() ) {
			return $empty_summary;
		}

		$term = get_queried_object();
		if ( ! is_object( $term ) || empty( $term->term_id ) ) {
			return $empty_summary;
		}

		$country      = apply_filters( 'fs_current_country', FS_Config::get_data( 'default_country' ) );
		$language     = function_exists( 'wpm_get_language' ) ? wpm_get_language() : get_query_var( 'lang' );
		$cache_version = FS_Product::get_category_product_summary_cache_version();
		$cache_key     = md5( serialize( [ (int) $term->term_id, (string) $country, (string) $language, $cache_version ] ) );

		if ( array_key_exists( $cache_key, self::$category_rating_summary_cache ) ) {
			return self::$category_rating_summary_cache[ $cache_key ];
		}

		$transient_key = 'fs_fshop_category_rating_' . $cache_key;
		$cached        = get_transient( $transient_key );
		if ( is_array( $cached ) && isset( $cached['count'], $cached['average'] ) ) {
			self::$category_rating_summary_cache[ $cache_key ] = $cached;
			return $cached;
		}

		$product_summary = FS_Product::get_category_product_summary();
		$product_ids     = isset( $product_summary['product_ids'] ) ? $product_summary['product_ids'] : [];
		$comments        = $product_ids ? get_comments( [
			'post__in' => $product_ids,
			'status'   => 'approve',
			'number'   => 0,
			'orderby'  => 'comment_ID',
			'order'    => 'ASC',
		] ) : [];

		$ratings_by_product = [];
		foreach ( $comments as $comment ) {
			$post_id = (int) $comment->comment_post_ID;
			if ( ! isset( $ratings_by_product[ $post_id ] ) ) {
				$ratings_by_product[ $post_id ] = [ 'sum' => 0, 'count' => 0 ];
			}
			$ratings_by_product[ $post_id ]['sum'] += (int) $comment->comment_karma;
			$ratings_by_product[ $post_id ]['count']++;
		}

		$rating_count = 0;
		$rating_sum   = 0.0;
		foreach ( $ratings_by_product as $product_rating ) {
			$rating = round( $product_rating['sum'] / $product_rating['count'], 1 );
			if ( $rating > 0 ) {
				$rating_sum += $rating;
				$rating_count++;
			}
		}

		$summary = [
			'count'   => $rating_count,
			'average' => $rating_count > 0 ? $rating_sum / $rating_count : 0.0,
		];

		self::$category_rating_summary_cache[ $cache_key ] = $summary;
		set_transient( $transient_key, $summary, 86400 );

		return $summary;
	}
}
