<table class="wp-list-table widefat fixed striped order-list">
  <thead>
  <tr>
    <th>ID</th>
    <th>Название</th>
    <th>Артикул</th>
    <th>Цена</th>
    <th>Количество</th>
    <th>Атрибуты</th>
    <th>Стоимость</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ( $products as $id => $product ): ?>
    <tr>
      <td><?php echo $id ?></td>
      <td><a href="<?php echo get_the_permalink( $id ) ?>" target="_blank"
             title="перейти к товару"><?php echo get_the_title( $id ) ?></a></td>
      <td><?php do_action( 'fs_product_code', $id, '%s', true ) ?></td>
      <td><?php fs_the_price( $id ) ?></td>
      <td><?php echo $product['count'] ?></td>
      <td>
		  <?php
		  global $fs_config;
		  if ( ! empty( $product['attr'] ) ) {
			  echo '<ul class="product-att">';
			  foreach ( $product['attr'] as $att ) {
				  $term = get_term( $att, $fs_config->data['product_att_taxonomy'] );
				  echo '<li><b>' . apply_filters('the_title',get_term_field( 'name', $term->parent, $fs_config->data['product_att_taxonomy'] )) . '</b>: ' . apply_filters('the_title',$term->name) . '</li>';
			  }
			  echo '</ul>';
		  }
		  ?>
      </td>
      <td><?php echo fs_row_price( $id, $product['count'] ) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
  <tfoot>
  <tr>
    <td colspan="6">Общая стоимость</td>
    <td colspan="1"><?php echo $amount ?></td>
  </tr>
  </tfoot>
</table>