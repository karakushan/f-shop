<div class="wrap fs-atributes fs-settings">
	<h1><?php _e('Product Attributes','fast-shop') ?></h1>
	<?php 
	$fs_atributes=fs_get_attributes_group();
	$fs_atr=fs_get_attributes();
	?>
	<form action="#" method="post" id="fs_attr_form" class="fs_form">
		<input type="hidden" name="action" value="attr_edit">
		<div class="fs-add-attr">
			<h3>Добавление атрибута</h3>
			<p id="fs_attr_form_i"></p>
			<p>
				<label for="fs_attr_name">Название:</label>
				<input type="text" name="fs_attr_name" id="fs_attr_name" required>
			</p>
			<p>
				<label for="fs_attr_type">Тип атрибута:</label>
				<select name="fs_attr_type" id="fs_attr_type">
					<option value="text">текст</option>
					<option value="image">изображение</option>
				</select>
			</p>
			<div id="fs_attr_type_block">
				<label for="fs_attr_type">Выберите изображение:</label>
				<div id="fs_select_image"><button>Выбрать</button></div>
				<input type="hidden" name="fs_attr_image_id">
			</div>
			<p>
				<label for="fs_attr_group">Группа:</label>
				<select name="fs_attr_group" id="fs_attr_group" required> 
					<option value="">выберите группу</option>
					<?php if ( $fs_atributes): ?>
						<?php foreach ($fs_atributes as $key => $attr): ?>
							<option value="<?php echo $key ?>"><?php echo $attr ?></option>
						<?php endforeach ?>
						
					<?php endif ?>
					
				</select> <a href="#" id="fs_add_group_link">Добавить группу</a><br>
				


			</p>
			<div id="fs_add_attr_group">
				<p>
					<label>Название группы на русском</label>
					<input type="text" name="fs_attr_group_name" id="fs_attr_group_name"><span></span>
				</p>
				<p>
					<label>Название группы на английском</label>
					<input type="text" name="fs_attr_group_name_en" id="fs_attr_group_name_en"><span></span>

				</p>
				<p>
					<button type="button">Добавить группу</button>
				</p>

			</div>
			<p>
				<input type="submit" name="fs_send_attr" value="Добавить">
			</p>
		</div>
	</form>
	<h3 class="fs-section-title">Группы и свойства</h3>
	<?php if ( $fs_atributes): ?>
		<ul class="fs-group-list">
			<?php foreach ($fs_atributes as $key => $attr): ?>
				<li><?php echo $attr.' <span>('.$key.')</span>'; ?> <a href="#" data-fs-attr-group="<?php echo $key ?>" data-name="<?php echo $attr ?>" data-fs-action="delete-attr-group">удалить группу</a>
					<?php if (count($fs_atr[$key])): ?>
						<ul>
							<?php foreach ($fs_atr[$key] as $key2 => $att): ?>
								<li><?php echo $att['name']; ?> <a href="#" data-fs-action="delete-attr-single" data-fs-attr-group="<?php echo $key ?>" data-name="<?php echo $att['name'] ?>" data-fs-attr-id="<?php echo $key2; ?>">удалить атрибут</a></li>
							<?php endforeach ?>
						</ul>
					<?php endif ?>
				</li>
			<?php endforeach ?>	
		</ul>
	<?php endif ?>
</div>

