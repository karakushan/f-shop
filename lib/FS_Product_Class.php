<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 19.08.2017
 * Time: 18:16
 */

namespace FS;


class FS_Product_Class {

	/**
	 * удаляет все товары
	 *
	 * @param bool $attachments - удалять вложения или нет (по умолчанию удаляет)
	 */
	public static function delete_products() {
		global $fs_config;
		$attachments = true;
		$posts       = new \WP_Query( array(
			'post_type'      => array( $fs_config->data['post_type'] ),
			'posts_per_page' => - 1
		) );
		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				global $post;
				if ( $attachments ) {
					$childrens = get_children( array( 'post_type' => 'attachment', 'post_parent' => $post->ID ) );
					if ( $childrens ) {
						foreach ( $childrens as $children ) {
							wp_delete_post( $children->ID, true );
						}
					}
				}
				wp_delete_post( $post->ID, true );
			}
		}
	}

}