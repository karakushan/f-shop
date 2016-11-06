<div class="row">
<div class="col-sm-4">
<aside class="sidebar">
    <div class="widget widget-single user-panel">

        <h3 class="widget-title user-panel-title"><span>Панель пользователя</span></h3>
        <ul>
            <li><a href="<?php echo add_query_arg(array('fs-page'=>'conditions')); ?>">Условия работы</a></li>
            <li><a href="<?php echo add_query_arg(array('fs-page'=>'profile')); ?>">Мои данные</a></li>
            <li><a href="<?php echo wp_logout_url(get_permalink()); ?>">Выход</a></li>

        </ul>
    </div>
</aside>
</div>
<div class="col-sm-8">
    <?php fs_page_content() ?>
</div>
</div>