<div class="wrap fs-atributes">
	<h1><?php _e('Product Attributes','fast-shop') ?></h1>
	<?php $fs_atributes=get_option('fs-attr-groups')!=false?get_option('fs-attr-groups'):array(); ?>
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
</div>
<style type="text/css">
	
</style>
<script type="text/javascript">
	jQuery(function($) {
		$('#fs_add_attr_group button').on('click', function(event) {
			event.preventDefault();
			var inputStatus=true;

			$('#fs_add_attr_group input').each(function(index, el) {
				if ($(this).val().length<1) {
					inputStatus=false;
					$(this).addClass('fs_error_input');
					$(this).next().text('заполните поле');

				}else{
					$(this).removeClass('fs_error_input');
					$(this).next().text('');
				}
			});
			
			if (inputStatus!=false) {
				$.ajax({
					url: ajaxurl,
					data: {
						action: "attr_group_edit",
						name: $('#fs_attr_group_name').val(),
						slug: $('#fs_attr_group_name_en').val(),
					},
				})
				.done(function(data) {
					$('#fs_attr_group').html(data);
					$('#fs_add_attr_group').fadeOut('800');
					$('#fs_add_attr_group input').each(function(index, el) {
						$(this).val('');
					});
				})
				.fail(function() {
					console.log("error");
				})
				.always(function() {
					console.log("complete");
				});
			}
			
		});
		$('#fs_add_group_link').on('click', function(event) {
			event.preventDefault();
			$('#fs_add_attr_group').fadeIn(800);
		});		
	});
</script>
