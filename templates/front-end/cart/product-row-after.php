<tr>
	<td colspan="6" class="all-summ">
		<a href="/product/" class="read-mores">Продолжить покупки</a>
	</td>
	<td class="l-price"><span class="itogo">Итого: </span><?php fs_all_price(true) ?></td>
</tr>
</table>
</div>
<div class="row">
	<h3>оформить заказ</h3>
	<form  action="" name="order-send" method="POST" role="form" class="order-send" novalidate="novalidate">

		<input type="hidden" name="action" value="order_send">
		<?php wp_nonce_field(); ?>
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<input type="text" class="form-control" id="name" name="name" placeholder="Имя" >
				</div>						
				<div class="form-group">
					<input type="tel" class="form-control" id="tel" placeholder="Ваш номер телефона" name="telefon">
				</div>
				<div class="form-group">
					<input type="email" class="form-control" id="email" placeholder="Ваш Email" name="email">
				</div>
				<div class="form-group">
					<textarea name="comments" id=""  class="form-control" placeholder="Комментарий"></textarea>
				</div>	
				
			</div>
			<div class="col-md-6">
				<div class="form-group" style="    margin-bottom: 92px;">
					<p><b>Выберите способ доставки</b></p>
					<ul><?php global $fs_delivery;
						$fs_delivery->list_delivery('radio') ?></ul>
					
				</div>			
				<div class="form-group" style="    margin-bottom: 110px;">
					<p><b>Выберите способ оплаты</b></p>
					<ul>
						<li>
						<input type="radio" id="pay-1" name="pay_method">
							<label for="pay-1">Наличными курьеру</label>

							<div class="check"></div>
						</li>		
						<li>
						<input type="radio" id="pay-2" name="pay_method">
							<label for="pay-2">Безналичный расчет</label>

							<div class="check"></div>
						</li>	
						<li>
						<input type="radio" id="pay-3" name="pay_method">
							<label for="pay-3">Наложенным платежом</label>

							<div class="check"></div>
						</li>

					</ul>
					
				</div>							

			</div>
		<div class="col-sm-12">
			<div class="row">
					<div class=" col-sm-6">
				<div class="form-group">
					<?php fs_order_send('подтвердить заказ','class="order-b"')  ?>
				</div>
							</div>
			</div>
		</div>
			
		</div>
	</form>
</div>
</div>