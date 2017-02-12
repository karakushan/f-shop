<?php $wishlist=fs_get_wishlist(); ?>
<a href="<?php echo $wishlist['page'] ?>">
	<span><?php echo $wishlist['count'] ?></span>
</a>
<span class="name">
	Избранное
</span>
<?php if ($wishlist['count']>0): ?>
	<span><?php echo $wishlist['count'] ?> товаров</span>
<?php else: ?>
	<span>нет товаров</span>
<?php endif ?>