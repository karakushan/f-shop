<?php
$posts = get_posts( array(
	'posts_per_page' => - 1,
	'post_type'      => $args['post_type'],
) );
?>
<select name="<?php echo $name; ?>">
	<option value="0"><?php _e( 'Select', 'f-shop' ); ?></option>
	<?php foreach ( $posts as $item ): ?>
        <option value="<?php echo $item->ID ?>" <?php selected( $item->ID, $args['value'] ) ?>><?php echo apply_filters( 'the_title', $item->post_title ) ?></option>
	<?php endforeach; ?>
</select>
