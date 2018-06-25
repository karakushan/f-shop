<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.06.2018
 * Time: 10:11
 */ ?>
<div class="slider" data-fs-element="jquery-ui-slider">
  <ul class="price--interval">
    <li><a href="<?php fs_filter_link(array('price_start'=>0,'price_end'=>1000)) ?>"><i class="icon icon-pluss"></i> до 1000 грн.</a></li>
    <li><a href="<?php fs_filter_link(array('price_start'=>1000,'price_end'=>2000)) ?>"><i class="icon icon-pluss"></i> от 1000 до 2000 грн.</a></li>
    <li><a href="<?php fs_filter_link(array('price_start'=>2000,'price_end'=>3000)) ?>"><i class="icon icon-pluss"></i> от 2500 до 3000 грн.</a></li>
    <li><a href="<?php fs_filter_link(array('price_start'=>3000)) ?>"><i class="icon icon-pluss"></i> от 3000 грн.</a></li>
  </ul>
  <div class="fs-price-show">
    <span data-fs-element="range-start" data-currency="false">0</span>
    <span data-fs-element="range-end" data-currency="false"><?php echo $args['price_max'] ?></span>
    </span>
  </div>
  <div data-fs-element="range-slider" id="range-slider"></div>
  <div class="clearfix"></div>
<div class="price--inputs">
  <span>от <input type="text" value="0" data-fs-element="range-start-input" data-url="<?php fs_filter_link([],null,array('price_start')) ?>"></span>
  <span>до <input type="text" value="<?php echo esc_attr($args['price_max']) ?>" data-fs-element="range-end-input" data-url="<?php fs_filter_link([],null,array('price_end')) ?>"><?php echo $args['currency'] ?>.</span>
</div>
</div>
