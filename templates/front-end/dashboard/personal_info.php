<div class="fs-dashboard__tabs">
    <ul class="fs-dashboard__nav fs-layout-verical">
        <li class="active">
            <a href="#fs-dashboard-sub-tab-1">
				<?php esc_html_e( 'Edit profile', 'f-shop' ); ?></a>
        </li>
        <li>
            <a href="#fs-dashboard-sub-tab-2"><?php esc_html_e( 'Message Setup', 'f-shop' ); ?></a>
        </li>
        <li>
            <a href="#fs-dashboard-sub-tab-3"><?php esc_html_e( 'Login and password', 'f-shop' ); ?></a>
        </li>
        <li>
            <a href="#fs-dashboard-sub-tab-4"><?php esc_html_e( 'Deleting an account', 'f-shop' ); ?></a>
        </li>
    </ul><!--.fs-dashboard__nav-->

    <div class="fs-dashboard__tabs-container">

        <div id="fs-dashboard-sub-tab-1" class="fs-dashboard__tab active">

            <div class="fs-dashboard__tab-title"><?php esc_html_e( 'Edit profile', 'f-shop' ); ?></div>

			<?= FS\FS_Form::form_open( array(
				'class'       => 'fs-dashboard-personal',
				'id'          => 'fs-save-user-data',
				'name'        => 'fs-save-user-data',
				'ajax_action' => 'fs_save_user_data'
			) );
			do_action( 'qm/debug', $user );
			?>

			<?php fs_form_field( 'fs_first_name', [ 'value' => $user->first_name ] ) ?>

			<?php fs_form_field( 'fs_last_name', [ 'value' => $user->last_name ] ) ?>

			<?php fs_form_field( 'fs_phone', [ 'value' => $user->phone ] ) ?>

			<?php fs_form_field( 'fs_email', [ 'value' => $user->email ] ) ?>

			<?php fs_form_field( 'fs_user_avatar' ) ?>

			<?php fs_form_field( 'fs_gender', [ 'value' => $user->gender ] ) ?>

			<?php fs_form_field( 'fs_country', [ 'value' => $user->country ] ) ?>

			<?php fs_form_field( 'fs_region', [ 'value' => $user->region ] ) ?>

			<?php fs_form_field( 'fs_city', [ 'value' => $user->city ] ) ?>

			<?php fs_form_field( 'fs_address', [ 'value' => $user->address ] ) ?>

            <button type="submit"><?php esc_html_e( 'Save' ) ?></button>

			<?= FS\FS_Form::form_close(); ?>

        </div><!--.fs-dashboard__tab-->

        <div id="fs-dashboard-sub-tab-2" class="fs-dashboard__tab">

			<?php echo FS\FS_Form::form_open( array(
				'class'       => 'fs-dashboard-personal',
				'id'          => 'fs-save-user-data',
				'name'        => 'fs-save-user-data',
				'ajax_action' => 'fs_save_user_data'
			) ); ?>

            <div class="fs-dashboard__tab-title"><?php esc_html_e( 'Message Setup', 'f-shop' ); ?></div>

            <div class="fs-dashboard__checkbox">
                <label class="switch" for="fs_subscribe_news">
                    <input type="checkbox" name="fs_subscribe_news" id="fs_subscribe_news"/>
                    <div class="slider round"></div>
                </label>
                <label for="fs_subscribe_news">Получать новости сайта и предложения</label>
            </div>

            <div class="fs-dashboard__checkbox">
                <label class="switch" for="fs_notify_availability">
                    <input type="checkbox" name="fs_notify_availability" id="fs_notify_availability"/>
                    <div class="slider round"></div>
                </label>
                <label for="fs_notify_availability">Уведомлять о появившемся товаре</label>
            </div>

            <button type="submit"><?php esc_html_e( 'Save' ) ?></button>

			<?php echo FS\FS_Form::form_close(); ?>

        </div><!--.fs-dashboard__tab-->

        <div id="fs-dashboard-sub-tab-3" class="fs-dashboard__tab">

            <div class="fs-dashboard__tab-title"><?php esc_html_e( 'Login and password', 'f-shop' ); ?></div>
			<?php echo FS\FS_Form::form_open( array(
				'class'       => 'fs-dashboard-personal',
				'id'          => 'fs_change_login',
				'name'        => 'fs_change_login',
				'ajax'        => 'on',
				'ajax_action' => 'fs_change_login'
			) ); ?>

			<?php fs_form_field( 'fs_login' ) ?>

			<?php fs_form_field( 'fs_password' ) ?>

            <button type="submit"><?php esc_html_e( 'Save' ) ?></button>

			<?php echo FS\FS_Form::form_close(); ?>

        </div><!--.fs-dashboard__tab-->

        <div id="fs-dashboard-sub-tab-4" class="fs-dashboard__tab">

            <div class="fs-dashboard__tab-title"><?php esc_html_e( 'Account deleting', 'f-shop' ); ?></div>
			<?php echo FS\FS_Form::form_open( array(
				'class'       => 'fs-dashboard-personal',
				'id'          => 'fs-save-user-data',
				'name'        => 'fs_delete_account',
				'ajax'        => 'on',
				'ajax_action' => 'fs_delete_account'
			) ); ?>

			<?php fs_form_field( 'fs_password' ) ?>

            <button type="submit"><?php esc_html_e( 'Save' ) ?></button>

			<?php echo FS\FS_Form::form_close(); ?>
        </div>

    </div><!--.fs-dashboard__tabs-container-->
</div>