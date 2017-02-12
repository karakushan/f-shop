<div class="wrap fast-shop-settings">
    <h2><?php _e('Store settings','fast-shop') ?></h2>
    <form action="<?php echo wp_nonce_url('/wp-admin/edit.php?post_type=product&page=fast-shop-settings','fs_nonce'); ?>" method="post" class="fs-option">
        <div id="fs-options-tabs">
            <ul>
                <li><a href="#tabs-1"><?php _e( 'General', 'fast-shop' ); ?></a></li>
                <li><a href="#tabs-2"><?php _e( 'Letters', 'fast-shop' ); ?></a></li>
                <!-- <li><a href="#tabs-3"><?php _e( 'Stock', 'fast-shop' ); ?></a></li> -->
                <li><a href="#tabs-4"><?php _e( 'Page', 'fast-shop' ); ?></a></li>
                <li><a href="#tabs-5"><?php _e( 'Users', 'fast-shop' ); ?></a></li>
                <!-- <li><a href="#tabs-6"><?php _e( 'Messages', 'fast-shop' ); ?></a></li> -->
                <li><a href="#tabs-7"><?php _e( 'Gallery', 'fast-shop' ); ?></a></li>
                <li><a href="#tabs-8"><?php _e( 'Export', 'fast-shop' ); ?></a></li>
            </ul>
            <div id="tabs-1">
                <p>
                    <label for="">Символ валюты <span>(по умолчанию $):</span></label><br>
                    <input type="text" name="fs_option[currency_symbol]" value="<?php echo fs_currency() ?>">

                </p>
                <p>
                    <label for="currency_delimiter">Разделитель цены <span>(по умолчанию .):</span></label><br>
                    <input type="text" name="fs_option[currency_delimiter]" value="<?php echo fs_option('currency_delimiter','.') ?>">

                </p>
                <p>
                    <label for="currency_delimiter">Использовать копейки</label><br>
                    <input type="checkbox" name="fs_option[price_cents]" value="1" <?php checked(fs_option('price_cents'),1) ?>>

                </p>
            </div>
            <div id="tabs-2">

                <p>
                    <label for="manager_email">Куда отправлять письма <span>(по умолчанию почта админа, можно настроить несколько адресов разделив запятой):</span></label><br>
                    <input type="text" name="fs_option[manager_email]" id="manager_email" value="<?php echo fs_option('manager_email',get_option('admin_email')) ?>">

                </p> 
                <p>
                    <label for="site_logo">Ссылка на логотип сайта в письме <span>отображается в  верхней части письма</span></label><br>
                    <input type="text" name="fs_option[site_logo]" id="site_logo" value="<?php echo fs_option('site_logo') ?>">

                </p>
                <p>
                    <label for="email_sender">Email отправителя писем <span>(используется в заголовке письма, должен совпадать с доменом сайта)</span></label><br>
                    <input type="email" name="fs_option[email_sender]"  id="email_sender" value="<?php echo fs_option('email_sender',get_bloginfo('admin_email')) ?>">
                </p>
                <p>
                    <label for="name_sender">Название отправителя писем <span>(используется в заголовке письма, 2-3 слова не больше, на латиннице)</span></label><br>
                    <input type="text" name="fs_option[name_sender]"  id="name_sender" value="<?php echo fs_option('name_sender',get_bloginfo('name')) ?>">
                </p>
                <p>
                    <label for="">Список переменных для использования в письмах</label><br>
                    <code>
                        %fs_name% - Имя заказчика,
                        %total_amount% - общая сумма покупки,
                        %order_id% - id заказа,
                        %products_listing% - список купленных продуктов,
                        %fs_email% - почта заказчика,
                        %fs_adress% - адрес доставки,
                        %fs_pay% - способ оплаты,
                        %fs_city% - город
                        %fs_delivery% - тип доставки,
                        %fs_phone% - телефон заказчика,
                        %fs_message% - комментарий заказчика,
                        %site_name% - название сайта
                        %admin_url% - адрес админки
                    </code>
                </p>
                <p>
                    <label for="customer_mail_header">Заголовок письма заказчику:</label><br>
                    <input type="text" name="fs_option[customer_mail_header]" id="customer_mail_header" value="<?php echo fs_option('customer_mail_header','Заказ товара на сайте «'.get_bloginfo('name').'»'); ?>">
                </p>
                <p>
                    <label for="customer_mail">Текст письма заказчику после отправки заказа:</label><br>
                    <textarea name="fs_option[customer_mail]" id="customer_mail"  rows="10"><?php echo fs_option('customer_mail') ?></textarea>
                </p>
                <p>
                    <label for="admin_mail_header">Заголовок письма администратору:</label><br>
                    <input type="text" name="fs_option[admin_mail_header]" id="admin_mail_header" value="<?php echo fs_option('admin_mail_header','Заказ товара на сайте «'.get_bloginfo('name').'»'); ?>">
                </p>
                <p>
                    <label for="admin_mail">Текст письма администратору после отправки заказа:</label><br>
                    <textarea name="fs_option[admin_mail]" id="admin_mail"  rows="10"><?php echo fs_option('admin_mail') ?></textarea>
                </p>
                
            </div>
         <!--    <div id="tabs-3">
         
             <p>
                 <label for="action_pcount">К-во товаров при которых активируется скидка:</label><br>
                 <input type="number" min="1" name="fs_option[action_pcount]" id="action_pcount" value="<?php echo fs_option('action_pcount') ?>">
             </p>
             <p>
                 <label>Акционная скидка считается в :</label><br>
                 <label class="label-light"> <input type="radio" name="fs_option[action_count]" id="action_count1" value="0" <?php checked('0',fs_option('action_count')) ?> <?php checked('',fs_option('action_count')) ?>>в фиксированом к-ве</label><br>
                 <label class="label-light"><input type="radio" name="fs_option[action_count]" id="action_count2" value="1" <?php checked('1',fs_option('action_count')) ?>>в процентах</label>
             </p>
             <p>
                 <label for="action_summa">Размер скидки <span>(действует глобально, по всему сайту)</span></label><br>
                 <input type="number" min="1" name="fs_option[action_summa]" id="action_summa" value="<?php echo fs_option('action_summa') ?>">
             </p>
             <p>
                 <label for="action_label">Включать отметку акция атоматически <span>(надпись акция включится без отметки чекбокса, при наличии акционной цены)</span></label><br>
                 <input type="checkbox" name="fs_option[action_label]" value="1" <?php checked(1,fs_option('action_label')) ?>>
         
             </p>
         
         </div> -->
            <div id="tabs-4">
                <p>
                    <label for="page_cart">Страница корзины:</label><br>
                    <?php
                    $query=new WP_Query(array('post_type'=>'page','posts_per_page'=>-1)); ?>

                    <select name="fs_option[page_cart]" id="page_cart">
                        <option value="">Выберите страницу</option>
                        <?php  if ( $query->have_posts() ) : ?>
                            <?php  while ($query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if (get_post_status(get_the_ID())!='publish') echo 'disabled' ?> <?php selected(get_the_ID(),fs_option('page_cart')) ?> ><?php the_title() ?></option>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php else: ?>
                        <?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница оплаты:</label><br>
                    <?php
                    $query=new WP_Query(array('post_type'=>'page','posts_per_page'=>-1)); ?>

                    <select name="fs_option[page_payment]" id="page_payment">
                        <option value="">Выберите страницу:</option>
                        <?php  if ( $query->have_posts() ) : ?>
                            <?php  while ($query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if (get_post_status(get_the_ID())!='publish') echo 'disabled' ?> <?php selected(get_the_ID(),fs_option('page_payment')) ?> ><?php the_title() ?></option>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php else: ?>
                        <?php endif; ?>
                    </select>

                </p>
                <p>
                    <label for="page_cart">Страница успешной отправки заказа:</label><br>
                    <?php
                    $query=new WP_Query(array('post_type'=>'page','posts_per_page'=>-1)); ?>

                    <select name="fs_option[page_success]" id="page_success">
                        <option value="">Выберите страницу</option>
                        <?php  if ( $query->have_posts() ) : ?>
                            <?php  while ($query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if (get_post_status(get_the_ID())!='publish') echo 'disabled' ?> <?php selected(get_the_ID(),fs_option('page_success')) ?> ><?php the_title() ?></option>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php else: ?>
                        <?php endif; ?>
                    </select>

                </p> 
                <p>
                    <label for="page_cart">Страница списка желаний:</label><br>
                    <?php
                    $query=new WP_Query(array('post_type'=>'page','posts_per_page'=>-1)); ?>

                    <select name="fs_option[page_whishlist]" id="page_whishlist">
                        <option value="">Выберите страницу</option>
                        <?php  if ( $query->have_posts() ) : ?>
                            <?php  while ($query->have_posts() ) : $query->the_post(); ?>
                                <option value="<?php the_ID() ?>" <?php if (get_post_status(get_the_ID())!='publish') echo 'disabled' ?> <?php selected(get_the_ID(),fs_option('page_whishlist')) ?> ><?php the_title() ?></option>
                            <?php endwhile; wp_reset_query(); ?>
                        <?php else: ?>
                        <?php endif; ?>
                    </select>

                </p>
            </div>
            <div id="tabs-5">
                <p>
                    <label for="register_user">Регистрировать пользователя при покупке</label><br>
                    <input type="checkbox" name="fs_option[register_user]" id="register_user" value="1" <?php checked(fs_option('register_user'),1) ?>>
                </p>
            </div> 
           <!--  <div id="tabs-6">
               <p>
                   <label for="register_user">Показывать модальное окно поле добавления товара в корзину</label><br>
                   <input type="checkbox" name="fs_option[order_modal]" id="order_modal" value="1" <?php checked(fs_option('order_modal'),1) ?>>
               </p>
               <p>
                   <label for="register_user">Идентификатор модального окна</label><br>
                   <input type="text" name="fs_option[order_modal_id]" id="order_modal_id" value="<?php echo fs_option('order_modal_id') ?>">
               </p>
           </div> -->
            <div id="tabs-7">
                <h2>Настройки галереи в карточке товара</h2>
                <p>Внимание! Для работы слайдера необходимо, чтобы ваша тема поддерживала загрузку миниатюр.</p>
                <p>
                    <label for="image_placeholder">Заглушка изображения <span>отображается если галерея не загружена</span></label><br>
                    <input type="text" name="fs_option[image_placeholder]"  id="image_placeholder" value="<?php echo fs_option('image_placeholder') ?>">
                </p>
                <h3>Большое изображение</h3>

                <p>
                    <label for="gallery_img_width">Ширина большого изображения</label><br>
                    <input type="text" name="fs_option[gallery_big_width]"  id="gallery_img_width" value="<?php echo fs_option('gallery_big_width') ?>">
                </p>
                <p>
                    <label for="gallery_img_height">Высота большого изображения</label><br>
                    <input type="text" name="fs_option[gallery_big_height]"  id="gallery_img_height" value="<?php echo fs_option('gallery_big_height') ?>">
                </p>

                <h3>Маленькие изображения</h3>
                <p>
                <label for="gallery_img_width">Ширина маленького изображения</label><br>
                    <input type="text" name="fs_option[gallery_small_width]"  id="gallery_img_width" value="<?php echo fs_option('gallery_small_width') ?>">
                </p>
                <p>
                <label for="gallery_img_height">Высота маленького изображения</label><br>
                    <input type="text" name="fs_option[gallery_small_height]"  id="gallery_img_height" value="<?php echo fs_option('gallery_small_height') ?>">
                </p>

            </div>
            <div id="tabs-8">
                <h2><?php _e('Settings export and import of goods', 'fast-shop' ); ?></h2>
                <p>
                    <label for="company_name">Название компании</label><br>
                    <input type="text" name="fs_option[company_name]"  id="company_name" value="<?php echo fs_option('company_name',get_bloginfo('name')) ?>">
                </p>
                <p>
                    <label for="export_format">Формат экспорта</label><br>
                    <input type="text" name="fs_option[export_format]"  id="export_format" value="<?php echo fs_option('export_format','xml') ?>">
                </p>
                <p>
                    <label for="export_shedule">Автоматический экспорт</label><br>
                    <select name="fs_option[export_shedule]" id="export_shedule">
                        <option value="">Никогда</option>
                        <?php $schedules = wp_get_schedules(); ?>
                        <?php if ($schedules): ?>
                            <?php foreach ($schedules as $key => $schedule): ?>
                                <option value="<?php echo $key ?>" <?php selected($key,fs_option('export_shedule','')) ?>><?php echo $schedule['display'] ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                    </select>
                </p>
                <p>
                    <a href="<?php echo wp_nonce_url(add_query_arg(array('fs_action'=>'export_yml')),'fs_action'); ?>" class="fs-btn fs-btn-green ">Запустить экпорт</a>
                </p>
                <p>
                    <?php $upload_dir=wp_upload_dir('shop');
                    $format=fs_option('export_format','xml'); ?>
                    <a href="<?php echo $upload_dir['url'].'products.'.$format ?>" class="fs-btn" download>Скачать базу товаров</a>
                </p>
                
            </div>
        </div>
        <input type="submit" name="fs_save_options" value="Сохранить">
    </form>
</div>