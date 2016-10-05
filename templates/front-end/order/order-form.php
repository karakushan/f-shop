<?php 
/*
Список основных полей для использования в письмах
fs_name
fs_email
fs_city
fs_delivery
fs_pay
fs_phone
fs_adress
fs_message
*/
?>
	
	<h2 class="text-center"><span>оформить заказ</span></h2>
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<input type="text" name="fs_name" class="form-control"  placeholder="Ваше имя" title="Введите ваше имя" required>
			</div>
			<div class="form-group">

				<input type="email" class="form-control"  placeholder="Ваш e-mail" name="fs_email" title="Введите валидный email" required>
			</div>
			<div class="form-group">

				<input type="tel" class="form-control"  placeholder="Ваш телефон" name="fs_phone" title="Нужен номер телефона для связи" required>
			</div>
			<div class="form-group">

			<input type="text" name="fs_city" class="form-control"  placeholder="Город" title="Заполните поле" required>
			</div>

			<div class="form-group">

				<input type="text" name="fs_adress" class="form-control"  placeholder="Улица, дом" title="Адрес проживания">
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">

				<select class="form-control" name="fs_delivery" title="Выберите способ доставки" required>
					<option value="">Способ доставки</option>
					<option value="2">Способ доставки 1</option>
					<option value="3">Способ доставки 2</option>
				</select>
			</div>	
			<div class="form-group">

				<select class="form-control" name="fs_pay" title="Выберите способ оплаты" required>
					<option value="">Вид оплаты</option>
					<option value="1">Вид оплаты 1</option>
					<option value="2">Вид оплаты 2</option>
				</select>
			</div>
			<div class="form-group" >
				<textarea name="fs_message"   class="form-control" placeholder="Ваше сообщение"></textarea></div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12 text-center">
				<input type="submit" value="отправить">
			</div>
		</div>
	
