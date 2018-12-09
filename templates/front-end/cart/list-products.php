<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 03.06.2018
 * Time: 19:14
 */
?>
<div class="fs-cart-listing">
	<?php $cart = fs_get_cart() ?>
	<?php if ( $cart ): ?>

      <table class="table table-condensed">
        <thead class="thead-light">
        <tr>
          <td>
			  <?php esc_html_e( 'Photo', 'f-shop' ); ?>
          </td>
          <td>
			  <?php esc_html_e( 'Product', 'f-shop' ); ?>
          </td>
          <td>
			  <?php esc_html_e( 'Vendor code', 'f-shop' ); ?>
          </td>
          <td>
			  <?php esc_html_e( 'Price', 'f-shop' ); ?>
          </td>
          <td>
			  <?php esc_html_e( 'Quantity', 'f-shop' ); ?>
          </td>
          <td>
			  <?php esc_html_e( 'Cost', 'f-shop' ); ?>
          </td>
          <td></td>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $cart as $c ): ?>
          <tr>
            <td>
				<?php echo get_the_post_thumbnail( $c['id'], 'thumbnail' ) ?>
            </td>
            <td>
              <div class="info">
                <a href="<?php echo esc_url( $c['link'] ) ?>" target="_blank" class="name"><?php echo $c['name'] ?></a>
              </div>
            </td>
            <td>
				<?php echo $c['sku'] ?>
            </td>
            <td>
				<?php echo $c['all_price'] ?>
            </td>
            <td>
				<?php do_action( 'fs_cart_quantity', $c['id'], $c['count'], array(
					'pluss' => array(
						'class'   => 'fs-pluss',
						'content' => '<span class="glyphicon glyphicon-plus"></span>'
					),
					'minus' => array(
						'class'   => 'fs-minus',
						'content' => '<span class="glyphicon glyphicon-minus"></span>'
					),
				) ) ?>
            </td>
            <td>
				<?php echo $c['all_price'] ?>
            </td>
            <td>
				<?php fs_delete_position( $c['id'], array(
					'class'   => 'fs-remove-position',
					'type'    => 'button',
					'content' => '&times;'
				) ) ?>
            </td>
          </tr>
		<?php endforeach; ?>
        </tbody>
      </table>
      <div class="fs-products-after">
        <div class="row">
          <div class="col-lg-6">
            <a href="<?php echo esc_url( fs_get_catalog_link() ) ?>" class="btn btn-primary">
				<?php esc_html_e( 'Continue shopping', 'f-shop' ) ?> <span
                class="glyphicon glyphicon-chevron-right"></span>
            </a>
          </div>
          <div class="col-lg-6 text-right">
			  <?php fs_delete_cart( array(
				  'type'  => 'button',
				  'class' => 'btn btn-danger'
			  ) ) ?>
          </div>
        </div>

      </div>
      <table class="table table-bordered" style="width: 300px;">
        <tbody>
        <tr>
          <td><?php esc_html_e( 'Cost of goods', 'f-shop' ) ?>:</td>
          <td><?php fs_total_amount() ?></td>
        </tr>
        <tr>
          <td><?php esc_html_e( 'Discount', 'f-shop' ) ?>:</td>
          <td>0</td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e( 'Total', 'f-shop' ) ?>:</th>
          <td><?php fs_total_amount() ?></td>
        </tr>
        </tbody>
      </table>
      <p>
        <a href="<?php echo esc_url( fs_get_checkout_page_link() ) ?>"
           class="btn btn-success btn-lg"><?php _e( 'Checkout', 'f-shop' ); ?></a>
      </p>
	<?php else: ?>
      <p class="fs-info-block"><span
          class="icon glyphicon glyphicon-info-sign"></span> <?php esc_html_e( 'Your basket is empty', 'f-shop' ) ?>.&nbsp;
        <a
          href="<?php echo esc_url( fs_get_catalog_link() ) ?>"><?php esc_html_e( 'To the catalog', 'f-shop' ) ?></a>
      </p>
	<?php endif; ?>
</div>
