<?php

namespace FS;
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class FS_Export_Class {
	public $feed_name = 'fs-yml-export';
	public static $base_price;
	public static $action_price;

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'http_get_export' ) );
		add_action( 'init', array( $this, 'products_feed' ) );

	}

	/**
	 * Создание XML фида товаров
	 */
	function products_feed() {
		add_feed( $this->feed_name, array( $this, 'products_to_yml' ) );
	}

	/**
	 * Экспорт товаров по GET запросу
	 */
	function http_get_export() {
		if ( ! isset( $_GET['fs-yml-export'] ) ) {
			return;
		}

		$this->products_to_yml();


	}


	function products_to_yml( $admin_notices = false ) {
		global $fs_config;

		header( 'Content-type: text/xml' );

		$xml                     = new \DomDocument( '1.0', get_bloginfo( 'charset' ) );
		$xml->formatOutput       = true;
		$xml->preserveWhiteSpace = false;


		$gallery            = new FS_Images_Class();
		self::$base_price   = apply_filters( 'fs_export_base_price', 'price' );
		self::$action_price = apply_filters( 'fs_export_action_price', 'action_price' );


		/*yml_catalog*/
		$yml_catalog = $xml->createElement( 'yml_catalog' );
		$yml_catalog->setAttribute( "date", date( 'Y-m-d H:i' ) );
		$xml->appendChild( $yml_catalog );
		/*yml_catalog->shop*/
		$shop = $xml->createElement( 'shop' );
		$yml_catalog->appendChild( $shop );
		/*yml_catalog->shop->name*/
		$shop_name = $xml->createElement( 'name', get_bloginfo( 'name' ) );
		$shop->appendChild( $shop_name );
		/*yml_catalog->shop->company*/
		$shop_company = $xml->createElement( 'company', fs_option( 'company_name', get_bloginfo( 'name' ) ) );
		$shop->appendChild( $shop_company );
		/*yml_catalog->shop->url*/
		$shop_url = $xml->createElement( 'url', get_bloginfo( 'url' ) );
		$shop->appendChild( $shop_url );
		/*yml_catalog->shop->currencies*/
		$currencies = $xml->createElement( 'currencies' );
		$shop->appendChild( $currencies );
		/*yml_catalog->shop->currencies->currency*/
		$currency = $xml->createElement( 'currency' );
		$currency->setAttribute( "id", 'UAH' );
		$currency->setAttribute( 'rate', '1' );
		$currencies->appendChild( $currency );

		//  КАТЕГОРИИ
		/*yml_catalog->shop->currencies*/
		$categories = $xml->createElement( 'categories' );
		$shop->appendChild( $categories );
		/*yml_catalog->shop->category*/
		$terms = get_terms( array( 'taxonomy' => 'catalog', 'hide_empty' => false ) );
		if ( $terms ) {
			foreach ( $terms as $key => $term ) {
				$category = $xml->createElement( 'category', $term->name );
				$category->setAttribute( "id", $term->term_id );
				if ( $term->parent ) {
					$category->setAttribute( "parentId", $term->parent );
				}
				$categories->appendChild( $category );
			}
		}

		//  ТОВАРЫ
		/*yml_catalog->shop->offers*/
		$offers = $xml->createElement( 'offers' );
		$shop->appendChild( $offers );

		$posts = get_posts( array( 'post_type' => $fs_config->data['post_type'], 'posts_per_page' => - 1 ) );
		if ( $posts ) {
			foreach ( $posts as $key => $post ) {
				setup_postdata( $post );
				$offer_id = apply_filters( 'fs_product_id', $post->ID );
				/*yml_catalog->shop->offers->offer*/
				$offer = $xml->createElement( 'offer' );

				$offer->setAttribute( "id", $offer_id );
				$offer->setAttribute( "available", 'true' );
				$offers->appendChild( $offer );
				/*yml_catalog->shop->offers->offer->url*/
				$url = $xml->createElement( 'url', get_permalink( $post->ID ) );
				$offer->appendChild( $url );

				/*yml_catalog->shop->offers->offer->price*/

				$price_site        = apply_filters( 'fs_export_price', fs_get_base_price( $post->ID ) );
				$price_action_site = apply_filters( 'fs_export_price_promo', fs_get_price( $post->ID ) );


				if ( get_post_meta( $post->ID, $fs_config->meta['action_price'], true ) && $price_action_site < $price_site ) {
					/*yml_catalog->shop->offers->offer->price*/
					$price = $xml->createElement( 'price', $price_action_site );
					$offer->appendChild( $price );

					/*yml_catalog->shop->offers->offer->oldprice*/
					$oldprice = $xml->createElement( 'oldprice', $price_site );
					$offer->appendChild( $oldprice );
				} else {
					/*yml_catalog->shop->offers->offer->price*/
					$price = $xml->createElement( 'price', $price_site );
					$offer->appendChild( $price );
				}

				/*yml_catalog->shop->offers->offer->currencyId*/
				$currencyId = $xml->createElement( 'currencyId', 'UAH' );
				$offer->appendChild( $currencyId );
				/*yml_catalog->shop->offers->offer->name*/
				$name = $xml->createElement( 'name', get_the_title( $post->ID ) );
				$offer->appendChild( $name );
				/*yml_catalog->shop->offers->offer->vendorCode*/
				$vendorCode = $xml->createElement( 'vendorCode', fs_product_code( $post->ID ) );
				$offer->appendChild( $vendorCode );
				/*yml_catalog->shop->offers->offer->description*/
				$description = $xml->createElement( 'description', sanitize_text_field( $post->post_content ) );
				$offer->appendChild( $description );


				/*yml_catalog->shop->offers->offer->categoryId*/
				$product_terms = get_the_terms( $post->ID, 'catalog' );
				if ( $product_terms ) {
					$count_terms = 0;
					foreach ( $product_terms as $key => $product_term ) {
						$count_terms ++;
						if ( $count_terms > 1 ) {
							break;
						}
						$categoryId = $xml->createElement( 'categoryId', $product_term->term_id );
						$offer->appendChild( $categoryId );

					}
				}
				/*yml_catalog->shop->offers->offer->param*/
				$product_attributes = get_the_terms( $post->ID, 'product-attributes' );
				if ( $product_attributes ) {
					foreach ( $product_attributes as $key => $product_attribut ) {
						$parent_name = get_term_field( 'name', $product_attribut->parent, 'product-attributes' );
						if ( ! is_wp_error( $parent_name ) ) {
							$param       = $xml->createElement( 'param', $product_attribut->name );
							$parent_name = get_term_field( 'name', $product_attribut->parent, 'product-attributes' );
							$param->setAttribute( "name", $parent_name );
							$offer->appendChild( $param );
						}

					}
				}
				/*yml_catalog->shop->offers->offer->picture*/
				$gallery_images = $gallery->gallery_images_url( $post->ID );
				if ( ! empty( $gallery_images ) ) {
					foreach ( $gallery_images as $key => $gallery_image ) {
						if ( is_numeric( $gallery_image ) ) {
							$picture = $xml->createElement( 'picture', wp_get_attachment_url( $gallery_image ) );
						} else {
							$picture = $xml->createElement( 'picture',  $gallery_image  );
						}
						$offer->appendChild( $picture );
					}
				}

			}
		}
		//  сохраняем результат
		echo $xml->saveXML();

		exit;
	}

}
