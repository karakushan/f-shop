<div class="wrap">
	<h1><?php _e('Orders','fast-shop') ?></h1>
	<?php if ($orders->get_orders()): ?>
		<div class="fs-order-top"><button type="button" class="btn-fs-1" data-fs-action="admin_truncate_orders" data-fs-confirm="Вы точно хотите удалить все заказы? Восстановление заказов станет невозможным.">очистить базу заказов</button></div>
		<div id="fs-table" class="fs-content-wrapper">



			<table>
				<thead>
					<tr>
						<th scope="col">#ID</th><th scope="col">Дата заказа</th><th scope="col">Телефон</th><th scope="col">Email</th><th scope="col">Способ доставки</th><th scope="col">Статус/Изменить</th><th scope="col">Сумма</th><th scope="col">Подробности</th><!-- <th>Отмена</th> -->
					</tr>
				</thead>
				<tbody>
					<?php foreach ($orders->get_orders() as $order): ?>
						<?php $status=$orders->order_status($order->status); ?>
						<tr>
							<th scope="row"><a href="<?php echo add_query_arg(array('action'=>'info','id'=>$order->id)) ?>"><?php echo $order->id ?></a></th>
							<td><?php echo date('d.m.Y H:i',strtotime($order->date)) ?></td>
							<td><?php echo $order->telephone ?></td>
							<td><?php echo $order->email ?></td>
							<td><?php echo $order->delivery ?></td>
							<td><select name="" id="" class="status_<?php echo $order->status ?>"  onchange="if(confirm('Вы дейтвительно хотите поменять статус заказа <?php echo $order->id ?>')){ document.location=this.value}">
								<?php foreach ($orders->order_status as $key => $value): ?>
									<option value="<?php echo add_query_arg(array('action'=>'edit','id'=>$order->id,'status'=>$key)) ?>" <?php selected( $key, $order->status  ); ?>><?php echo $value ?></option>
								<?php endforeach ?>

							</select></td>
							<td><?php echo $order->summa.' '.fs_currency() ?></td>
							<td><a href="<?php echo add_query_arg(array('action'=>'info','id'=>$order->id)) ?>">Подробности</a></td>

						</tr>
					<?php endforeach ?>
				</tbody>
				<tfoot><tr><th scope="row"><?php _e( 'Total', 'fast-shop' ); ?></th><td colspan="7"><?php 
echo sprintf( _n( '1 order', '%s orders', $orders_count, 'fast-shop'), $orders_count ); ?></td></tr></tfoot>
			</table>

		</div>
		<?php $orders->order_pagination('order-paggination') ?>
	<?php else: ?>
		<p><?php _e('At the current time you have no order.','fast-shop' ); ?></p>
	<?php endif ?>
</div>