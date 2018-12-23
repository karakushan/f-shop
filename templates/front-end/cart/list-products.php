<?php
/**
 * Product Basket Template
 */
?>
<div class="fs-cart-listing">
    <?php if ($cart): ?>
        <table class="table table-condensed">
            <thead class="thead-light">
            <tr>
                <td>
                    <?php esc_html_e('Photo', 'f-shop'); ?>
                </td>
                <td>
                    <?php esc_html_e('Product', 'f-shop'); ?>
                </td>
                <td>
                    <?php esc_html_e('Vendor code', 'f-shop'); ?>
                </td>
                <td>
                    <?php esc_html_e('Price', 'f-shop'); ?>
                </td>
                <td>
                    <?php esc_html_e('Quantity', 'f-shop'); ?>
                </td>
                <td>
                    <?php esc_html_e('Cost', 'f-shop'); ?>
                </td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($cart as $item_id => $product): ?>
                <?php $item = fs_set_product($product, $item_id) ?>
                <tr>
                    <td>
                        <?php fs_product_thumbnail($item->id) ?>
                    </td>
                    <td>
                        <div class="info">
                            <a href="<?php echo esc_url($item->permalink) ?>" target="_blank"
                               class="name"><?php echo esc_html($item->title) ?></a>
                        </div>
                    </td>
                    <td>
                        <?php $item->the_sku() ?>
                    </td>
                    <td>
                        <?php $item->the_price() ?>
                    </td>
                    <td>
                        <?php $item->cart_quantity(array(
                            'pluss' => array(
                                'class' => 'fs-pluss',
                                'content' => '<span class="glyphicon glyphicon-plus"></span>'
                            ),
                            'minus' => array(
                                'class' => 'fs-minus',
                                'content' => '<span class="glyphicon glyphicon-minus"></span>'
                            ),
                        )) ?>
                    </td>
                    <td>
                        <?php $item->the_cost() ?>
                    </td>
                    <td>
                        <?php $item->delete_position(array(
                            'class' => 'fs-remove-position',
                            'type' => 'button',
                            'content' => '&times;'
                        )) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <div class="fs-products-after">
            <div class="row">
                <div class="col">
                    <?php fs_delete_cart(array(
                        'type' => 'button',
                        'class' => 'btn btn-danger'
                    )) ?>
                </div>
                <div class="col">
                    <a href="<?php fs_checkout_url() ?>" class="btn btn-success">
                        <?php esc_html_e('Checkout', 'f-shop') ?>
                    </a>
                </div>
            </div>

        </div>

    <?php else: ?>
        <p class="fs-info-block"><span
                    class="icon glyphicon glyphicon-info-sign"></span> <?php esc_html_e('Your basket is empty', 'f-shop') ?>
            .&nbsp;
            <a
                    href="<?php echo esc_url(fs_get_catalog_link()) ?>"><?php esc_html_e('To the catalog', 'f-shop') ?></a>
        </p>
    <?php endif; ?>
</div>
