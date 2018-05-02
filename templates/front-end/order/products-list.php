<?php $cart = fs_get_cart() ?>
<?php if ( count( $cart ) ): ?>
  <div class="width">
    <div class="width widthTable">
      <table>
        <thead>
        <tr>
          <td>
            Фото
          </td>
          <td>
            Товар
          </td>
          <td>
            артикул
          </td>
          <td>
            цена
          </td>
          <td>
            количество
          </td>
          <td>
            стоимость
          </td>
          <td></td>
        </tr>
        </thead>
        <tbody>
		<?php foreach ( $cart as $c ): ?>
          <tr>
            <td>
				<?php echo $c['thumb'] ?>
            </td>
            <td>
              <div class="info">
                <span class="name"><?php echo $c['name'] ?></span>
                <span class="size">Размер: S</span>
                <span class="color">
                                    Цвет:
                                    <span class="colorB" style="background-color: #fd6ec3"></span>
                                    <span class="nameColor">розовый</span>
                                </span>
              </div>
            </td>
            <td>
				<?php echo $c['sku'] ?>
            </td>
            <td>
				<?php echo $c['all_price'] ?>
            </td>
            <td>
				<?php do_action( 'fs_cart_quantity', $c['id'], $c['count'], array(
					'wrapper_class' => 'count',
					'position'      => '%input% %pluss%  %minus%',
					'pluss'         => array( 'class' => 'plus', 'content' => '' ),
					'minus'         => array( 'class' => 'minus', 'content' => '' ),
					'input'         => array( 'class' => 'fs-cart-quantity' )
				) ) ?>
            </td>
            <td>
				<?php echo $c['all_price'] ?>
            </td>
            <td>
				<?php fs_delete_position( $c['id'], array( 'class' => 'remove' ) ) ?>
            </td>
          </tr>
		<?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="width btnBlockBasket">
      <a href="">
        продолжить покупки
      </a>
      <a href="" class="blue">
        очистить корзину
      </a>
    </div>
    <div class="width informBasket">
      <p>
        Стоимость товаров: <?php fs_total_amount() ?>
      </p>
      <p>
        Доставка: 0 грн
      </p>
      <p>
        <b>
          Итого: <?php fs_total_amount() ?>
        </b>
      </p>
    </div>
  </div>
<?php endif; ?>