<?php
$wishlist_items = fs_get_wishlist();

if (count($wishlist_items)  ): ?>
	<div class="table-responsive">
		<table class="table table-bordered table-centered">
			<thead>
			<tr>
				<th style="width: 137px;">
					<?php esc_html_e( 'Photo', 'f-shop' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Product', 'f-shop' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Vendor code', 'f-shop' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Price', 'f-shop' ); ?>
				</th>
				<th>
					<?php esc_html_e( 'Notifications', 'f-shop' ); ?>
				</th>

				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php
            global $post;
			foreach ( $wishlist_items as $post ) :
                    setup_postdata( $post );
					?>

					<tr>
						<td>
							<?php if ( has_post_thumbnail() ) {
								the_post_thumbnail();
							} ?>
						</td>
						<td>
							<div class="info">
								<a href="<?php the_permalink() ?>" target="_blank"
								   class="h5"><?php the_title() ?></a>
							</div>
						</td>
						<td>
							<?php fs_product_code() ?>
						</td>
						<td style="white-space: nowrap;">
							<?php fs_the_price() ?>
						</td>

						<td class="text-left">
							<div class="custom-control custom-checkbox">
								<input type="checkbox" name="notification-aviable" class="custom-control-input" id="notification-aviable">
								<label class="custom-control-label" for="notification-aviable"><?php esc_html_e('Notify about availability','f-shop'); ?></label>
							</div>

							<div class="custom-control custom-checkbox">
								<input type="checkbox" name="fs-follow-price" class="custom-control-input" id="fs-follow-price">
								<label class="custom-control-label" for="fs-follow-price"><?php esc_html_e('Follow the price','f-shop'); ?></label>
							</div>
						</td>
						<td>
							<?php fs_delete_wishlist_position(0,'&times;',['class'=>'btn btn-danger btn-sm']) ?>
						</td>
					</tr>
				<?php endforeach;

                wp_reset_postdata();
			 ?>
			</tbody>
		</table>
	</div>
<?php else: ?>
	<p class="text-center mb-5"><?php esc_html_e( 'No product found in wish list', 'f-shop' ) ?></p>
<?php endif ?>
