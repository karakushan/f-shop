<div class="wrap fast-shop-settings">
    <h2><?php _e( 'Store settings', 'fast-shop' ) ?></h2>
    <form action="<?php echo wp_nonce_url( '/wp-admin/edit.php?post_type=product&page=fast-shop-settings', 'fs_nonce' ); ?>"
          method="post" class="fs-option">
        <div id="fs-options-tabs">
            <ul>
                <li><a href="#tabs-1"><?php _e( 'General', 'fast-shop' ); ?></a></li>
                <li><a href="#tabs-2"><?php _e( 'Letters', 'fast-shop' ); ?></a></li>
                <!-- <li><a href="#tabs-3"><?php _e( 'Stock', 'fast-shop' ); ?></a></li> -->
                <li><a href="#tabs-4"><?php _e( 'Page', 'fast-shop' ); ?></a></li>
                <li><a href="#tabs-5"><?php _e( 'Users', 'fast-shop' ); ?></a></li>
                <!-- <li><a href="#tabs-6"><?php _e( 'Messages', 'fast-shop' ); ?></a></li> -->
                <!--<li><a href="#tabs-7"><?php /*_e( 'Gallery', 'fast-shop' ); */?></a></li>-->
                <li><a href="#tabs-8"><?php _e( 'Export', 'fast-shop' ); ?></a></li>
            </ul>
            <div id="tabs-1">
                <p>
                    <label for="">Символ валюты <span>(по умолчанию $):</span></label><br>
                    <input type="text" name="fs_option[currency_symbol]" value="<?php echo fs_currency() ?>">

                </p>
                <p>
                    <label for="currency_delimiter">Разделитель цены <span>(по умолчанию .):</span></label><br>
                    <input type="text" name="fs_option[currency_delimiter]"
                           value="<?php echo fs_option( 'currency_delimiter', '.' ) ?>">

                </p>
                <p>
                    <label for="currency_delimiter">Использовать копейки</label><br>
                    <input type="checkbox" name="fs_option[price_cents]"
                           value="1" <?php checked( fs_option( 'price_cents' ), 1 ) ?>>

                </p>
            </div>
            <div id="tabs-2">

                <p>
                    <label for="manager_email">Куда отправлять письма <span>(по умолчанию почта админа, можно настроить несколько адресов разделив запятой):</span></label><br>
                    <input type="text" name="fs_option[manager_email]" id="manager_email"
                           value="<?php echo fs_option( 'manager_email', get_option( 'admin_email' ) ) ?>">

                </p>
                <p>
                    <label for="site_logo">Ссылка на логотип сайта в письме
                        <span>отображается в  верхней части письма</span></label><br>
                    <input type="text" name="fs_option[site_logo]" id="site_logo"
                           value="<?php echo fs_option( 'site_logo' ) ?>">

                </p>
                <p>
                    <label for="email_sender">Email отправителя писем <span>(используется в заголовке письма, должен совпадать с доменом сайта)</span></label><br>
                    <input type="email" name="fs_option[email_sender]" id="email_sender"
                           value="<?php echo fs_option( 'email_sender', get_bloginfo( 'admin_email' ) ) ?>">
                </p>
                <p>
                    <label for="name_sender">Название отправителя писем <span>(используется в заголовке письма, 2-3 слова не больше, на латиннице)</span></label><br>
                    <input type="text" name="fs_option[name_sender]" id="name_sender"
                           value="<?php echo fs_option( 'name_sender', get_bloginfo( 'name' ) ) ?>">
                </p>
                <p>
                    <label for="customer_mail_header">Заголовок письма заказчику:</label><br>
                    <input type="text" name="fs_option[customer_mail_header]" id="customer_mail_header"
                           value="<?php echo fs_option( 'customer_mail_header', 'Заказ товара на сайте «' . get_bloginfo( 'name' ) . '»' ); ?>">
                </p>
                <p>
                    <label for="customer_mail">Текст письма заказчику после отправки заказа:</label><br>
					<?php wp_editor( fs_option( 'customer_mail' ), 'customer_mail', array(
						'wpautop'          => 1,
						'media_buttons'    => 1,
						'textarea_name'    => 'fs_option[customer_mail]',
						'textarea_rows'    => 8,
						'tabindex'         => null,
						'editor_css'       => '',
						'editor_class'     => '',
						'teeny'            => 0,
						'dfw'              => 0,
						'tinymce'          => 1,
						'quicktags'        => 1,
						'drag_drop_upload' => false
					) ) ?>
                </p>
                <p>
                    <label for="mail_social">Блок соц сетей:</label><br>

					<?php
					$social_def = '<a href=""><img width="44" height="47"src="http://s3.amazonaws.com/swu-filepicker/k8D8A7SLRuetZspHxsJk_social_08.gif" alt="twitter"/></a>
                                            <a href=""><img width="38" height="47" src="http://s3.amazonaws.com/swu-filepicker/LMPMj7JSRoCWypAvzaN3_social_09.gif" alt="facebook"/></a>
                                            <a href=""><img width="40" height="47" src="http://s3.amazonaws.com/swu-filepicker/hR33ye5FQXuDDarXCGIW_social_10.gif"
                                                            alt="rss"/></a>';
					wp_editor( fs_option( 'mail_social', $social_def ), 'mail_social', array(
						'wpautop'          => 1,
						'media_buttons'    => 1,
						'textarea_name'    => 'fs_option[mail_social]',
						'textarea_rows'    => 4,
						'tabindex'         => null,
						'editor_css'       => '',
						'editor_class'     => '',
						'teeny'            => 0,
						'dfw'              => 0,
						'tinymce'          => 1,
						'quicktags'        => 1,
						'drag_drop_upload' => false
					) ) ?>
                </p>
                <p>
                    <label for="form_footer_text">Текст внизу письма:</label><br>
					<?php wp_editor( fs_option( 'form_footer_text' ), 'form_footer_text', array(
						'wpautop'          => 1,
						'media_buttons'    => 1,
						'textarea_name'    => 'fs_option[form_footer_text]',
						'textarea_rows'    => 5,
						'tabindex'         => null,
						'editor_css'       => '',
						'editor_class'     => '',
						'teeny'            => 0,
						'dfw'              => 0,
						'tinymce'          => 1,
						'quicktags'        => 1,
						'drag_drop_upload' => false
					) ) ?>
                </p>
                <p>
                    <label for="admin_mail">Текст письма администратору после отправки заказа:</label><br>
					<?php wp_editor( fs_option( 'admin_mail' ), 'admin_mail', array(
						'wpautop'          => 1,
						'media_buttons'    => 1,
						'textarea_name'    => 'fs_option[admin_mail]',
						'textarea_rows'    => 8,
						'tabindex'         => null,
						'editor_css'       => '',
						'editor_class'     => '',
						'teeny'            => 0,
						'dfw'              => 0,
						'tinymce'          => 1,
						'quicktags'        => 1,
						'drag_drop_upload' => false
					) ) ?>
                </p>

            </div>
            <div id="tabs-4">
                <p>
                    <label for="page_cart">Страница корзины:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_cart]" id="page_cart">
                        <option value="">Выберите страницу</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_cart' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница оплаты:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_payment]" id="page_payment">
                        <option value="">Выберите страницу:</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_payment' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница успешной отправки заказа:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_success]" id="page_success">
                        <option value="">Выберите страницу</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_success' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница списка желаний:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_whishlist]" id="page_whishlist">
                        <option value="">Выберите страницу</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_whishlist' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница личного кабинета:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_cabinet]" id="page_whishlist">
                        <option value="">Выберите страницу</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_cabinet' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница авторизации:</label><br>
					<?php
					$query = new WP_Query( array( 'post_type' => 'page', 'posts_per_page' => - 1 ) ); ?>

                    <select name="fs_option[page_auth]" id="page_whishlist">
                        <option value="">Выберите страницу</option>
						<?php if ( $query->have_posts() ) : ?>
							<?php while ( $query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if ( get_post_status( get_the_ID() ) != 'publish' )
									echo 'disabled' ?> <?php selected( get_the_ID(), fs_option( 'page_auth' ) ) ?> ><?php the_title() ?></option>
							<?php endwhile;
							wp_reset_query(); ?>
						<?php else: ?>
						<?php endif; ?>
                    </select>

                </p>
            </div>
            <div id="tabs-5">
                <p>
                    <label for="register_user">Регистрировать пользователя при покупке</label><br>
                    <input type="checkbox" name="fs_option[register_user]" id="register_user"
                           value="1" <?php checked( fs_option( 'register_user' ), 1 ) ?>>
                </p>
            </div>

            <div id="tabs-8">
                <h2><?php _e( 'Settings export and import of goods', 'fast-shop' ); ?></h2>
                <p>
                    <label for="company_name">Название компании</label><br>
                    <input type="text" name="fs_option[company_name]" id="company_name"
                           value="<?php echo fs_option( 'company_name', get_bloginfo( 'name' ) ) ?>">
                </p>
                <p>
                    <label for="export_format">Формат экспорта</label><br>
                    <input type="text" name="fs_option[export_format]" id="export_format"
                           value="<?php echo fs_option( 'export_format', 'xml' ) ?>">
                </p>
                <p>
                    <label for="export_shedule">Автоматический экспорт</label><br>
                    <select name="fs_option[export_shedule]" id="export_shedule">
                        <option value="">Никогда</option>
						<?php $schedules = wp_get_schedules(); ?>
						<?php if ( $schedules ): ?>
							<?php foreach ( $schedules as $key => $schedule ): ?>
                                <option value="<?php echo $key ?>" <?php selected( $key, fs_option( 'export_shedule', '' ) ) ?>><?php echo $schedule['display'] ?></option>
							<?php endforeach ?>
						<?php endif ?>
                    </select>
                </p>
                <p>
                    <a href="<?php echo wp_nonce_url( add_query_arg( array( 'fs_action' => 'export_yml' ) ), 'fs_action' ); ?>"
                       class="fs-btn fs-btn-green ">Запустить экпорт</a>
                </p>
                <p>
					<?php $upload_dir = wp_upload_dir( 'shop' );
					$format           = fs_option( 'export_format', 'xml' ); ?>
                    <a href="<?php echo $upload_dir['url'] . 'products.' . $format ?>" class="fs-btn" download>Скачать
                        базу товаров</a>
                </p>

            </div>
        </div>
        <input type="submit" name="fs_save_options" value="Сохранить">
    </form>
</div>