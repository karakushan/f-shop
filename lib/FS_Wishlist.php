<?php

namespace FS;

class FS_Wishlist {
	private const SESSION_WISHLIST_KEY = 'fs_wishlist';

	public function __construct() {
		//  Add to wishlist
		add_action( 'wp_ajax_fs_addto_wishlist', array( $this, 'fs_add_to_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fs_addto_wishlist', array( $this, 'fs_add_to_wishlist' ) );

		// Remove from wish list
		add_action( 'wp_ajax_fs_del_wishlist_pos', array( $this, 'remove_from_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fs_del_wishlist_pos', array( $this, 'remove_from_wishlist' ) );

		// Clean Wishlist
		add_action( 'wp_ajax_fs_clean_wishlist', array( $this, 'fs_clean_wishlist' ) );
		add_action( 'wp_ajax_nopriv_fs_clean_wishlist', array( $this, 'fs_clean_wishlist' ) );
	}


	/**
	 * Retrieves the items in the wishlist.
	 *
	 * @return array The list of items in the wishlist.
	 */
	public static function get_wishlist_items(): array {
		$wishlist = $_SESSION[ self::SESSION_WISHLIST_KEY ] ?? [];

		if ( is_user_logged_in() ) {
			$user_id       = get_current_user_id(); // Get the current user's ID
			$user_wishlist = get_user_meta( $user_id, 'fs_wishlist', true ); // Get items from the user's meta field

			// Ensure the meta field data is an array
			if ( is_array( $user_wishlist ) ) {
				$wishlist = array_unique( array_merge( $wishlist, $user_wishlist ) );
			}
		}

		return is_array( $wishlist ) ? $wishlist : [];
	}


	/**
	 * Retrieves the products from the wishlist.
	 *
	 * Merges wishlist items from the session and user metadata if the user is logged in,
	 * and fetches the corresponding product posts.
	 *
	 * @return array An array of product posts from the wishlist.
	 */
	public static function get_wishlist_products(): array {
		$wishlist = self::get_wishlist_items();

		if ( empty( $wishlist ) ) {
			return [];
		}

		// Retrieve wishlist products
		$args = [
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => - 1,
			'post__in'       => $wishlist, // Use IDs from the wishlist
		];

		return get_posts( $args );
	}

	/**
	 * Checks if a product is in the wishlist.
	 *
	 * @param int $product_id The ID of the product to check.
	 *
	 * @return bool True if the product is in the wishlist, false otherwise.
	 */
	public static function contains( int $product_id ): bool {
		return in_array( $product_id, self::get_wishlist_items(), true );
	}

	/**
	 * Adds a product to the user's wishlist. If the user is logged in, the product is added to their persistent wishlist stored in user metadata.
	 * If the user is not logged in, the product is added to the session-based wishlist.
	 *
	 * @return void
	 */
	public function fs_add_to_wishlist() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( [ 'msg' => __( 'Security check failed', 'f-shop' ) ] );
		}

		$productId = (int) $_REQUEST['product_id'];

		// Check if product exists
		if ( ! get_post_status( $productId ) || get_post_type( $productId ) !== FS_Config::get_data( 'post_type' ) ) {
			wp_send_json_error( [ 'msg' => __( 'The product does not exist.', 'f-shop' ) ] );
		}

		// Check if the product is already in the wishlist
		if ( self::contains( $productId ) ) {
			self::delete_from_wishlist( $productId );

			wp_send_json_success( [
				'msg' => __( 'Product removed from wishlist', 'f-shop' )
			] );

			return;
		}

		$this->update_user_wishlist( get_current_user_id(), $productId );

		wp_send_json_success( [
			'msg' => str_replace( [ '%product%', '%wishlist_url%' ], [
				get_the_title( $productId ),
				fs_wishlist_url()
			], __( 'Item &laquo;%product%&raquo; successfully added to wishlist. <a href="%wishlist_url%">Go to wishlist</a>', 'f-shop' ) ),
		] );
	}

	/**
	 * Updates the user's wishlist by adding a product.
	 *
	 * @param int $userId The ID of the user. If 0, the product is added to the session wishlist.
	 * @param int $productId The ID of the product to add to the wishlist.
	 *
	 * @return bool Returns true if the product was successfully added; false if the product was already in the wishlist.
	 */
	private function update_user_wishlist( int $userId, int $productId ): bool {
		if ( ! $userId ) {
			$_SESSION[ self::SESSION_WISHLIST_KEY ][ $productId ] = $productId;

			return true; // Product successfully added to session
		}

		$userWishlist = get_user_meta( $userId, 'fs_wishlist', true );
		$userWishlist = is_array( $userWishlist ) ? $userWishlist : [];

		if ( in_array( $productId, $userWishlist, true ) ) {
			return false; // Product already in wishlist
		}

		$userWishlist[] = $productId;
		update_user_meta( $userId, self::SESSION_WISHLIST_KEY, $userWishlist );

		return true; // Product successfully added
	}

	/**
	 * Removes a product from the wishlist
	 *
	 * @return void
	 */
	public function remove_from_wishlist() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}

		$product_id = (int) $_REQUEST['item_id'];

		self::delete_from_wishlist( $product_id );

		wp_send_json_success( array(
			'msg'    => __( 'Product removed from your wishlist.', 'f-shop' ),
			'status' => true
		) );
	}

	/**
	 * Cleans the wishlist by removing all items.
	 *
	 * @return void
	 */
	function fs_clean_wishlist() {
		if ( ! FS_Config::verify_nonce() ) {
			wp_send_json_error( array( 'msg' => __( 'Security check failed', 'f-shop' ) ) );
		}

		unset( $_SESSION['fs_wishlist'] );

		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			delete_user_meta( $user_id, 'fs_wishlist' );
		}

		wp_send_json_success();
	}


	/**
	 * Deletes a product from the wishlist by product ID.
	 *
	 * This method allows for direct removal of a specific product from the wishlist
	 * based on its ID, regardless of the current session or user state.
	 *
	 * @param int $product_id The ID of the product to delete from the wishlist.
	 *
	 * @return bool Returns true if the product was successfully removed, false otherwise.
	 */
	public static function delete_from_wishlist( int $product_id ): bool {
		$wishlist = self::get_wishlist_items();

		// Check if the product exists in the wishlist
		if ( ! in_array( $product_id, $wishlist, true ) ) {
			return false; // Product is not in the wishlist
		}

		// Remove the product from the wishlist
		$key = array_search( $product_id, $wishlist );
		unset( $wishlist[ $key ] );

		// If the user is logged in, update their wishlist in user meta
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			update_user_meta( $user_id, self::SESSION_WISHLIST_KEY, $wishlist );
		}

		// Update session-based wishlist
		$_SESSION[ self::SESSION_WISHLIST_KEY ] = $wishlist;

		return true; // Product successfully removed
	}

}