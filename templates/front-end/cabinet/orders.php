<?php if ($orders): ?>
    <?php foreach ($orders as $od) { ?>
        <?php $order = fs_get_order($od->id); ?>

        <div class="itemTable width">
            <div class="headItem width">
                <div class="leftHead">
                    <button type="button"></button>
                    <p>Заказ <b><?php echo $od->id ?></b> от <?php echo date('d.m.Y', $order->date); ?></p>
                </div>
                <p>Заказ <?php echo $order->status ?></p>
                <duv class="rightHead">
                    <p>Итоговая сумма заказа <?php echo $od->summa . ' ' . fs_currency(); ?> </p>
                </duv>
            </div>
            <div class="width contentItem">
                <div class="responsiveTable">
                    <table>
                        <thead>
                        <tr>
                            <td>Фото товара</td>
                            <td>Модель</td>
                            <td>Дата заказа</td>
                            <td>Статус</td>
                            <td>Цена</td>
                            <td>Количество</td>
                            <td>Стоимость</td>
                        </tr>

                        </thead>
                        <tbody>
                        <?php if ($order->products): ?>
                            <?php foreach ($order->products as $id => $product) : ?>
                                <tr>
                                    <td>
                                        <?php echo get_the_post_thumbnail($id); ?>

                                    </td>
                                    <td>
                                            <span class="titleProduct">
                                                <?php echo get_the_title($id); ?>
                                            </span>
                                        <ul>
                                            <li>
                                                <span>№ тов.</span>
                                                <?php fs_product_code($id); ?>
                                            </li>
                                            <li>
                                                <span>цвет:</span>
                                                жемчужно-розовый
                                            </li>
                                            <li>
                                                <span>размер:</span>
                                                44/46
                                            </li>
                                            <li>
                                                <span>цена:.</span>
                                                <?php fs_the_price($id); ?>
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <?php echo date('d.m.Y', $order->date); ?>
                                    </td>
                                    <td>
                                        <?php echo $order->status ?>
                                    </td>
                                    <td>
                                        <?php fs_the_price($id); ?>
                                    </td>
                                    <td>
                                        <div class="count">

                                            <input type="text" value="<?php echo  (int)$product['count'] ?>" readonly>

                                        </div>
                                    </td>
                                    <td>
                                        <?php echo fs_row_price($id,$product['count']) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php else: ?>
    <p>У вас ещё нет ни одного заказа.</p>
<?php endif; ?>