<?php $wishlist=!empty($_SESSION['fs_user_settings']['fs_wishlist'])?$_SESSION['fs_user_settings']['fs_wishlist']:array() ?>

<div  id="fs-wishlist" ><a href="#" class="hvr-grow"><i class="icon icon-heart"></i><span><?php echo count($wishlist) ?></span></a>
<ul class="fs-wishlist-listing">
<li class="wishlist-header"><?php _e('Wishlist','cube44'); ?>: <i class="fa fa-times-circle" aria-hidden="true"></i></li>
<?php if ($wishlist): ?>
	
	<?php foreach ($wishlist as $key => $value): ?>
		<?php echo  "<li><i class=\"fa fa-trash\" aria-hidden=\"true\" data-fs-action=\"wishlist-delete-position\" data-product-id=\"$key\" data-product-name=\"".get_the_title($key)."\" ></i> <a href=\"".get_permalink($key)."\">".get_the_title($key)."</a></li>"; ?>
	<?php endforeach ?>
	
<?php endif ?>
</ul>
</div>