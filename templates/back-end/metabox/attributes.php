<h3>Атрибуты товара</h3>
<p>Здесь можно указать какими свойствами обладает товар.</p>
<?php
global $fs_config;
$attributes    = get_the_terms( $post->ID, $fs_config->data['product_att_taxonomy'] );
$att_hierarchy = [];
if ( $attributes ) {
	foreach ( $attributes as $att ) {
		if ( ! $att->parent ) {
			continue;
		}
		$att_hierarchy[ $att->parent ][] = $att;
	}

}
?>
<!--<pre><?php /*print_r( $att_hierarchy ); */ ?></pre>-->
<table class="fs-admin-atts">
  <thead>
  <tr>
    <th>Группа
      <button class="add-att button" type="button"><span class="dashicons dashicons-plus"></span></button>
    </th>
    <th>Свойства
      <button class="add-att button" type="button"><span class="dashicons dashicons-plus"></span></button>
    </th>
  </tr>
  </thead>
  <tbody>
  <?php if ( $att_hierarchy ): ?>
	  <?php foreach ( $att_hierarchy as $k => $att_h ): ?>
		  <?php $parent = get_term( $k, $fs_config->data['product_att_taxonomy'] ) ?>

      <tr>
        <td><?php echo apply_filters( 'the_title', $parent->name ) ?></td>
        <td>

          <ul class="fs-childs-list">   <?php foreach ( $att_h as $child ): ?>
              <li><?php echo apply_filters( 'the_title', $child->name ) ?> <span
                  class="dashicons dashicons-no-alt remove-att" title="do I delete a property?" data-action="remove-att"
                  data-category-id="<?php echo $child->term_id ?>" data-product-id="<?php echo $post->ID ?>"></span>
              </li>
			  <?php endforeach; ?>
          </ul>


        </td>
      </tr>
	  <?php endforeach; ?>
  <?php endif; ?>
  </tbody>
</table>