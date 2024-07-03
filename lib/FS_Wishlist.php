<?php

namespace FS;

class FS_Wishlist {

	public static function get_items() {
		return isset( $_SESSION['fs_wishlist'] ) ? (array) $_SESSION['fs_wishlist'] : [];
	}

	/**
	 * Checks if an item is in the wishlist
	 *
	 * @param $product_id
	 *
	 * @return bool
	 */
	public static function contains( $product_id = 0 ) {
		$ids = array_values( self::get_items() );

		return in_array( $product_id, $ids );
	}

}