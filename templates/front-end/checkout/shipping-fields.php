<?php
/**
 * Shipping fields template for F-Shop checkout page.
 *
 * This template displays shipping form fields that can be conditionally shown/hidden
 * based on the selected shipping method. Uses Alpine.js for dynamic field visibility.
 *
 * Fields:
 * - City (fs_city)
 * - Delivery Number (fs_delivery_number)
 * - Shipping Address (fs_shipping_address)
 */
?>
<div class="form-group" x-show="shipping_methods.find(method => method.id === parseInt(fs_delivery_method))?.disableFields?.includes('fs_city') !== true">
	<?php fs_form_field('fs_city'); ?>
</div>
<div class="form-group" x-show="shipping_methods.find(method => method.id === parseInt(fs_delivery_method))?.disableFields?.includes('fs_delivery_number') !== true">
	<?php fs_form_field('fs_delivery_number'); ?>
</div>
<div class="form-group" x-show="shipping_methods.find(method => method.id === parseInt(fs_delivery_method))?.disableFields?.includes('fs_shipping_address') !== true">
	<?php fs_form_field('fs_shipping_address'); ?>
</div>