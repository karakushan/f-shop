<div class="row">
    <div class="col-md-3">
        <div class="nav flex-column nav-pills" role="tablist" aria-orientation="vertical">
            <a href="#fs-dashboard-sub-tab-1" class="nav-link active"
               data-toggle="pill"><?php esc_html_e( 'Edit profile', 'f-shop' ); ?></a>
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
            <div class="tab-pane fade active show" id="fs-dashboard-sub-tab-1" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( 'Edit profile', 'f-shop' ); ?></h4>
                <fieldset>
                    <legend><?php esc_html_e( 'Personal Information', 'f-shop' ) ?></legend>
                    <div class="form-group row">
                        <label for="fs_first_name" class="col-sm-2"><?php esc_html_e( 'First name', 'f-shop' ); ?> <span
                                    class="required">*</span></label>

                        <div class="col-sm-10"><?php fs_form_field( 'fs_first_name', [ 'class' => 'form-control' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_last_name" class="col-sm-2"><?php esc_html_e( 'Last name', 'f-shop' ); ?></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_last_name', [ 'class' => 'form-control' ] ) ?></div>
                    </div>

                    <div class="form-group row">
                        <label for="fs_user_avatar"
                               class="col-sm-2"><?php esc_html_e( 'Photo', 'f-shop' ); ?></label>

                        <div class="col-sm-10">
                            <div class="fs-user-avatar"
                                 style="background-image:url(<?php echo esc_url( \FS\FS_Users_Class::get_user_avatar_url() ) ?>);">
								<?php fs_form_field( 'fs_user_avatar', [ 'class' => 'form-control ' ] ) ?>
                                <label for="fs_user_avatar" class="btn btn-dark btn-sm"
                                ><?php esc_html_e( 'Choose a photo', 'f-shop' ) ?></label>
                            </div>
                        </div>

                    </div>
                    <div class="form-group row">
                        <label for="fs_gender" class="col-sm-2"><?php esc_html_e( 'Gender', 'f-shop' ); ?></label>

                        <div class="col-sm-10"><?php fs_form_field( 'fs_gender', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                </fieldset>
                <fieldset>
                    <legend><?php esc_html_e( 'Contact details', 'f-shop' ) ?></legend>
                    <div class="form-group row">
                        <label for="fs_country" class="col-sm-2"><?php esc_html_e( 'Country', 'f-shop' ); ?> </label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_country', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_region" class="col-sm-2"><?php esc_html_e( 'Region', 'f-shop' ); ?> <span
                                    class="required">*</span></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_region', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_city" class="col-sm-2"><?php esc_html_e( 'City', 'f-shop' ); ?> <span
                                    class="required">*</span></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_city', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_zip_code" class="col-sm-2"><?php esc_html_e( 'Zip code', 'f-shop' ); ?></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_zip_code', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_adress" class="col-sm-2"><?php esc_html_e( 'Address', 'f-shop' ); ?></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_adress', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_phone" class="col-sm-2"><?php esc_html_e( 'Phone number', 'f-shop' ); ?> <span
                                    class="required">*</span></label>
                        <div class="col-sm-10"><?php fs_form_field( 'fs_phone', [ 'class' => 'form-control col-sm-10' ] ) ?></div>
                    </div>

                </fieldset>
                <button type="submit" class="btn btn-lg btn-primary"><?php esc_html_e( 'Save' ) ?></button>
            </div>
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-2" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( 'Message Setup', 'f-shop' ); ?></h4>
                <fieldset>
                    <legend><?php esc_html_e( 'Message types', 'f-shop' ) ?></legend>
                    <div class="form-group">
						<?php fs_form_field( 'fs_subscribe_news', [ 'class' => 'form-control' ] ) ?>
                    </div>
                    <div class="form-group">
						<?php fs_form_field( 'fs_subscribe_cart', [ 'class' => 'form-control' ] ) ?>
                    </div>

                </fieldset>
                <button type="submit" class="btn btn-lg btn-primary"><?php esc_html_e( 'Save' ) ?></button>
            </div>
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-3" role="tabpanel">
                <h4 class="tab-title"><?php esc_html_e( 'Login and password', 'f-shop' ); ?></h4>
                <fieldset>
                    <legend><?php esc_html_e( 'Login Settings', 'f-shop' ); ?></legend>
                    <div class="form-group row">
                        <label for="fs_login" class="col-sm-2"><?php esc_html_e( 'Username', 'f-shop' ); ?></label>
                        <div class="col"><?php fs_form_field( 'fs_login', [ 'class' => 'form-control' ] ) ?></div>
                    </div>
                    <div class="form-group row">
                        <label for="fs_password" class="col-sm-2"><?php esc_html_e( 'Password', 'f-shop' ); ?></label>
                        <div class="col"><?php fs_form_field( 'fs_password', [ 'class' => 'form-control' ] ) ?></div>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-lg btn-primary"><?php esc_html_e( 'Save' ) ?></button>
            </div>
            <div class="tab-pane fade" id="fs-dashboard-sub-tab-4" role="tabpanel">5</div>
        </div>
    </div>
</div><!-- .row -->
