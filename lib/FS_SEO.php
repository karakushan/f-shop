<?php


namespace FS;


class FS_SEO {
	function __construct() {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Добавляет микроразметку типа Organization
		add_action( 'fs_organization_microdata', [ $this, 'schema_organization_microdata' ] );
		add_action( 'fs_product_reviews_microdata', [ $this, 'product_reviews_microdata' ] );

		// Выводит микроразмету типа LocalBusiness
		add_action( 'fs_local_business_microdata', [ $this, 'schema_local_business_microdata' ] );

		// Позволяет регистрировать события для ремаркетинга Google Adwords
		add_action( 'fs_adwords_remarketing', [ $this, 'adwords_remarketing' ] );

		// Изменяет meta title
		add_filter( 'document_title_parts', array( $this, 'meta_title_filter' ), 10, 1 );

		// TODO: лучше создать специальную папку с интеграциями с другими плагинами и подключать классы с случае если плагин активен
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			// Change SEO Title
			add_filter( 'wpseo_title', array( $this, 'wpseo_title_filter' ), 10, 1 );
			// Change wordpress seo canonical
			add_filter( 'wpseo_canonical', array( $this, 'change_taxonomy_canonical' ), 10, 1 );
		}

		add_action( 'wp_footer', [ $this, 'scripts_in_footer' ] );
		add_action( 'wp_head', [ $this, 'scripts_in_head' ] );

		// Micro-marking of product card
		add_action( 'wp_head', array( $this, 'product_microdata' ) );
	}

	/**
	 * Change wordpress seo canonical
	 *
	 * @param $canonical
	 *
	 * @return string|string[]
	 */
	function change_taxonomy_canonical( $canonical ) {
		$taxonomy_name = FS_Config::get_data( 'product_taxonomy' );
		if ( is_tax( $taxonomy_name ) && fs_option( 'fs_disable_taxonomy_slug' ) ) {
			$canonical = get_term_link( get_queried_object_id(), $taxonomy_name );
			if ( get_locale() != FS_Config::default_locale() ) {
				$canonical = str_replace( [ '/ua', '/uk' ], [ '' ], $canonical );
			}
		}

		return $canonical;
	}

	/**
	 * Преобразовывает мета тайтл для плагина WPSEO
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	function wpseo_title_filter( $title ) {
		if ( ! is_tax( FS_Config::get_data( 'product_taxonomy' ) ) ) {
			return $title;
		}
		$meta_title = get_term_meta( get_queried_object_id(), fs_localize_meta_key( '_seo_title' ), 1 );
		$title      = $meta_title ? $meta_title : $title;

		return apply_filters( 'fs_meta_title', $title );
	}

	/**
	 * Изменяет meta title
	 *
	 * @param $title
	 *
	 * @return mixed
	 */
	public function meta_title_filter( $title ) {

		if ( is_archive( FS_Config::get_data( 'post_type' ) ) && ! is_tax( FS_Config::get_data( 'product_taxonomy' ) ) ) {
			$meta_title     = fs_option( '_fs_catalog_meta_title' ) ?: __( 'Catalog', 'f-shop' );
			$title['title'] = esc_attr( $meta_title );
			$title['site']  = '';

		} elseif ( is_tax( FS_Config::get_data( 'product_taxonomy' ) ) ) {
			$meta_title     = get_term_meta( get_queried_object_id(), fs_localize_meta_key( '_seo_title' ), 1 );
			$meta_title     = $meta_title ? $meta_title : $title['title'];
			$title['title'] = esc_attr( $meta_title );
			$title['site']  = '';
		}

		$title['title'] = apply_filters( 'fs_meta_title', apply_filters( 'the_title', $title['title'] ) );

		return $title;
	}

	/**
	 * Добавляет meta description
	 */
	public function meta_description_action() {
		$meta_description = '';

		// Если посетитель находится на странице архива товаров
		if ( is_archive( FS_Config::get_data( 'post_type' ) ) && ! is_tax( 'catalog' ) ) {
			$meta_description = fs_option( '_fs_catalog_meta_description' ) ?: '';

		} elseif ( is_archive( FS_Config::get_data( 'post_type' ) ) && is_tax( 'catalog' ) ) { // Если посетитель находится на странице таксономии товаров
			$meta_description = get_term_meta( get_queried_object_id(), fs_localize_meta_key( '_seo_description' ), 1 );
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
		$this->meta_description_action();

		do_action( 'fs_local_business_microdata' );
		do_action( 'fs_organization_microdata' );
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
						"ratingValue" => FS_Product::get_average_rating( $comment->comment_post_ID ),
						"reviewCount" => "11"
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
		$product_taxonomy = FS_Config::get_data( 'product_taxonomy' );
		$product_id       = intval( $post->ID );
		$categories       = get_the_terms( $product_id, $product_taxonomy );
		$manufacturer     = get_the_terms( $product_id, 'brands' );
		$brand            = ! is_wp_error( $manufacturer ) && ! empty( $manufacturer[0]->name ) ? $manufacturer[0]->name : get_bloginfo( 'name' );
		$description      = $post->post_excerpt
			? apply_filters( 'the_content', $post->post_excerpt )
			: apply_filters( 'the_content', $post->post_content );
		$description      = strip_tags( $description );

		$schema = array(
			"@context"     => "https://schema.org",
			"@type"        => "Product",
			"url"          => get_the_permalink(),
			"category"     => ! is_wp_error( $categories ) && ! empty( $categories[0]->name ) ? esc_attr( $categories[0]->name ) : '',
			"image"        => esc_url( fs_get_product_thumbnail_url( 0, 'full' ) ),
			"brand"        => $brand,
			"manufacturer" => $brand,
			"model"        => get_the_title(),
			"sku"          => fs_get_product_code(),
			"mpn"          => fs_get_product_code(),
			"productID"    => $product_id,
			"description"  => $description,
			"name"         => get_the_title( $product_id ),
			"offers"       => [
				"@type"           => "Offer",
				"availability"    => fs_aviable_product() ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
				"price"           => fs_get_price(),
				"priceCurrency"   => fs_option( 'fs_currency_code', 'UAH' ),
				"url"             => get_the_permalink(),
				"priceValidUntil" => date( "Y-m-d", strtotime( 'tomorrow' ) ),
			]
		);


		// -->aggregateRating
		$total_vote = get_post_meta( $product_id, 'fs_product_rating' );
		if ( count( $total_vote ) ) {
			$schema["aggregateRating"] = [
				"@type"       => "AggregateRating",
				"ratingCount" => count( $total_vote ),
				"ratingValue" => FS_Product::get_average_rating( $product_id )
			];
		}

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

}