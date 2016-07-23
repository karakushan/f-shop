<style type="text/css">	
	.attr-desc {
		margin-bottom: 25px;
		max-width: 530px;
		border: 1px solid #e0e0e0;
		padding: 10px;
		background: #fff;
		line-height: 1.4em;
	}
	.fs-atributes label{
		font-weight: bold;
	}
	.fs-atributes form{
		margin-bottom: 30px;
	}
</style>
<div class="wrap fs-atributes">
	<h1>Атрибуты товаров</h1>
	<div class="attr-desc">В этом разделе меню вы можете добавлять атрибуты товара. Это необходимо если ваш товар может иметь различные цвета, типы и т.д.. В клиентской части покупатель сможет увидеть и выбрать товар с нужными ему свойствами.</div>

	<?php $fs_atributes=get_option('fs-attr-group'); ?>
	<?php // print_r($fs_atributes) ?>
	<form action="<?php echo wp_nonce_url(add_query_arg(array('fs_action'=>'fs-add-attr-group')),'fs_action') ?>" method="post">
		<div>
			<label for="name">Добавить группу атрибутов <small>(например "цвета")</small>:</label>
			<br>
			<input type="text" name="group-name" id="name" value="" tabindex="1" placeholder="введите название группы здесь" size="40"  required />
			<input type="text" name="group-name-en" id="name" value="" tabindex="1" placeholder=" название группы на англ." size="40"  required />
		</div>

		<div>
			<input type="submit" value="добавить группу"/>
		</div>
	</form>
	<form action="<?php echo wp_nonce_url(add_query_arg(array('fs_action'=>'fs-add-attr')),'fs_action') ?>" method="post">

		<div>
			<label for="name">Добавить атрибут в группу <small>(красный)</small>:</label>
			<br>
			
				<select name="group-name" id="" required>
					<option value="">Выберите группу</option>
					<?php if ($fs_atributes): ?>
						<?php foreach ($fs_atributes as $fs_atr): ?>
							<option value="<?php echo $fs_atr['slug'] ?>"><?php echo $fs_atr['title'] ?></option>
						<?php endforeach ?>
					<?php endif ?>
					
				</select>
			
			<input type="text" name="attr-name" id="name" value="" tabindex="1" placeholder="название атрибута" size="40"  required />
			<input type="text" name="attr-slug" id="name" value="" tabindex="1" placeholder=" название атрибута англ." size="40"  required />
		
			<input type="submit" value="добавить атрибут" />
		</div>
	</form>
	<?php if ($fs_atributes): ?>
		<ul>

			<?php foreach ($fs_atributes as $fs_atr): ?>
				<li><h3><?php echo $fs_atr['title'] ?> <a href="<?php echo wp_nonce_url(add_query_arg(array('fs_action'=>'fs-delete-group','group-name'=>$fs_atr['slug'])),'fs_action') ?>">удалить</a></h3>

<?php if (count($fs_atr['attributes'])): ?>
	<ul>
		<?php foreach ($fs_atr['attributes'] as $key => $fs_attr): ?>
			<li><?php echo $fs_attr ?> <a href="<?php echo wp_nonce_url(add_query_arg(array('fs_action'=>'fs-delete-attr','attr-name'=>$key)),'fs_action') ?>">удалить</a></li>
		<?php endforeach ?>
	</ul>
<?php endif ?>
				<ul>
					
				</ul>
				</li>
				
			<?php endforeach ?>

		</ul>
	<?php endif ?>
</div>
