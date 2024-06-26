<div class="fs-reset-widget">
    <div class="fs-reset-widget__top">
        <?php echo esc_html(sprintf(_n( 'Selected: %s product','Selected: %s products', $count, 'f-shop' ),$count)
            ) ?>
    </div>
    <div class="fs-reset-widget__body">
        <a href="<?php fs_reset_filter_link(); ?>" class="fs-reset-widget__btn"
           title="<?php esc_html_e('Reset all', 'f-shop'); ?>"><?php esc_html_e('Reset all', 'f-shop'); ?></a>
        <?php foreach ($links as $link): ?>
            <a href="<?php echo esc_url($link['url']); ?>"><?php echo esc_html($link['name']); ?></a>
        <?php endforeach; ?>
    </div>
</div>
<?php
