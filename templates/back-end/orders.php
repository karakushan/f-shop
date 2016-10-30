<div class="wrap">
	<h2><?php _e('Orders','fast-shop') ?></h2>
	<table class="wp-list-table widefat fixed striped posts">
	<thead>
		<tr>
			<th>#ID</th><th>Дата заказа</th><th>Телефон</th><th>Email</th><th>Способ доставки</th><th>Статус/Изменить</th><th>Сумма</th><th>Подробности</th><!-- <th>Отмена</th> -->
		</tr>
		</thead>
		<?php foreach ($orders->get_orders() as $order): ?>
			<?php $status=$orders->order_status($order->status); ?>
			<tr>
				<td><?php echo $order->id ?></td>
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

	</table>
	<?php $orders->order_pagination('order-paggination') ?>
</div>