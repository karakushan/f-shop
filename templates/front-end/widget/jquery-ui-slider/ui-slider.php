<?php
/**
 * Created by PhpStorm.
 * User: karak
 * Date: 25.06.2018
 * Time: 10:11
 */ ?>
<div class="slider" data-fs-element="jquery-ui-slider">
  <ul class="price--interval">
    <li><a href=""><i class="icon icon-pluss"></i> до 1000 грн.</a></li>
    <li><a href=""><i class="icon icon-pluss"></i> от 1500 до 2500 грн.</a></li>
    <li><a href=""><i class="icon icon-pluss"></i> от 2500 до 3000 грн.</a></li>
    <li><a href=""><i class="icon icon-pluss"></i> от 3500 грн.</a></li>
  </ul>
  <div class="fs-price-show">
    <span data-fs-element="range-start" data-currency="false">0</span>
    <span data-fs-element="range-end" data-currency="false"><?php echo $args['price_max'] ?></span>
    </span>
  </div>
  <div data-fs-element="range-slider" id="range-slider"></div>
  <div class="clearfix"></div>
<div class="price--inputs">
  <span>от <input type="text" value="0" data-fs-element="range-start-input" data-url="<?php fs_filter_link([],null,array('price_start')) ?>">0</span>
  <span>до <input type="text" value="<?php echo esc_attr($args['price_max']) ?>" data-fs-element="range-end-input" data-url="<?php fs_filter_link([],null,array('price_end')) ?>"><?php echo $args['currency'] ?>.</span>
</div>
</div>
