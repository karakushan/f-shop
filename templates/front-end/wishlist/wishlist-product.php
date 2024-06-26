<div class="col-lg-3">
  <div class="fs-wishlist-item">
    <figure>
		<?php if ( has_post_thumbnail() )
			the_post_thumbnail( 'medium' ) ?>
		<?php if ( fs_is_bestseller() ): ?>
          <span class="label-best">хит</span>
		<?php endif; ?>
    </figure>
    <div class="info">
      <a href="<?php the_permalink() ?>" class="name"><?php the_title() ?></a>
		<?php do_action( 'fs_the_price', 0, '<span class="price">%s %s</span>' ) ?>
      <div><?php do_action( 'fs_add_to_cart', 0, 'В корзину', array( 'class' => 'addToCart', 'type' => 'link' ) ) ?></div>
      <div><?php fs_delete_wishlist_position( 0, 'удалить из списка?' ) ?></div>
    </div>
  </div>
</div>