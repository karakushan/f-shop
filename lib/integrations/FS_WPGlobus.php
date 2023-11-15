<?php


namespace FS\Integrations;


use FS\FS_Config;
use WPGlobus;
use WPGlobus_Utils;

class FS_WPGlobus {
	public $switcher_name = '';

	public function __construct() {
		add_action( 'fs_wpglobus_language_switcher', [ $this, 'wpglobus_language_switcher' ] );
		add_action( 'wp_footer', [ $this, 'footer_inline_scripts' ] );
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
			'class'             => 'lang-switcher',
			'type'              => 'ul',
			'name'              => 'wpglobus-lang-switcher',
			'link_class'        => '',
			'link_active_class' => ''
		] );

		$this->switcher_name = $args['name'];

		$post_type        = FS_Config::get_data( 'post_type' );
		$languages        = WPGlobus::Config()->enabled_languages;
		$default_language = WPGlobus::Config()->default_language;
		$locales          = WPGlobus::Config()->locale;
        
        if ( $args['type'] == 'ul' ) {
			echo '<ul class="' . esc_attr( $args['class'] ) . '">';
		} elseif ( $args['type'] == 'select' ) {
			echo '<select name="' . esc_attr( $args['name'] ) . '" class="' . esc_attr( $args['class'] ) . '" >';
		} elseif ( $args['type'] == 'link' ) {
			echo '<div class="' . esc_attr( $args['class'] ) . '">';
		}

		foreach ( $languages as $lang ) {
			$class     = $lang == $default_language ? 'class="active"' : '';
			$lang_name = apply_filters( 'fs_language_display_name', $lang );

			if ( is_singular( $post_type ) ) { 
				global $post;
				$slug   = $default_language==$lang ? $post->post_name : get_post_meta( $post->ID, 'fs_seo_slug__' . mb_strtolower($locales[ $lang ]), 1 ) ;
				$link   =$default_language==$lang ? site_url( sprintf( '%s/%s/', $post_type, $slug ) )
                    : site_url( sprintf( '%s/%s/%s/', $lang, $post_type, $slug ) );
			} elseif ( fs_option( 'fs_localize_slug' ) && fs_is_product_category() ) {
				$link = fs_localize_category_url( get_queried_object_id(), $locales[ $lang ] );
			} else {
				$link = WPGlobus_Utils::localize_current_url( $lang );
			}

			$link = apply_filters( 'fs_wpglobus_language_switcher_link', $link, $lang );

			if ( $args['type'] == 'ul' ) {
				echo ' <li ' . $class . '><a href="' . esc_url( $link ) . '" ' . $class . '>' . esc_html( $lang_name ) . '</a></li>';
			} elseif ( $args['type'] == 'select' ) {
				echo ' <option ' . $class . ' value="' . esc_url( $link ) . '" ' . selected( $lang, WPGlobus::Config()->language, false ) . '>' . esc_html( $lang_name ) . '</option>';
			} elseif ( $args['type'] == 'link' ) {
				if ( WPGlobus::Config()->language != $lang ) {
					echo '<a href="' . esc_attr( $link ) . '" class="' . esc_attr( $args['link_class'] ) . '" title="' . esc_html( $lang_name ) . '">' . esc_html( $lang_name ) . '</a>';
				} else {
					echo '<span class="' . esc_attr( $args['link_active_class'] ) . '">' . esc_html( $lang_name ) . '</span>';
				}
			}
		}

		if ( $args['type'] == 'ul' ) {
			echo '</ul>';
		} elseif ( $args['type'] == 'select' ) {
			echo '</select>';
		} elseif ( $args['type'] == 'link' ) {
			echo '</div>';
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