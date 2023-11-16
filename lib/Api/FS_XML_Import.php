<?php

namespace FS\Api;

use FS\FS_Config;

class FS_XML_Import {
	private static $external_id_meta_key = 'fs_external_id';

	public static function handle( $request ) {
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/image.php' );

		$feed_id = $request->get_param( 'feed_id' );

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
		foreach ( $xml->shop->offers->offer as $offer ) {
			$category_id = (int) $offer->categoryId;
			if ( ! empty( $include_categories ) && ! in_array( $category_id, $include_categories ) ) {
				continue;
			}

			$internal_cat_id    = $wpdb->get_var( "SELECT term_id FROM $wpdb->termmeta WHERE meta_value = $category_id AND meta_key = '" . self::$external_id_meta_key . "' LIMIT 1" );
			$product_categories = [];
			if ( $internal_cat_id ) {
				$product_categories[] = $internal_cat_id;
			}
			$external_id       = (int) $offer['id'];
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

			$internal_id     = $wpdb->get_var( "SELECT post_id FROM $wpdb->termmeta WHERE meta_value = $external_id AND meta_key = '" . self::$external_id_meta_key . "' LIMIT 1" );
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
			update_post_meta( $product_ID, FS_Config::get_meta( 'fs_drop_price' ), $drop_price );
			update_post_meta( $product_ID, FS_Config::get_meta( 'fs_remaining_amount' ), $quantity_in_stock );
			update_post_meta( $product_ID, FS_Config::get_meta( 'fs_country_of_origin' ), $country_of_origin );
			update_post_meta( $product_ID, FS_Config::get_meta( 'fs_articul' ), $vendorCode );

			if ( is_array( $product_categories ) ) {
				wp_set_post_categories( $product_ID, $product_categories );
			}

			if ( ! empty( $offer->picture ) ) {
				$gallery = [];
				foreach ( $offer->picture as $key => $picture ) {
					$image_id = \media_sideload_image( (string) $picture, ( ! $key ? $product_ID : 0 ) );

					if ( is_wp_error( $image_id ) ) {
						echo $image_id->get_error_message();
						continue;
					}

					if ( $key === 0 ) {
						set_post_thumbnail( $product_ID, $gallery[0] );
					} else {
						$gallery[] = $image_id;
					}
				}
				update_post_meta( $product_ID, 'fs_galery', $gallery );
			}

			break;
		}

		wp_send_json_success( [ 'message' => __( 'Imported', 'f-shop' ), 'url' => $feed_url ] );
	}

	private function make_category( $name, $parent_id = 0, $external_id = 0 ) {
		if ( ! term_exists( $name, FS_Config::get_data( 'product_taxonomy' ) ) ) {
			$term = wp_insert_term( $name, FS_Config::get_data( 'product_taxonomy' ) );
			if ( ! is_wp_error( $term ) ) {
				update_term_meta( $term['term_id'], self::$external_id_meta_key, $external_id );
			}

			return $term['term_id'];
		}
	}
}