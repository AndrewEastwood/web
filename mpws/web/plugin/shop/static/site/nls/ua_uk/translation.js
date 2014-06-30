define("plugin/shop/site/nls/ua_uk/translation", [
    // here we will call default lang pkgs to override them
    'default/js/lib/underscore',
    'website/nls/ua_uk/plugin_shop'
], function (_, CustomerPluginShop) {
    return _.extend({}, {
        order_status_NEW: 'Прийняте',
        order_status_ACTIVE: 'В процесі виконання',
        order_status_LOGISTIC_DELIVERING: 'Відправлено',
        order_status_LOGISTIC_DELIVERED: 'Вантаж прибув',
        order_status_SHOP_CLOSED: 'Виконано',
        shopping_cart_link_removeAll: "Видалити всі товари з списку",
        shopping_cart_form_title: "Оформлення замовлення",
        shopping_cart_field_firstName: "Імя",
        shopping_cart_field_lastName: "Прізвище",
        shopping_cart_field_email: "Ел.пошта",
        shopping_cart_field_phone: "Контактний телефон",
        shopping_cart_field_profile_address_option_none: "Не вибрано",
        shopping_cart_field_profile_address: "Мої адреси",
        shopping_cart_field_address: "Адреса доставки",
        shopping_cart_field_pobox: "Поштовий індекс",
        shopping_cart_field_country: "Країна",
        shopping_cart_field_city: "Місто",
        shopping_cart_field_logistic: "Перевізник",
        shopping_cart_field_warehouse: "Номер складу",
        shopping_cart_field_comment: "Ваші побажання",
        shopping_cart_error_EmptyShippingAddress: "Потрібно вказати адресу доставки",
        shopping_cart_error_WrongProfileAddressID: "Вибрана неіснуюча адреса з Вашого акаунту",
        shopping_cart_error_UnknownError: "Помилка збереження замовлення",
        list_wish_link_clear: "Видалити всі товари з списку",
        list_wish_empty: "Ваш список бажань порожній",
        list_wish_alert_add: "Товар доданий в список бажань",
        list_wish_alert_remove: "Товар видалений з списоку бажань",
        list_wish_alert_clear: "Список бажань очищено",
        list_compare_link_clear: "Видалити всі товари з списку",
        list_compare_empty: "Немає товарів до порівняння",
        list_compare_alert_add: "Товар доданий в список порівнянь",
        list_compare_alert_remove: "Товар видалений з списоку порівнянь",
        list_compare_alert_clear: "Список порівнянь очищено",
        list_cart_link_clear: "Видалити всі товари з кошику",
        list_cart_empty: "Ваш кошик порожній",
        list_cart_alert_add: "Товар доданий в кошик",
        list_cart_alert_updated: "Товар в кошику оновлено",
        list_cart_alert_remove: "Товар видалений з кошику",
        list_cart_alert_clear: "Ваш кошик очищено",
        order_trackingPage_label_hash: "Код замовлення",
        order_trackingPage_button_get: "Отримати статус",
        order_trackingPage_wrongHash: "Невірно вказаний трекнг-код замовлення",
    }, CustomerPluginShop);
});