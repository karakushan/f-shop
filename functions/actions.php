<?php

// добавляем шорткоды полей в Contact Form 7
add_action( 'wpcf7_init', 'fs_cf7_add_shortcode' );
function fs_cf7_add_shortcode() {
	wpcf7_add_shortcode( 'post_url', 'fs_post_url_shortcode_handler', true );
	wpcf7_add_shortcode( 'post_id', 'fs_post_id_shortcode_handler', true );
	wpcf7_add_shortcode( 'post_title', 'fs_post_title_shortcode_handler', true );
	wpcf7_add_shortcode( array( 'delivery_methods', 'delivery_methods*' ), 'fs_delivery_shortcode_handler', true );
	wpcf7_add_shortcode( array( 'payment_methods', 'payment_methods*' ), 'fs_payment_shortcode_handler', true );

}

function fs_post_url_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_permalink() . '">';

	return $field;
}

function fs_post_id_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_id() . '">';

	return $field;
}

function fs_post_title_shortcode_handler( $tag ) {
	$field = '<input type="hidden" name="' . $tag['name'] . '" value="' . get_the_title() . '">';

	return $field;
}

function fs_delivery_shortcode_handler( $tag ) {
	$methods = get_terms( array( 'taxonomy' => 'fs-delivery-methods', 'hide_empty' => false ) );
	$options = '';
	if ( $tag['options'] ) {
		foreach ( $tag['options'] as $key => $option ) {
			$opt     = explode( ':', $option );
			$options .= $opt[0] . '=' . '"' . $opt[1] . '" ';
		}
	}
	$field = '';
	if ( $methods ) {
		$field .= '<select name="' . $tag['name'] . '" ' . $options . ' required>';
		$field .= '<option value="">' . __( 'Choose a shipping method', 'fast-shop' ) . '</option>';
		foreach ( $methods as $key => $method ) {
			$field .= '<option value="' . $method->name . '">' . $method->name . '</option>';
		}
		$field .= '</select>';
	}

	return $field;
}

function fs_payment_shortcode_handler( $tag ) {
	$methods = get_terms( array( 'taxonomy' => 'fs-payment-methods', 'hide_empty' => false ) );
	$options = '';
	if ( $tag['options'] ) {
		foreach ( $tag['options'] as $key => $option ) {
			$opt     = explode( ':', $option );
			$options .= $opt[0] . '=' . '"' . $opt[1] . '" ';
		}
	}
	$field = '';
	if ( $methods ) {
		$field .= '<select name="' . $tag['name'] . '" ' . $options . ' required>';
		$field .= '<option value="">' . __( 'Choose a shipping method', 'fast-shop' ) . '</option>';
		foreach ( $methods as $key => $method ) {
			$field .= '<option value="' . $method->name . '">' . $method->name . '</option>';
		}
		$field .= '</select>';
	}

	return $field;
}

// Регистрация виджета консоли вывода популярных товаров
add_action( 'wp_dashboard_setup', 'fs_dashboard_widgets' );
// Выводит контент
function fs_popular_db_widget() {
	$popular = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => 5,
		'meta_query'=>array(
			'views'=>array(
				'key'       => 'views',
				'type'=>'NUMERIC'
			)

		),
		'orderby'        => 'views',
		'order'=>'DESC'

	) );
	if ( $popular->have_posts() ) {
		echo '<table class="fsdw_popular">';
		while ( $popular->have_posts() ) {
			$popular->the_post();
			global $post;
			$thumbmail = get_the_post_thumbnail( $post->ID, array( 50, 50 ) );
			$title     = get_the_title( $post->ID );
			$link      = get_the_permalink( $post->ID );
			$views     = get_post_meta( $post->ID, 'views', true );
			$views     = intval( $views );
			echo '<tr>';
			echo '<td>' . $thumbmail . '</td>';
			echo '<td><a href="' . $link . '" target="_blank">' . $title . '</a></br>
			<span><i>'.__('Views','fast-shop').': </i>' . $views . '</span></td>';
			echo '<td>';
			fs_the_price( $post->ID );
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<p>' . __( 'It looks like your site has not been visited yet.', 'fast-shop' ) . '</p>';
	}
	wp_reset_query();
	echo '</table>';
}

// Используется в хуке
function fs_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_widget', __('Popular items','fast-shop'), 'fs_popular_db_widget' );
}






