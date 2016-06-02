<style>
	table, table td, table th {
		border: 2px dashed rgb(241, 241, 241);
		border-collapse: collapse;
		padding: 6px 16px;
		/* border-radius: 10px; */
		background: #ccc;
	}
	table th{
		background: #00b9eb;
		font-weight: bold;
		color: #000;
	}
</style>
<div class="wrap">
	<h2>Настройка доставки</h2>
	<form method="post" action=""> 

		<h3>Добавление способа доставки</h3>
		<table>
			<tr>
				<th>Идентификатор</th>
				<th>Название </th>
				<th>Стоимость</th>
				<th></th>
			</tr>			
			<tr>
				<td><input type="text" name="delivery_id" id="" required="required"></td>
				<td><input type="text" name="delivery_name" id="" required="required"></td>
				<td><input type="text" name="delivery_price" id="" size="6"  required="required"></td>
				<td><input type="submit" name="delivery_save" value="добавить"></td>
			</tr>
		</table>
		<h3>Способы доставки</h3>
		<table>
			<tr>
				<th>#ID</th>
				<th>Название </th>
				<th>Стоимость</td>
					
					<th></th>
				</tr>	
				<?php if ( $delivery->delivery): ?>

					<?php foreach ( $delivery->delivery as $delivery): ?>
						<tr>
							<td><?php echo $delivery['id'] ?></td>
							<td><?php echo $delivery['name'] ?></td>
							<td><?php echo $delivery['price'].' '.get_option( 'currency_icon' ); ?></td>
							
							<td><a href="<?php echo add_query_arg(array('action'=>'delete','id'=>$delivery['id'])) ?>" onclick="if (confirm('Вы действительно намерены удалить этот способ доставки?')) { document.location.this.href ;} return false;">удалить</a></td>
						</tr>
					<?php endforeach ?>

				<?php endif ?>	
			</table>	

		</form>
	</div>