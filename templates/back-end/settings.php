<div class="wrap fast-shop-settings">
    <h2>Fast Shop настройки</h2>
    <p>Вы можете изменять настройки во всех вкладках. После изменнения настроек не забудьте сохранить.</p>
    <form action="<?php echo wp_nonce_url($_SERVER['REQUEST_URI'],'fs_nonce'); ?>" method="post" class="fs-option">
        <div id="fs-options-tabs">
            <ul>
                <li><a href="#tabs-1">Общие настройки</a></li>
                <li><a href="#tabs-2">Письма</a></li>
                <li><a href="#tabs-3">Акции</a></li>
                <li><a href="#tabs-4">Страницы</a></li>


            </ul>

            <div id="tabs-1">
                <p>
                    <label for="">Символ валюты <span>(по умолчанию $):</span></label><br>
                    <input type="text" name="fs_option[currency_symbol]" value="<?php echo fs_currency() ?>">

                </p>
            </div>
            <div id="tabs-2">
                <p>
                    <label for="manager_email">Куда отправлять письма <span>(по умолчанию почта админа):</span></label><br>
                    <input type="email" name="fs_option[manager_email]" id="manager_email" value="<?php echo fs_option('manager_email',get_option('admin_email')) ?>">

                </p>
                <p>
                    <label for="customer_mail">Текст письма заказчику после отправки заказа:</label><br>
                    <textarea name="fs_option[customer_mail]" id="customer_mail"  rows="10"><?php echo fs_option('customer_mail') ?></textarea>
                </p>
                <p>
                    <label for="admin_mail">Текст письма администратору после отправки заказа:</label><br>
                    <textarea name="fs_option[admin_mail]" id="admin_mail"  rows="10"><?php echo fs_option('admin_mail') ?></textarea>
                </p>
            </div>
            <div id="tabs-3">
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
                    <label for="action_summa">Размер скидки</label><br>
                    <input type="number" min="1" name="fs_option[action_summa]" id="action_summa" value="<?php echo fs_option('action_summa') ?>">
                </p>
            </div>
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
                    <label for="page_cart">Страница успешного заказа:</label><br>
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
            </div>
        </div>
        <input type="submit" name="fs_save_options" value="Сохранить">
    </form>
</div>