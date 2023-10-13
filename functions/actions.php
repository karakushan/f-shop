<?php
// Регистрация виджета консоли вывода популярных товаров
add_action( 'wp_dashboard_setup', 'fs_dashboard_widgets' );
// Выводит контент
function fs_popular_db_widget() {
	$popular = new WP_Query( array(
		'post_type'      => 'product',
		'posts_per_page' => 5,
		'meta_query'     => array(
			'views' => array(
				'key'  => 'views',
				'type' => 'NUMERIC'
			)

		),
		'orderby'        => 'views',
		'order'          => 'DESC'

	) );
	if ( $popular->have_posts() ) {
		echo '<table class="fsdw_popular">';
		while ( $popular->have_posts() ) {
			$popular->the_post();
			global $post;
			$thumbmail = get_the_post_thumbnail_url( $post->ID, array( 50, 50 ) );
			$title     = get_the_title( $post->ID );
			$link      = get_the_permalink( $post->ID );
			$views     = get_post_meta( $post->ID, 'views', true );
			$views     = intval( $views );
			echo '<tr>';
			echo '<td><img src="' . esc_url( $thumbmail ) . '" alt="' . esc_attr( $title ) . '"></td>';
			echo '<td><a href="' . esc_url( $link ) . '" target="_blank">' . esc_html( $title ) . '</a></br>
			<span><i>' . esc_html__( 'Views', 'f-shop' ) . ': </i>' . esc_html( $views ) . '</span></td>';
			echo '<td>';
			fs_the_price( $post->ID );
			echo '</td>';
			echo '</tr>';
		}
	} else {
		echo '<p>' . esc_html__( 'It looks like your site has not been visited yet.', 'f-shop' ) . '</p>';
	}
	wp_reset_query();
	echo '</table>';
}

// Используется в хуке
function fs_dashboard_widgets() {
	wp_add_dashboard_widget( 'dashboard_widget', __( 'Popular items', 'f-shop' ), 'fs_popular_db_widget' );
}

// Добавляем кнопки в текстовый html-редактор
add_action( 'admin_print_footer_scripts', 'fs_add_sheensay_quicktags' );
function fs_add_sheensay_quicktags() {
	if ( ! isset( $_GET['page'] ) ) {
		return;
	}
	if ( $_GET['page'] != 'f-shop-settings' || ! wp_script_is( 'quicktags' ) ) {
		return;
	} ?>
    <script type="text/javascript">
        if (QTags) {
            // QTags.addButton( id, display, arg1, arg2, access_key, title, priority, instance );
            QTags.addButton('fs_b_fname', '<?php esc_attr_e( 'First name', 'f-shop' ) ?>', '%fs_first_name%', '', '', '<?php esc_attr_e( 'First name', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_lname', '<?php esc_attr_e( 'Last name', 'f-shop' ) ?>', '%fs_last_name%', '', '', '<?php esc_attr_e( 'Last name', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_besc_attr_email', '<?php esc_attr_e( 'Email', 'f-shop' ) ?>', '%fsesc_attr_email%', '', '', '<?php esc_attr_e( 'E-mail', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_order_id', '<?php esc_attr_e( 'Order id', 'f-shop' ) ?>', '%order_id%', '', '', '<?php esc_attr_e( 'Order id', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_total_amount', '<?php esc_attr_e( 'Amount', 'f-shop' ) ?>', '%total_amount%', '', '', '<?php esc_attr_e( 'Amount', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_phone', '<?php esc_attr_e( 'Phone', 'f-shop' ) ?>', '%fs_phone%', '', '', '<?php esc_attr_e( 'Phone', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_fs_city', '<?php esc_attr_e( 'City', 'f-shop' ) ?>', '%fs_city%', '', '', '<?php esc_attr_e( 'City', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_fs_adress', '<?php esc_attr_e( 'Delivery address', 'f-shop' ) ?>', '%fs_adress%', '', '', '<?php esc_attr_e( 'Delivery address', 'f-shop' ) ?>', 1);
            QTags.addButton('fs_b_site_name', '<?php esc_attr_e( 'Site name', 'f-shop' ) ?>', '%site_name%', '', '', '<?php esc_attr_e( 'Site name', 'f-shop' ) ?>', 1);
        }
    </script>
	<?php
}