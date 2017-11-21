var fs_message;
var event;
// переводы сообщений
var FastShopLang = {
    uk: {
        confirm_text: "Вы точно хочете видалити позицію «%s» із списку бажань?",
        wishText: "Товар успішно доданий в список бажань!",
        delete_text: "Вы точно хочете видалити товар «%s» із кошика?",
        delete_all_text: "Ви точно хочете видалити всі товари із кошика?",
        count_error: "к-сть товарів не може бути меньше 1"


    },
    ru_RU: {
        confirm_text: "Вы точно хотите удалить позицию «%s» из списка желаний?",
        wishText: "Товар успешно добавлен в список желаний!",
        delete_text: "Вы точно хотите удалить продукт «%s» из корзины?",
        delete_all_text: "Вы точно хотите удалить все товары из корзины?",
        count_error: "к-во товаров не может быть меньше единицы"

    }

};
//переключатель сообщений в зависимости от локали
switch (FastShopData.fs_lang) {
    case "ru_RU":
        fs_message = FastShopLang.ru_RU;
        break;
    case "uk":
        fs_message = FastShopLang.uk;
        break;
    default:
        fs_message = FastShopLang.ru_RU;
}