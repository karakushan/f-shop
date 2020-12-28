<div class="row">
    <div class="col-md-3">
        <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
            <a href="#fs-dashboard-sub-tab-1" class="nav-link active"
               data-toggle="pill">
				<?php esc_html_e( 'Edit profile', 'f-shop' ); ?></a>
            <a href="#fs-dashboard-sub-tab-2" class="nav-link"
               data-toggle="pill"><?php esc_html_e( 'Message Setup', 'f-shop' ); ?></a>
            <a href="#fs-dashboard-sub-tab-3" class="nav-link"
               data-toggle="pill"><?php esc_html_e( 'Login and password', 'f-shop' ); ?></a>
            <a href="#fs-dashboard-sub-tab-4" class="nav-link"
               data-toggle="pill"><?php esc_html_e( 'Deleting an account', 'f-shop' ); ?></a>
        </div>
    </div>
    <div class="col">
        <div class="tab-content">

            <!-- Tab: Edit profile -->
            <div class="tab-pane fade active show in" id="fs-dashboard-sub-tab-1" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( '', 'f-shop' ); ?></h4>
				<?php echo FS\FS_Form::form_open( array(
					'class'       => 'fs-dashboard-personal',
					'id'          => 'fs-save-user-data',
					'name'        => 'fs-save-user-data',
					'ajax_action' => 'fs_save_user_data'
				) ); ?>
				<?php fs_form_field( 'fs_first_name', [
					'label' => __( 'First name', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_last_name', [
					'label' => __( 'Last name', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

                <div class="form-group row">
                    <label for="fs_user_avatar" class="col-sm-2"><?php esc_html_e( 'Photo', 'f-shop' ); ?></label>
                    <div class="col-sm-4">
                        <div class="fs-user-avatar"
                             style="background-image:url(<?php echo esc_url( \FS\FS_Users::get_user_avatar_url() ) ?>);">
							<?php fs_form_field( 'fs_user_avatar', [ 'class' => 'form-control ' ] ) ?>
                            <label for="fs_user_avatar" class="btn btn-dark btn-sm"
                            ><?php esc_html_e( 'Choose a photo', 'f-shop' ) ?></label>
                        </div>
                    </div>
                </div>

				<?php fs_form_field( 'fs_gender', [
					'label' => __( 'Gender', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_country', [
					'label' => __( 'Country', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_region', [
					'label' => __( 'Region', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_city', [
					'label' => __( 'City', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_zip_code', [
					'label' => __( 'Zip code', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_address', [
					'label' => __( 'Address', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

				<?php fs_form_field( 'fs_phone', [
					'label' => __( 'Phone number', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4'
				] ) ?>

                <button type="submit" class="btn btn-lg btn-primary bts bts-lg"><?php esc_html_e( 'Save' ) ?></button>
				<?php echo FS\FS_Form::form_close(); ?>
            </div>

            <!-- Tab: Message Setup -->
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-2" role="tabpanel">
				<?php echo FS\FS_Form::form_open( array(
					'class'       => 'fs-dashboard-personal',
					'id'          => 'fs-save-user-data',
					'name'        => 'fs-save-user-data',
					'ajax_action' => 'fs_save_user_data'
				) ); ?>
                <h4 class="tab-title"><?php esc_html_e( 'Message Setup', 'f-shop' ); ?></h4>
				<?php fs_form_field( 'fs_subscribe_news', [
					'class'         => 'form-check-input',
					'wrapper_class' => 'form-group form-check form-switch',
					'label_class'   => 'form-check-label'
				] ) ?>

				<?php fs_form_field( 'fs_subscribe_cart', [
					'class'         => 'form-check-input',
					'wrapper_class' => 'form-group form-check form-switch',
					'label_class'   => 'form-check-label'
				] ) ?>
                <button type="submit" class="btn btn-lg btn-primary bts bts-lg"><?php esc_html_e( 'Save' ) ?></button>
				<?php echo FS\FS_Form::form_close(); ?>
            </div>

            <!-- Tab: Login and password -->
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-3" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( 'Login and password', 'f-shop' ); ?></h4>
				<?php echo FS\FS_Form::form_open( array(
					'class'       => 'fs-dashboard-personal',
					'id'          => 'fs_change_login',
					'name'        => 'fs_change_login',
					'ajax'        => 'on',
					'ajax_action' => 'fs_change_login'
				) ); ?>

				<?php fs_form_field( 'fs_login', [
					'label' => __( 'Username', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4',
					'required' => false
				] ) ?>

				<?php fs_form_field( 'fs_password', [
					'label' => __( 'Password', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4',
					'required' => false
				] ) ?>

                <button type="submit" class="btn btn-lg btn-primary bts bts-lg"><?php esc_html_e( 'Save' ) ?></button>
				<?php echo FS\FS_Form::form_close(); ?>
            </div>

            <!-- Tab: Account deleting -->
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-4" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( 'Account deleting', 'f-shop' ); ?></h4>
				<?php echo FS\FS_Form::form_open( array(
					'class'       => 'fs-dashboard-personal',
					'id'          => 'fs-save-user-data',
					'name'        => 'fs_delete_account',
					'ajax'        => 'on',
					'ajax_action' => 'fs_delete_account'
				) ); ?>
				<?php fs_form_field( 'fs_password', [
					'label' => __( 'Current password', 'f-shop' ),
					'label_class' => 'col-form-label fs-form-label col-sm-2',
					'wrapper_class' => 'form-group row',
					'class' => 'form-control col-sm-4',
					'required' => true
				] ) ?>

                <button type="submit" class="btn btn-lg btn-primary bts bts-lg"><?php esc_html_e( 'Save' ) ?></button>
				<?php echo FS\FS_Form::form_close(); ?>
            </div>

        </div>
    </div>
</div><!-- .row -->