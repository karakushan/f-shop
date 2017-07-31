<input type="hidden" name="fs[profile_update]" value="<?php echo time() ?>">
<p><b>Дата обновления :
    <span><?php echo date( 'd.m.Y', $user->profile_update ) ?></span></b></p>

<div class="fs-row">
  <div class="fs-col-6"><label for="display-name">Отображаемое имя:</label></div>
  <div class="fs-col-6">
    <input type="text" name="fs[<?php echo $field['display_name']['name'] ?>]"
           value="<?php echo $user->display_name ?>" id="display-name"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6"><label for="user_email">E-mail <span>*</span>:</label></div>
  <div class="fs-col-6">
    <input type="email" name="fs[<?php echo $field['user_email']['name'] ?>]"
           value="<?php echo $user->user_email ?>" id="user_email"
           placeholder="<?php echo $field['user_email']['label'] ?>*" required
           title="<?php _e( 'required field', 'fast-shop' ) ?>"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6"><label for="user_phone">Телефон:</label></div>
  <div class="fs-col-6">
    <input type="tel" name="fs[<?php echo $field['phone']['name'] ?>]" value="<?php echo $user->phone ?>"
           id="user_phone"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6">
    <label for="birth_day">Дата рождения:</label>
  </div>
  <div class="fs-col-6">
    <input type="date" name="fs[<?php echo $field['birth_day']['name'] ?>]"
           value="<?php echo date( 'Y-m-d', $user->birth_day ) ?>" id="birth_day"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6">
    <label for="user_email">Пол:</label>
  </div>
  <div class="fs-col-6">
    <select class="selectStyle" name="fs[<?php echo $field['gender']['name'] ?>]">
      <option <?php selected( $user->gender, 'm' ); ?> value="m">М</option>
      <option <?php selected( $user->gender, 'w' ); ?> value="w">Ж</option>
    </select>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6">
    <label for="user_city">Город:</label>
  </div>
  <div class="fs-col-6">
    <input type="text" name="fs[<?php echo $field['city']['name'] ?>]" value="<?php echo $user->city ?>"
           id="user_city"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6">
    <label for="user_adress">Адрес:</label>
  </div>
  <div class="fs-col-6">
    <input type="text" name="fs[<?php echo $field['adress']['name'] ?>]" value="<?php echo $user->adress ?>"
           id="user_adress"/>
  </div>
</div>
<div class="fs-row">
  <div class="fs-col-6">
    <label for="user_adress">Фото:</label>
  </div>
  <div class="fs-col-6">
    <div class="fileBlock">
      <input type="file" id="file" name="">
      <label for="file">Выберите файл</label>
    </div>
  </div>
</div>
<div class="fs-row">
  <button type="submit" class="greenBtn" data-fs-element="submit">Сохранить</button>
</div>