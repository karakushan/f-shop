<?php


namespace FS;


class FS_SEO {
	function __construct() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Adds microdata
		// todo: Проверить и добавить кеширование в хуках ниже
		add_action( 'fs_organization_microdata', [ $this, 'schema_organization_microdata' ] );
		add_action( 'fs_product_reviews_microdata', [ $this, 'product_reviews_microdata' ] );
		add_action( 'fs_product_category_microdata', [ $this, 'product_category_microdata' ] );

		// Outputs microdata of type LocalBusiness
		add_action( 'fs_local_business_microdata', [ $this, 'schema_local_business_microdata' ] );

		// Allows you to register events for Google Adwords remarketing
		add_action( 'fs_adwords_remarketing', [ $this, 'adwords_remarketing' ] );

		// TODO: лучше создать специальную папку с интеграциями с другими плагинами и подключать классы с случае если плагин активен
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			// Change wordpress seo canonical
			add_filter( 'wpseo_canonical', array( $this, 'replace_products_canonical' ), 10, 1 );

		}

		add_filter( 'document_title_parts', array( $this, 'meta_title_filter' ), 10, 1 );

		add_action( 'wp_footer', [ $this, 'scripts_in_footer' ] );
		add_action( 'wp_head', [ $this, 'scripts_in_head' ] );

		// Micro-marking of product card
		add_action( 'wp_head', array( $this, 'product_microdata' ) );

		add_filter( 'fs_transform_meta_value', [ $this, 'transform_meta_value' ], 10, 3 );
	}

	/**
	 * Change wordpress seo canonical
	 *
	 * @param $canonical
	 *
	 * @return string|string[]
	 */
	function replace_products_canonical( $canonical ) {
		$taxonomy_name = FS_Config::get_data( 'product_taxonomy' );
		if ( is_tax( $taxonomy_name ) && fs_option( 'fs_disable_taxonomy_slug' ) ) {
			$canonical = get_term_link( get_queried_object_id(), $taxonomy_name );
		} elseif ( is_singular( FS_Config::get_data( 'post_type' ) ) && get_locale() != FS_Config::default_locale() ) {
			global $post;
			$slug = get_post_meta( $post->ID, 'fs_seo_slug__' . get_locale(), 1 );
			if ( $slug ) {
				$canonical = site_url( sprintf( '%s/%s/%s/', 'ua', FS_Config::get_data( 'post_type' ), $slug ) );
			}
		}

		return $canonical;
	}

	/**
	 * Изменяет meta title
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function meta_title_filter( $title ) {
		if ( fs_is_catalog() && fs_option( '_fs_catalog_meta_title' ) != '' ) {
			$title['title'] = esc_attr( apply_filters( 'the_title', fs_option( '_fs_catalog_meta_title' ) ) );
			$title['site']  = '';
		} elseif ( fs_is_product_category() ) {
			$term_id        = get_queried_object_id();
			$meta_title     = get_term_meta( $term_id, fs_localize_meta_key( '_seo_title' ), 1 ) ?: get_term_meta( $term_id, '_seo_title', 1 );
			$title['title'] = esc_attr( apply_filters( 'the_title', $meta_title ) );
			$title['site']  = '';
		}

		$title['title'] = apply_filters( 'fs_meta_title', $title['title'] );

		return $title;
	}

	/**
	 * Добавляет meta description
	 */
	public function meta_description_action() {
		$meta_description = '';

		// Если посетитель находится на странице архива товаров
		if ( fs_is_catalog() && fs_option( '_fs_catalog_meta_description' ) != '' ) {
			$meta_description = fs_option( '_fs_catalog_meta_description' );
		} elseif ( fs_is_product_category() ) { // Если посетитель находится на странице таксономии товаров
			$meta_description = fs_get_term_meta( '_seo_description' );
		}

		$meta_description = apply_filters( 'fs_meta_description', $meta_description );

		if ( $meta_description ) {
			echo PHP_EOL . '<meta name="description" content="' . esc_attr( apply_filters( 'the_title', $meta_description ) ) . '"/>' . PHP_EOL;
		}
	}

	/**
	 * Выводит скрипты в шапке
	 */
	public function scripts_in_head() {
		if ( fs_is_catalog() || fs_is_product_category() ) {
			$this->meta_description_action();
		}

		do_action( 'fs_local_business_microdata' );
		do_action( 'fs_organization_microdata' );
		do_action( 'fs_product_category_microdata' );
		if ( is_singular( FS_Config::get_data( 'post_type' ) ) ) {
			do_action( 'fs_product_reviews_microdata' );
		}
	}


	/**
	 * Выводит скрипты в футере
	 */
	public function scripts_in_footer() {

		if ( fs_option( '_fs_adwords_remarketing' ) ) {
			do_action( 'fs_adwords_remarketing' );
		}
	}

	/**
	 * Sends the custom dimension to Google Analytics
	 */
	public function adwords_remarketing() {
		$page_cart             = fs_option( 'page_cart' );
		$page_checkout         = fs_option( 'page_checkout' );
		$page_checkout_success = fs_option( 'page_success' );
		?>
		<script>
			<?php if (is_front_page()): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'home',
                'ecomm_totalvalue': ''
            });

			<?php  elseif ($page_cart && is_page( [ $page_cart ] )): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'cart',
                'ecomm_totalvalue': <?php  echo esc_attr( fs_get_total_amount() ) ?>
            });
			<?php  elseif ($page_checkout && is_page( [ $page_checkout ] )): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': '',
                'ecomm_pagetype': 'cart',
                'ecomm_totalvalue': <?php  echo esc_attr( fs_get_total_amount() ) ?>
            });
			<?php  elseif ($page_checkout_success && is_page( $page_checkout_success )):
			$order = new FS_Orders();
			?>
            gtag('event', 'page_view', {
                'ecomm_prodid': <?php echo esc_attr( $order->get_last_order_id() ) ?>,
                'ecomm_pagetype': 'purchase',
                'ecomm_totalvalue': <?php echo esc_attr( $order->get_last_order_amount() ); ?>
            });
			<?php  elseif (is_singular( FS_Config::get_data( 'post_type' ) )): ?>
            gtag('event', 'page_view', {
                'ecomm_prodid': <?php the_ID(); ?>,
                'ecomm_pagetype': 'product',
                'ecomm_totalvalue':  <?php echo esc_attr( fs_get_price( get_the_ID() ) ); ?>
            });
			<?php endif; ?>
		</script>
		<?php
	}

	/**
	 * Выводит микроразметку отзывов товара
	 */
	public function product_reviews_microdata() {
		$comments = get_comments( [ 'post_id' => get_the_ID(), 'comment_parent' => 0 ] );
		foreach ( $comments as $comment ) {
			$average_rating = FS_Product::get_average_rating( $comment->comment_post_ID );
			$total_votes    = get_post_meta( $comment->comment_post_ID, 'fs_product_rating' );
			echo '<script type=\'application/ld+json\'>';
			echo json_encode( [
				"@context"     => "http://www.schema.org",
				"@type"        => 'Review',
				"name"         => __( 'Product Reviews', 'f-shop' ),
				"author"       => [
					'@type' => 'Person',
					"name"  => $comment->comment_author
				],
				"reviewBody"   => strip_tags( $comment->comment_content ),
				"itemReviewed" => [
					"@type" => "Product",
					"name"  => get_the_title( $comment->comment_post_ID ),

					"offers"          => [
						"@type"         => "AggregateOffer",
						"lowPrice"      => fs_get_price(),
						"highPrice"     => fs_get_price(),
						"priceCurrency" => "UAH",
						"offerCount"    => 1
					],
					"description"     => strip_tags( get_the_excerpt( $comment->comment_post_ID ) ),
					"image"           => get_the_post_thumbnail_url( $comment->comment_post_ID ) ?: '',
					"aggregateRating" => [
						"@type"       => "AggregateRating",
						"ratingValue" => $average_rating ?: 5,
						"reviewCount" => is_array( $total_votes ) && count( $total_votes ) > 0 ? count( $total_votes ) : 1
					],
					"sku"             => fs_get_product_code( $comment->comment_post_ID )
				]
			] );
			echo '</script>';
		}
	}

	/**
	 * Добавляет микроразметку типа Огранизация
	 *
	 * @see https://schema.org/Organization
	 */
	public function schema_organization_microdata() {
		if ( ! ( is_front_page() || is_home() ) ) {
			return;
		}

		$custom_logo_id  = get_theme_mod( 'custom_logo' );
		$custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : ' ';
		$micro_data      = [
			"@context"     => "http://www.schema.org",
			"@type"        => 'Organization',
			"name"         => fs_option( 'contact_name', get_bloginfo( 'name' ) ),
			"url"          => home_url( '/' ),
			"logo"         => $custom_logo_url,
			"contactPoint" => [
				"@type"             => "ContactPoint",
				"telephone"         => fs_option( 'contact_phone' ),
				"contactType"       => "customer service",
				"contactOption"     => "TollFree",
				"areaServed"        => "UA",
				"availableLanguage" => "Ukrainian"
			],
		];

		$micro_data = apply_filters( 'fs_schema_organization_microdata', $micro_data );

		echo '<script type=\'application/ld+json\'>';
		echo json_encode( $micro_data );
		echo '</script>';
	}

	/**
	 * Displays the micro-layout of the store on the main
	 */
	public function schema_local_business_microdata() {
		if ( ! ( is_front_page() || is_home() ) ) {
			return;
		}

		$custom_logo_id  = get_theme_mod( 'custom_logo' );
		$custom_logo_url = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : ' ';
		$micro_data      = [
			"@context"     => "http://www.schema.org",
			"@type"        => fs_option( 'contact_type', 'LocalBusiness' ),
			"priceRange"   => "$$",
			"name"         => fs_option( 'contact_name', get_bloginfo( 'name' ) ),
			"url"          => home_url( '/' ),
			"logo"         => $custom_logo_url,
			"image"        => $custom_logo_url,
			"description"  => get_bloginfo( 'description' ),
			"address"      => [
				"@type"           => "PostalAddress",
				"streetAddress"   => fs_option( 'contact_address' ),
				"addressLocality" => fs_option( 'contact_city' ),
				"postalCode"      => fs_option( 'contact_zip' ),
				"addressCountry"  => fs_option( 'contact_country' )
			],
			"openingHours" => fs_option( 'opening_hours' ),
			"telephone"    => fs_option( 'contact_phone' )
		];


		$micro_data = apply_filters( 'fs_schema_local_business_microdata', $micro_data );

		echo '<script type=\'application/ld+json\'>';
		echo json_encode( $micro_data );
		echo '</script>';
	}

	/**
	 * Micro-marking of product card
	 */
	function product_microdata() {
		if ( ! is_singular( FS_Config::get_data( 'post_type' ) ) ) {
			return;
		}
		global $post;
		$product_taxonomy       = FS_Config::get_data( 'product_taxonomy' );
		$product_id             = $post->ID;
		$categories             = get_the_terms( $product_id, $product_taxonomy );
		$manufacturer           = get_the_terms( $product_id, 'brands' );
		$brand                  = ! is_wp_error( $manufacturer ) && ! empty( $manufacturer[0]->name ) ? $manufacturer[0]->name : get_bloginfo( 'name' );
		$description            = $post->post_excerpt
			? apply_filters( 'the_content', $post->post_excerpt )
			: apply_filters( 'the_content', $post->post_content );
		$description            = strip_tags( $description );
		$total_votes            = get_post_meta( $product_id, 'fs_product_rating' );
		$product_average_rating = get_post_meta( $product_id, 'fs_product_average_rating' );

		$schema = array(
			"@context"        => "https://schema.org",
			"@type"           => "Product",
			"url"             => get_the_permalink(),
			"category"        => ! is_wp_error( $categories ) && ! empty( $categories[0]->name ) ? esc_attr( $categories[0]->name ) : '',
			"image"           => esc_url( fs_get_product_thumbnail_url( 0, 'full' ) ),
			"brand"           => $brand,
			"manufacturer"    => $brand,
			"model"           => get_the_title(),
			"sku"             => fs_get_product_code(),
			"mpn"             => fs_get_product_code(),
			"productID"       => $product_id,
			"description"     => $description ?: __( 'No description', 'f-shop' ),
			"name"            => get_the_title( $product_id ),
			"aggregateRating" => [
				"@type"       => "AggregateRating",
				"ratingCount" => count( $total_votes ) ? count( $total_votes ) : 1,
				"ratingValue" => $product_average_rating ?: 5
			],
			"offers"          => [
				"@type"                   => "Offer",
				"availability"            => fs_in_stock() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
				"price"                   => fs_get_price(),
				"priceCurrency"           => fs_option( 'fs_currency_code', 'UAH' ),
				"url"                     => get_the_permalink(),
				"priceValidUntil"         => date( "Y-m-d", strtotime( 'tomorrow' ) ),
				"hasMerchantReturnPolicy" => [
					"@type"                    => "MerchantReturnPolicy",
					"refundType"               => "FullRefund",
					"applicableCountry"        => "UA",
					"returnPolicyCategory"     => "https://schema.org/MerchantReturnFiniteReturnWindow",
					"returnPolicyDuration"     => [
						"@type"    => "QuantitativeValue",
						"value"    => 14,
						"unitCode" => "DAY"
					],
					"returnMethod"             => "https://schema.org/ReturnByMail",
					"returnPolicyLabel"        => "14 дней",
					"merchantReturnDays"       => 14,
					"returnShippingFeesAmount" => [
						"@type"    => "MonetaryAmount",
						"currency" => fs_option( 'fs_currency_code', 'UAH' ),
						"value"    => 0
					]
				],
				"shippingDetails"         => [
					"@type"               => "OfferShippingDetails",
					"shippingRate"        => [
						"@type"    => "MonetaryAmount",
						"currency" => fs_option( 'fs_currency_code', 'UAH' ),
						"value"    => fs_option( 'fs_min_shipping_cost', 70 )
					],
					"shippingDestination" =>
						[
							"@type"          => "DefinedRegion",
							"addressCountry" => "UA"
						],
					"deliveryTime"        => [
						"@type"        => "ShippingDeliveryTime",
						"transitTime"  => [
							"@type"    => "QuantitativeValue",
							"minValue" => 1,
							"maxValue" => 5,
							"unitCode" => "DAY"
						],
						"handlingTime" => [
							"@type"    => "QuantitativeValue",
							"minValue" => 1,
							"maxValue" => 2,
							"unitCode" => "DAY"
						],
						"businessDays" => [
							"@type"     => "OpeningHoursSpecification",
							"dayOfWeek" => [
								"Monday",
								"Tuesday",
								"Wednesday",
								"Thursday",
								"Friday"
							],
							"opens"     => "09:00",
							"closes"    => "18:00"
						]
					]
				]
			]
		);

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

		echo '
        <script type="application/ld+json">';
		echo json_encode( $schema );
		echo ' </script>';

	}

	/**
	 * Creates a slug based on the title of a post
	 *
	 * @param $value
	 * @param $key
	 * @param $code
	 * @param $post_id
	 *
	 * @return mixed|string
	 */
	public function transform_meta_value( $value, $key, $code ) {
		if ( ( $key == 'fs_seo_slug' || $key == '_seo_slug' )
		     && $value == '' && $code
		     && ( $_POST[ 'post_title_' . $code ] != '' || $_POST['name'] != '' ) ) {
			$value = $_POST[ 'post_title_' . $code ] ?? $_POST[ 'name_' . $code ];
			if ( $value != '' ) {
				$value = fs_convert_cyr_name( $value );
			}
		}

		return $value;
	}

	/**
	 * Displays microdata for the category page
	 *
	 * @return void
	 */
	function product_category_microdata() {
		if ( ! fs_is_product_category() ) {
			return;
		}
		$term        = get_queried_object();
		$count_votes = FS_Rating_Class::get_count_ratings_in_category();
		$schema      = array(
			"@context"        => "https://schema.org",
			"@type"           => "Product",
			"name"            => $term->name,
			"image"           => esc_url( fs_get_category_image( $term->term_id, 'full' ) ),
			"description"     => strip_tags( $term->description ) != ''
				? strip_tags( $term->description )
				: '',
			"offers"          => [
				"@type"         => "AggregateOffer",
				"lowPrice"      => FS_Product::get_min_price_in_category(),
				"highPrice"     => FS_Product::get_max_price_in_category(),
				"offerCount"    => FS_Product::get_count_products_in_category() > 0 ? FS_Product::get_count_products_in_category() : 1,
				"priceCurrency" => fs_option( 'fs_currency_code', 'UAH' ),
			],
			"aggregateRating" => [
				"@type"       => "AggregateRating",
				"ratingCount" => $count_votes ?: 1,
				"ratingValue" => $count_votes ? round( FS_Rating_Class::get_average_rating_in_category(), 1 ) : 5,
				"bestRating"  => "5"
			]
		);


		echo '<script type="application/ld+json">';
		echo json_encode( $schema );
		echo '</script>';
	}
}