<?php


namespace FS\Integrations;


use FS\FS_Config;
use WPGlobus;
use WPGlobus_Utils;

class WP_Globus {
	public $switcher_name = '';

	public function __construct() {
		add_filter( 'wpglobus_hreflang_tag', [ $this, 'wpglobus_hreflang_tag_filter' ], 10, 1 );
		add_action( 'fs_wpglobus_language_switcher', [ $this, 'wpglobus_language_switcher' ] );
		add_action( 'wp_footer', [ $this, 'footer_inline_scripts' ] );
	}

	/**
	 * Заменяет hreflang в head
	 *
	 * @param $hreflangs
	 *
	 * @return mixed
	 */
	function wpglobus_hreflang_tag_filter( $hreflangs ) {
		$product_taxonomy_name = FS_Config::get_data( 'product_taxonomy' );
		$post_type             = FS_Config::get_data( 'post_type' );
        

		// Меняем hreflang для категорий товара
		if ( is_archive( $post_type ) && is_tax( $product_taxonomy_name ) ) {
			$queried_object = get_queried_object();
			$slug           = get_term_meta( $queried_object->term_id, '_seo_slug__uk', 1 )
				? get_term_meta( $queried_object->term_id, '_seo_slug__uk', 1 ) : $queried_object->slug;
			if ( fs_option( 'fs_disable_taxonomy_slug' ) ) {
				$hreflangs['ru'] = sprintf( '<link rel="alternate" hreflang="ru" href="%s"/>', site_url( $queried_object->slug . '/' ) );
				$hreflangs['ua'] = sprintf( '<link rel="alternate" hreflang="uk" href="%s"/>', site_url( 'ua/' . $slug . '/' ) );
			} else {
				$hreflangs['ru'] = sprintf( '<link rel="alternate" hreflang="ru" href="%s"/>', site_url( $product_taxonomy_name . '/' . $queried_object->slug . '/' ) );
				$hreflangs['ua'] = sprintf( '<link rel="alternate" hreflang="uk" href="%s"/>', site_url( 'ua/' . $product_taxonomy_name . '/' . $slug . '/' ) );
			}
		} elseif ( is_singular( $post_type ) ) {
			global $post;
			$slug_ua         = get_post_meta( $post->ID, 'fs_seo_slug__uk', 1 );
			$hreflangs['ua'] = sprintf( '<link rel="alternate" hreflang="ua" href="%s"/>', $slug_ua ? site_url( sprintf( '%s/%s/%s/', 'ua', $post_type, $slug_ua ) )
				: WPGlobus_Utils::localize_current_url( 'ua' ) );
			$hreflangs['ru'] = sprintf( '<link rel="alternate" hreflang="ru" href="%s"/>', WPGlobus_Utils::localize_current_url( 'ru' ) );
		}
		return $hreflangs;
	}

	/**
	 * Переключатель языков для WPGlobus
	 *
	 * @param array $args
	 */
	public function wpglobus_language_switcher( $args = [] ) {
		if ( ! class_exists( 'WPGlobus' ) || ! WPGlobus::Config()->enabled_languages ) {
			return;
		}

		$args = wp_parse_args( $args, [
			'class' => 'lang-switcher',
			'type'  => 'ul',
			'name'  => 'wpglobus-lang-switcher'
		] );

		$this->switcher_name = $args['name'];

		$locales               = [ 'ua' => 'uk', 'ru' => 'ru_RU' ];
		$post_type             = FS_Config::get_data( 'post_type' );
		$languages             = WPGlobus::Config()->enabled_languages;
		$product_taxonomy_name = FS_Config::get_data( 'product_taxonomy' );

		if ( $args['type'] == 'ul' ) {
			echo '<ul class="' . esc_attr( $args['class'] ) . '">';
		} elseif ( $args['type'] == 'select' ) {
			echo '<select name="' . esc_attr( $args['name'] ) . '" class="' . esc_attr( $args['class'] ) . '" >';
		}
		foreach ( $languages as $lang ) {
			$class = $lang == WPGlobus::Config()->language ? 'class="active"' : '';

			if ( is_singular( $post_type ) ) {
				global $post;
				$prefix = $lang == WPGlobus::Config()->default_language ? '' : $lang;
				$slug   = get_post_meta( $post->ID, 'fs_seo_slug__' . $locales[ $lang ], 1 );
				$link   = $slug ? site_url( sprintf( '%s/%s/%s', $prefix, $post_type, $slug ) )
					: site_url( sprintf( '%s/%s/%s', $prefix, $post_type, $post->post_name ) );
			} elseif ( is_archive( $post_type ) && is_tax( $product_taxonomy_name ) ) {
				$link = fs_localize_category_url( get_queried_object_id(), $locales[ $lang ] );
			} else {
				$link = WPGlobus_Utils::localize_current_url( $lang );
			}

			$link = apply_filters( 'fs_wpglobus_language_switcher_link', $link, $lang );

			if ( $args['type'] == 'ul' ) {
				echo ' <li ' . $class . '><a href="' . esc_url( $link ) . '" ' . $class . '>' . $lang . '</a></li>';
			} elseif ( $args['type'] == 'select' ) {
				echo ' <option ' . $class . ' value="' . esc_url( $link ) . '" ' . selected( $lang, WPGlobus::Config()->language, false ) . '>' . esc_html( $lang ) . '</option>';
			}
		}

		if ( $args['type'] == 'ul' ) {
			echo '</ul>';
		} elseif ( $args['type'] == 'select' ) {
			echo '</select>';
		}
	}

	public function footer_inline_scripts() { ?>
        <script>
            jQuery('[name="<?php echo esc_attr( $this->switcher_name ) ?>"').on('change', function () {
                location.href = jQuery(this).val();
            })
        </script>
	<?php }
}