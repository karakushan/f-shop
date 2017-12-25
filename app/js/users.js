// валидация формы редактирования личных данных
var userInfoEdit = jQuery('form[name="fs-profile-edit"]');
userInfoEdit.validate({
    rules: {
        "fs-password": {
            minlength: 6
        },
        "fs-repassword": {
            equalTo: "#fs-password"
        }
    },
    messages: {
        "fs-repassword": {
            equalTo: "пароль и повтор пароля не совпадают"
        },
        "fs-password": {
            minlength: "минимальная длина 6 символов"
        },
    },
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: userInfoEdit.serialize(),
            beforeSend: function () {
                userInfoEdit.find('.fs-form-info').fadeOut().removeClass('fs-error fs-success').html();
                userInfoEdit.find('[data-fs-element="submit"]').html('<img src="/wp-content/plugins/f-shop/assets/img/ajax-loader.gif">');
            }
        })
            .done(function (result) {
                userInfoEdit.find('[data-fs-element="submit"]').html('Сохранить');
                var data = JSON.parse(result);
                if (data.status == 0) {
                    userInfoEdit.find('.fs-form-info').addClass('fs-error').fadeIn().html(data.message);
                } else {
                    userInfoEdit.find('.fs-form-info').addClass('fs-success').fadeIn().html(data.message);
                }
                setTimeout(function () {
                    userInfoEdit.find('.fs-form-info').fadeOut('slow').removeClass('fs-error fs-success').html();
                }, 5000)
            });
    }
});

// регистрация пользователя
var userProfileCreate = jQuery('form[name="fs-profile-create"]');
userProfileCreate.validate({
    rules: {
        "fs-password": {
            minlength: 6
        },
        "fs-repassword": {
            equalTo: "#fs-password"
        }
    },
    messages: {
        "fs-repassword": {
            equalTo: "пароль и повтор пароля не совпадают"
        },
        "fs-password": {
            minlength: "минимальная длина 6 символов"
        },
    },
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: userProfileCreate.serialize(),
            beforeSend: function () {
                userProfileCreate.find('.form-info').html('').fadeOut();
                userProfileCreate.find('.fs-preloader').fadeIn();
            }
        })
            .done(function (result) {
                userProfileCreate.find('.fs-preloader').fadeOut();
                var data = JSON.parse(result);
                if (data.status == 1) {
                    userProfileCreate.find('.form-info').removeClass('bg-danger').addClass('bg-success').fadeIn().html(data.message);
                    // если операция прошла успешно - очищаем поля
                    userProfileCreate.find('input').each(function () {
                        if (jQuery(this).attr('type') != 'hidden') {
                            jQuery(this).val('');
                        }
                    });
                } else {
                    userProfileCreate.find('.form-info').removeClass('bg-success').addClass('bg-danger').fadeIn().html(data.message);
                }


            });
    }
});

// авторизация пользователя
var loginForm = jQuery('form[name="fs-login"]');
loginForm.validate({
    submitHandler: function (form) {
        jQuery.ajax({
            url: FastShopData.ajaxurl,
            type: 'POST',
            data: loginForm.serialize(),
            beforeSend: function () {
                loginForm.find('.form-info').fadeOut().removeClass('bg-danger').html('');
                loginForm.find('.fs-preloader').fadeIn();
            }
        })
            .done(function (result) {
                var data = JSON.parse(result);
                console.log(data);
                loginForm.find('.fs-preloader').fadeOut();
                if (data.status == 0) {
                    loginForm.find('.fs-form-info').addClass('bg-danger').fadeIn().html(data.error);
                } else {
                    if (data.redirect == false) {
                        location.reload();
                    } else {
                        location.href = data.redirect;
                    }
                }
            });
    }
});

