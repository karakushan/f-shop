<div class="fs-dashboard">
    <div class="fs-dashboard__tabs">

        <ul class="fs-dashboard__nav">
            <li class="active">
                <a data-fs-element="tab" href="#personal-info"><?php esc_html_e( 'Personal information', 'f-shop' ) ?>
                </a>
            </li>
            <li>
                <a data-toggle="tab" href="#current-orders"><?php esc_html_e( 'My orders', 'f-shop' ) ?>
                </a>
            </li>
            <li>
                <a data-toggle="tab" href="#wishlist"><?php esc_html_e( 'WishList', 'f-shop' ) ?>
                </a>
            </li>
            <li>
                <a data-toggle="tab" href="#reviews"><?php esc_html_e( 'Reviews', 'f-shop' ) ?>
                </a>
            </li>
        </ul><!--.fs-dashboard__nav-->

        <div class="fs-dashboard__tabs-container">

            <div id="personal-info" class="fs-dashboard__tab active">
				<?= fs_frontend_template( 'dashboard/personal_info', [
					'vars' => array(
						'user' => fs_get_current_user()
					)
				] ) ?>
            </div>

            <div id="current-orders" class="fs-dashboard__tab">
				<?= fs_frontend_template( 'dashboard/orders', [
					'vars' => array(
						'orders' => FS\FS_Orders::get_user_orders( 0, 'new' )
					)
				] ) ?>
            </div>

            <div id="wishlist" class="fs-dashboard__tab">
				<?= fs_frontend_template( 'dashboard/wishlist' ) ?>
            </div>

            <div id="reviews" class="fs-dashboard__tab">
				<?= fs_frontend_template( 'dashboard/reviews' ) ?>
            </div>

        </div><!--.fs-dashboard__tabs-container-->

    </div><!--.fs-dashboard__tabs-->

</div><!--.fs-dashboard-->