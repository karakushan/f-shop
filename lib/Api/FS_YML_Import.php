<?php

namespace FS\Api;

use FS\FS_Config;
use WP_CLI;
use WP_CLI\ExitException;

class FS_YML_Import {
	private static $external_id_meta_key = 'fs_external_id';
	private static $feed_id = 0;
	private static $skip_exists = false;
	private static $type = 'api';
	private static $limit = null;


	/**
	 * This method is triggered when calling the WP CLI command
	 *
	 * @param $args
	 *
	 * @return void
	 * @throws ExitException
	 */
	public static function run( $args ) {
		if ( ! isset( $args['feed_id'] ) || ! is_numeric( $args['feed_id'] ) ) {
			WP_CLI::error( __( 'Invalid parameter feed_id', 'f-shop' ) );
		}

		self::$feed_id     = $args['feed_id'];
		self::$skip_exists = $args['skip_exists'] ?? false;
		self::$limit       = $args['limit'] ?? null;
		self::$type        = 'cli';

		self::import();
	}

	/**
	 * This method fires on an HTTP request
	 *
	 * @param $request
	 *
	 * @return void
	 */
	public static function handle( $request ) {
		self::$feed_id     = $request->get_param( 'feed_id' );
		self::$skip_exists = $request->get_param( 'skip_exists' ) ?? false;
		self::$limit       = $request->get_param( 'limit' ) ?? null;
		self::$type        = 'api';

		self::import();
	}

