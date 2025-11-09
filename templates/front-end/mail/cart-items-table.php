<?php
/**
 * Template for cart items table in email
 * This template can be included using %cart_items_table% variable
 * 
 * Available variables:
 * @var array $cart_items Array of cart items
 * @var string $products_cost Products cost
 * @var string $delivery_cost Delivery cost
 * @var string $cart_discount Cart discount
 * @var string $packing_cost Packing cost
 * @var string $cart_amount Total cart amount
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<table cellpadding="0" cellspacing="0" width="600" class="w320">
    <tr>
        <td class="item-table">
            <table cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td class="title-dark" width="300">
                        <?= __('Item', 'f-shop') ?>
                    </td>
                    <td class="title-dark" width="163">
                        <?= __('Qty', 'f-shop') ?>
                    </td>
                    <td class="title-dark" width="97">
                        <?= __('Total', 'f-shop') ?>
                    </td>
                </tr>

                <?php /** @var array $cart_items */
                if (!empty($cart_items) && is_array($cart_items)):
                    foreach ($cart_items as $item): ?>
                        <tr>
                            <td class="item-col item">
                                <table cellspacing="0" cellpadding="0" width="100%">
                                    <tr>
                                        <td class="mobile-hide-img">
                                            <?php if (!empty($item['thumbnail_url'])): ?>
                                                <a href="<?= esc_url($item['link'] ?? '#') ?>" target="_blank">
                                                    <img width="110" src="<?= esc_url($item['thumbnail_url']) ?>"
                                                        alt="<?= esc_attr($item['name'] ?? '') ?>">
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="product">
                                            <?php if (!empty($item['link'])): ?>
                                                <a href="<?= esc_url($item['link']) ?>" target="_blank"
                                                    style="color: #4d4d4d; font-weight:bold;"><?= esc_html($item['name'] ?? '') ?></a>
                                            <?php else: ?>
                                                <span style="color: #4d4d4d; font-weight:bold;"><?= esc_html($item['name'] ?? '') ?></span>
                                            <?php endif; ?>
                                            <br />
                                            <?php if (!empty($item['attr']) && is_array($item['attr'])): ?>
                                                <?php foreach ($item['attr'] as $attribute): ?>
                                                    <?php if (isset($attribute['parent_name']) && isset($attribute['name'])): ?>
                                                        <?= esc_html($attribute['parent_name']) ?> : <?= esc_html($attribute['name']) ?><br/>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td class="item-col quantity">
                                <?= esc_html($item['qty'] ?? 0) ?>
                            </td>
                            <td class="item-col">
                                <?= esc_html($item['all_price'] ?? '0') ?><?= esc_html($item['currency'] ?? '') ?>
                            </td>
                        </tr>
                    <?php endforeach;
                endif; ?>

                <tr>
                    <td class="item-col item">
                    </td>
                    <td class="item-col quantity" colspan="2" style="border-top: 1px solid #cccccc;">
                        <table cellspacing="0" cellpadding="0" width="100%" style="border-collapse:separate !important;">
                            <?php if (!empty($products_cost)): ?>
                                <tr>
                                    <td style="width: 70%; text-align: right; padding: 8px 15px 8px 0;">
                                        <?= __('Cost of goods', 'f-shop') ?>
                                    </td>
                                    <td style="width: 30%; text-align: right; padding: 8px 0;">
                                        <?= esc_html($products_cost) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($delivery_cost)): ?>
                                <tr>
                                    <td style="width: 70%; text-align: right; padding: 8px 15px 8px 0;">
                                        <?= __('Cost of delivery', 'f-shop') ?>
                                    </td>
                                    <td style="width: 30%; text-align: right; padding: 8px 0;">
                                        <?= esc_html($delivery_cost) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($cart_discount)): ?>
                                <tr>
                                    <td style="width: 70%; text-align: right; padding: 8px 15px 8px 0;">
                                        <?= __('Discount', 'f-shop') ?>
                                    </td>
                                    <td style="width: 30%; text-align: right; padding: 8px 0;">
                                        <?= esc_html($cart_discount) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($packing_cost)): ?>
                                <tr>
                                    <td style="width: 70%; text-align: right; padding: 8px 15px 8px 0;">
                                        <?= __('Packing', 'f-shop') ?>
                                    </td>
                                    <td style="width: 30%; text-align: right; padding: 8px 0;">
                                        <?= esc_html($packing_cost) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($cart_amount)): ?>
                                <tr>
                                    <td style="width: 70%; text-align: right; padding: 8px 15px 8px 0; font-weight: bold; color: #4d4d4d">
                                        <?= __('Total', 'f-shop') ?>
                                    </td>
                                    <td style="width: 30%; text-align: right; padding: 8px 0; font-weight: bold; color: #4d4d4d">
                                        <?= esc_html($cart_amount) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

