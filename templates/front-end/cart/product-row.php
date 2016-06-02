<tr>
	<td>
	<div class="delete-product" title="удалить товар" onclick="if (confirm('Вы действительно хотите удалить товар <?echo $key;?> из корзины?') ) { document.location.href='/cart/?cart=delete&product_id=<?echo $key;?>'}">
<img src="/wp-content/themes/nailstable/images/remove.jpg" alt=""></div>
		
	</td>
	<td>
		<?php if (has_post_thumbnail($key)) {
			echo get_the_post_thumbnail( $key, array(137,130)); 
		} else {
			echo '<img src="http://placehold.it/137x130">';
		} ?>
	</td>
	<td>
		<div class="list-title">
			<a href="<?php echo get_permalink($key ); ?>" target="_blank">
				<?php echo get_the_title($key); ?>
			</a>
		</div>
	</td>
	<td><?php echo $key ?></td>
	<td class="price-row">

		<?php fs_the_price($key) ?></span>

	</td>	
	<td><?php echo $count['count']  ?></td>
	<td class="price-row">

	<?php echo  fs_row_price($key,$count['count'] ) ?>

	</td>
	
</tr>