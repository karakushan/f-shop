<?php

namespace FS\Admin;

class CommentMetabox {

	public function __construct() {
		add_action( 'add_meta_boxes_comment', [ $this, 'add_comment_metabox' ] );
		add_action( 'edit_comment', [ $this, 'save_comment_meta' ], 10, 2 );
	}

	/**
	 * Adds a custom meta box to the comment editing screen in the WordPress admin.
	 *
	 * @return void
	 */
	public function add_comment_metabox() {
		static $has_been_added = false; // Prevents infinite loop
		if ( $has_been_added ) {
			return;
		}
		$has_been_added = true;

		add_meta_box(
			'comment_meta_box',
			__( 'Comment Meta', 'f-shop' ),
			[ $this, 'render_metabox' ],
			'comment',
			'normal',
			'high'
		);
	}

	/**
	 * Renders the custom meta box for editing a user's rating in the WordPress admin comment edit screen.
	 *
	 * @param \WP_Comment $comment The comment object for which the meta box is being displayed.
	 *
	 * @return void
	 */
	public function render_metabox( \WP_Comment $comment ) {
		$rating = (int) $comment->comment_karma;
		wp_nonce_field( 'save_comment_meta', 'comment_meta_nonce' );
		?>
        <p>
            <label for="user_rating"><?php esc_html_e( 'Rating:', 'f-shop' ); ?></label>
            <select name="user_rating" id="user_rating">
				<?php for ( $i = 1; $i <= 5; $i ++ ) : ?>
                    <option value="<?php echo $i; ?>" <?php selected( $rating, $i ); ?>>
						<?php echo $i; ?>
                    </option>
				<?php endfor; ?>
            </select>
        </p>
		<?php
	}

	/**
	 * Saves custom metadata for a comment, specifically a user rating, if provided and valid.
	 *
	 * @param int $comment_id The ID of the comment being saved.
	 *
	 * @return void
	 */
	public function save_comment_meta( $comment_id ) {
		if ( ! isset( $_POST['comment_meta_nonce'] ) || ! wp_verify_nonce( $_POST['comment_meta_nonce'], 'save_comment_meta' ) ) {
			return;
		}


		$rating = isset( $_POST['user_rating'] ) ? intval( $_POST['user_rating'] ) : 0;

		if ( $rating >= 1 && $rating <= 5 ) {
			remove_action( 'edit_comment', [ $this, 'save_comment_meta' ], 10 ); // Prevents infinite loop
			wp_update_comment( [
				'comment_ID'    => $comment_id,
				'comment_karma' => $rating,
			] );
			add_action( 'edit_comment', [ $this, 'save_comment_meta' ], 10, 2 );
		}
	}
}