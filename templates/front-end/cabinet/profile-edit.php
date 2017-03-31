<div class="width colums">
    <div class="col">
            <span class="up">
                <input type="hidden" name="fs[profile_update]" value="<?php echo time() ?>">
                                        Дата обновления :
                                        <span><?php echo date('d.m.Y', $user->profile_update) ?></span>
                                    </span>
        <p>
            <input type="text" name="fs[<?php echo $field['display_name']['name'] ?>]"
                   value="<?php echo $user->display_name ?>"
                   placeholder="<?php echo $field['display_name']['label'] ?>*" required
                   title="<?php _e('required field', 'fast-shop') ?>"/>
        </p>
        <p>
            <input type="text" name="fs[<?php echo $field['user_email']['name'] ?>]"
                   value="<?php echo $user->user_email ?>"
                   placeholder="<?php echo $field['user_email']['label'] ?>*" required
                   title="<?php _e('required field', 'fast-shop') ?>"/>
        </p>
        <p>
            <input type="text" name="fs[<?php echo $field['birth_day']['name'] ?>]"
                   value="<?php echo $user->birth_day ?>"
                   placeholder="<?php echo $field['birth_day']['label'] ?>"/>
        </p>
        <ul class="gender">
            <li>Ваш пол</li>
            <li>
                <input type="radio"
                       name="fs[<?php echo $field['gender']['name'] ?>]" <?php checked($user->gender, 'm'); ?>
                       id="radio1" value="m">
                <label for="radio1">Мужской</label>
            </li>
            <li>
                <input type="radio"
                       name="fs[<?php echo $field['gender']['name'] ?>]" <?php checked($user->gender, 'w'); ?>
                       id="radio2" value="w">
                <label for="radio2">Женский</label>
            </li>
        </ul>
        <p><input type="text" name="fs[<?php echo $field['phone']['name'] ?>]" value="<?php echo $user->phone ?>"
                  placeholder="<?php echo $field['phone']['label'] ?>*" required
                  title="<?php _e('required field', 'fast-shop') ?>*"/></p>
    </div>
    <div class="col">
                                    <span class="up">
                                        Дата регистрации :
                                        <span><?php echo date('d.m.Y', strtotime($user->user_registered)); ?></span>
                                    </span>
        <select class="select" name="fs[<?php echo $field['country']['name'] ?>]">
            <option value=""><?php echo $field['country']['label'] ?></option>
            <option>Украина</option>
            <option>Россия</option>
            <option>Беларусь</option>
            <option>Другая</option>
        </select>
        <select class="select" name="fs[<?php echo $field['state']['name'] ?>]">
            <option><?php echo $field['state']['label'] ?></option>
            <?php $states = array('Вінницька область', 'Волинська область', 'Дніпропетровська область', 'Донецька область', 'Житомирська область', 'Закарпатська область', 'Запорізька область', 'Івано-Франківська область', 'Київська область', 'Кіровоградська область', 'Луганська область', 'Львівська область', 'Миколаївська область', 'Одеська область', 'Полтавська область', 'Рівненська область', 'Сумська область', 'Тернопільська область', 'Харківська область', 'Херсонська область', 'Хмельницька область', 'Черкаська область', 'Чернівецька область', 'Чернігівська область'); ?>
            <?php foreach ($states as $state): ?>
                <option value="<?php echo $state ?>" <?php selected($user->state, $state) ?>><?php echo $state ?></option>
            <?php endforeach; ?>
        </select>
        <p><input type="text" name="fs[<?php echo $field['city']['name'] ?>]" value="<?php echo $user->city ?>"
                  placeholder="<?php echo $field['city']['label'] ?>" /></p>
        <p><input type="text" name="fs[<?php echo $field['adress']['name'] ?>]" value="<?php echo $user->adress ?>"
               placeholder="<?php echo $field['adress']['label'] ?>"/></p>
        <div class="fileBlock width">
            <span>Загрузить Ваше фото</span>
            <input type="file" id="file" name="">
            <label for="file">Выберите файл</label>
        </div>
    </div>
</div>
<div class="mapCabinet width">
    <div class="leftMap">
                                    <span class="titleMap">
                                        Ваше местоположение на карте:
                                    </span>
        <p>
            Вы может уточнить ваше местоположение на карте
            переместив метку изменив отображаемый участоккарты или масштаб
        </p>
    </div>
    <div class="rightMap">

    </div>
</div>
<div class="width">
    <div class="btnBlock">
        <button type="submit" class="fs-submit">Сохранить</button>
    </div>
</div>