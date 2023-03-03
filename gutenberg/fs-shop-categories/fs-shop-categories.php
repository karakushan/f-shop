<?php
/**
 * Plugin Name:       Fs Shop Categories
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fs-shop-categories
 *
 * @package           create-block
 */

use FS\FS_Config;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_fs_shop_categories_block_init() {
	register_block_type( __DIR__ . '/build', [
			'render_callback' => 'fs_shop_categories_render_callback'
	] );
}

add_action( 'init', 'create_block_fs_shop_categories_block_init' );

function fs_shop_categories_render_callback() {
	$current_category = get_queried_object();
	$categories       = get_terms( [
			'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
			'hide_empty' => false,
			'parent'     => fs_is_product_category() ? $current_category->term_id : 0
	] );
	$query            = $_REQUEST['categories'] ?? [];
	$item_view_mode   = 'links';
	ob_start();
	if ( ! empty( $categories ) ): ?>
		<ul class="fs-category-filter">
			<?php foreach ( $categories as $category ): ?>
				<?php $clear_category = $query;
				$key                  = array_search( $category->term_id, $query );
				if ( $key !== false ) {
					unset( $clear_category[ $key ] );
				} ?>
				<li class="fs-checkbox-wrapper level-1">
					<?php if ( $item_view_mode == 'links' ): ?>
						<a <?php if ( $current_category->term_id != $category->term_id ): ?>href="<?php echo esc_attr( get_term_link( $category ) ) ?>"<?php endif; ?>
						   class="level-1-link <?php echo $current_category->term_id == $category->term_id || $current_category->parent == $category->term_id ? 'active' : '' ?>">
							<?php echo fs_get_category_icon( $category->term_id ) ?: '' ?>
							<?php echo apply_filters( 'the_title', $category->name ) ?>
						</a>
						<?php
						$sub_categories = get_terms( [
								'taxonomy'   => FS_Config::get_data( 'product_taxonomy' ),
								'hide_empty' => false,
								'parent'     => $category->term_id
						] );
						if ( ! empty( $sub_categories ) ):
							?>
							<ul class="fs-category-filter">
								<?php foreach ( $sub_categories as $sub_category ): ?>
									<li class="level-2">
										<a <?php if ( $current_category->term_id != $sub_category->term_id ): ?>href="<?php echo esc_attr( get_term_link( $sub_category ) ) ?>"<?php endif ?>
										   class="level-2-link <?php echo $current_category->term_id == $sub_category->term_id ? 'active' : '' ?>">
											<?php echo fs_get_category_icon( $sub_category->term_id ) ?: '' ?>
											<span><?php echo apply_filters( 'the_title', $sub_category->name ) ?></span>
										</a>
									</li>
								<?php endforeach ?>
							</ul>
						<?php endif; ?>
					<?php else: ?>
						<input type="checkbox" class="checkStyle"
							   data-fs-action="filter"
							   data-fs-redirect="<?php echo esc_url( add_query_arg( [ 'categories' => $clear_category ] ) ); ?>"
							   name="categories[<?php echo $category->slug; ?>]"
							   value="<?php echo esc_url( add_query_arg( [ 'categories' => array_merge( $query, [ $category->term_id ] ) ] ) ); ?>"
							   id="fs-category-<?php echo $category->term_id ?>" <?php echo in_array( $category->term_id, $query ) ? 'checked' : '' ?>>
						<label for="fs-category-<?php echo $category->term_id ?>"><?php echo $category->name; ?></label>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif;

	return ob_get_clean();
}
