<div class="livesearch-item">
	<a href="<?php the_permalink() ?>">
		<?php if (has_post_thumbnail()) the_post_thumbnail() ?>
		<span class="title"><?php the_title() ?></span> <br>

	</a>
	<p class="price"><?php do_action('fs_the_price') ?></p>
</div>