	/**
	 * Исполняемый метод который выполняет всю работу по импорте с XML
	 *
	 * @param $request
	 *
	 * @return void
	 */
	public static function import() {
		{
			$feed_id     = self::$feed_id;
			$skip_exists = self::$skip_exists;

			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			if ( ! $feed_id || ! is_numeric( $feed_id ) ) {
				wp_send_json_error( [ 'message' => __( 'Invalid parameter feed_id', 'f-shop' ) ] );
			}

			$feed_term = get_term( $feed_id, FS_Config::get_data( 'drop_taxonomy' ) );
			if ( ! $feed_term ) {
				wp_send_json_error( [ 'message' => __( 'Feed term not found', 'f-shop' ) ] );
			}

			global $wpdb;

			$feed_url = get_term_meta( $feed_term->term_id, 'fs_drop_price_url', true );

			$xml = simplexml_load_file( $feed_url );

			if ( $xml === false ) {
				wp_send_json_error( [ 'message' => __( 'XML file not found', 'f-shop' ) ] );
			}

			$include_categories = explode( ',', get_term_meta( $feed_term->term_id, 'fs_drop_include_categories', true ) );

			// Categories
			foreach ( $xml->shop->categories->category as $category ) {
				$external_id   = (int) $category['id'];
				$external_name = (string) $category;

				if ( ! empty( $include_categories ) && ! in_array( $external_id, $include_categories ) ) {
					continue;
				}

				self::make_category( $external_name, 0, $external_id );
			}

			// Products
			$count = 0;
			if ( self::$type == 'cli' ) {
				WP_CLI::log( sprintf( __( 'Feed contains %d elements', 'f-shop' ), $xml->shop->offers->children()->count() ) );
				$total    = self::$limit && is_numeric( self::$limit ) ? intval( self::$limit ) : $xml->shop->offers->children()->count();
				$progress = \WP_CLI\Utils\make_progress_bar( sprintf( __( 'Importing %s elements', 'f-shop' ), $total ), $total );
			}

			foreach ( $xml->shop->offers->offer as $offer ) {
				if ( self::$limit && is_numeric( self::$limit ) && $count >= intval( self::$limit ) ) {
					break;
				}

				$external_id = (int) $offer['id'];
				$internal_id = $wpdb->get_var( "SELECT post_id FROM $wpdb->postmeta WHERE meta_value = $external_id AND meta_key = '" . self::$external_id_meta_key . "' LIMIT 1" );

				// Skip existing
				if ( $skip_exists && $internal_id ) {
					continue;
				}

				$category_id = (int) $offer->categoryId;
				if ( ! empty( $include_categories ) && ! in_array( $category_id, $include_categories ) ) {
					continue;
				}

				$internal_cat_id    = $wpdb->get_var( "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = $category_id AND meta_key = '" . self::$external_id_meta_key . "' LIMIT 1" );
				$product_categories = [];
				if ( $internal_cat_id ) {
//					$product_categories[] = $internal_cat_id;
				}


				$name              = (string) $offer->name;
				$name_ua           = (string) $offer->name_ua;
				$in_stock          = (string) $offer['available'] === 'true';
				$description       = (string) $offer->description;
				$description_ua    = (string) $offer->description_ua;
				$price             = (float) $offer->price;
				$drop_price        = (float) $offer->drp;
				$sub_category      = (string) $offer->sub_category;
				$quantity_in_stock = (int) $offer->quantity_in_stock;
				$country_of_origin = (string) $offer->country_of_origin;
				$vendorCode        = (string) $offer->vendorCode;

				$post_data = [
					'post_title'   => sprintf( '[:ua]%s[:ru]%s[:]', $name_ua, $name ),
					'post_content' => sprintf( '[:ua]%s[:ru]%s[:]', $description_ua, $description ),
					'post_status'  => 'publish',
					'post_type'    => FS_Config::get_data( 'post_type' ),
				];

				$post_data['ID'] = $internal_id ?? 0;

				$product_ID = wp_insert_post( $post_data );

				if ( is_wp_error( $product_ID ) ) {
					continue;
				}

				if ( ! empty( $sub_category ) ) {
					$sub_category_id = self::make_category( $sub_category, $internal_cat_id ?? 0, $external_id );

					if ( is_numeric( $sub_category_id ) ) {
						$product_categories[] = (int) $sub_category_id;
					}

				}

				update_post_meta( $product_ID, self::$external_id_meta_key, $external_id );
				update_post_meta( $product_ID, FS_Config::get_meta( 'price' ), $price );
				update_post_meta( $product_ID, 'fs_drop_price', $drop_price );
				update_post_meta( $product_ID, FS_Config::get_meta( 'remaining_amount' ), $quantity_in_stock );
				update_post_meta( $product_ID, FS_Config::get_meta( 'country_of_origin' ), $country_of_origin );
				update_post_meta( $product_ID, FS_Config::get_meta( 'sku' ), $vendorCode );

				if ( is_array( $product_categories ) ) {
					$attached_categories=wp_set_object_terms( $product_ID, $product_categories, FS_Config::get_data('product_taxonomy'),  false );
					if (is_wp_error($attached_categories)) {
						WP_CLI::log($attached_categories->get_error_message());
					}
				}

				if ( ! empty( $offer->picture ) ) {
					$gallery         = [];
					$uploaded_photos = (array) get_post_meta( $product_ID, 'fs_drop_uploaded_photos', 1 ) ?? [];
					foreach ( $offer->picture as $key => $picture ) {
						if ( in_array( (string) $picture, $uploaded_photos ) ) {
							continue;
						}

						$image_id = \media_sideload_image( (string) $picture, ( ! $key ? $product_ID : 0 ), '', 'id' );

						if ( is_wp_error( $image_id ) || ! is_numeric( $image_id ) ) {
							continue;
						}

						if ( $key === 0 ) {
							set_post_thumbnail( $product_ID, $gallery[0] );
						} else {
							$gallery[] = $image_id;
						}

						$uploaded_photos[] = (string) $picture;
					}
					update_post_meta( $product_ID, 'fs_galery', $gallery );
					update_post_meta( $product_ID, 'fs_drop_uploaded_gallery', $uploaded_photos );
				}

				if ( self::$type == 'cli' ) {
					$progress->tick();
				}
				$count ++;
			}
			if ( self::$type == 'cli' ) {
				$progress->finish();
			}
			$message = sprintf( __( 'Imported %d products', 'f-shop' ), $count );

			if ( self::$type == 'cli' ) {
				WP_CLI::success( $message );
			} else {
				wp_send_json_success( [ 'message' => $message, 'url' => $feed_url ] );
			}
		}
	}

	private static function make_category( $name, $parent_id = 0, $external_id = 0 ) {
		$term = term_exists( $name, FS_Config::get_data( 'product_taxonomy' ) );
		if ( isset( $term['term_id'] ) ) {
			return $term['term_id'];
		} else {
			$term = wp_insert_term( $name, FS_Config::get_data( 'product_taxonomy' ) );
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( $term['term_id'], self::$external_id_meta_key, $external_id );
			}

			return $term['term_id'];
		}
	}


}