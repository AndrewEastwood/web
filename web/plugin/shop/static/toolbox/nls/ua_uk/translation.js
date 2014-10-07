define("plugin/shop/toolbox/nls/ua_uk/translation", [
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
        order_status_CUSTOMER_CANCELED: "Відмова покупця",
        order_status_SHOP_REFUNDED: "Відшкодування",
        // orderEntry_Popup_field_firstName: "Імя",
        // orderEntry_Popup_field_lastName: "Прізвище",
        // orderEntry_Popup_field_email: "Ел.пошта",
        // orderEntry_Popup_field_phone: "Контактний телефон",
        // orderEntry_Popup_field_profile_address_option_none: "Не вибрано",
        // orderEntry_Popup_field_profile_address: "Мої адреси",
        // orderEntry_Popup_field_address: "Адреса доставки",
        // orderEntry_Popup_field_pobox: "Поштовий індекс",
        // orderEntry_Popup_field_country: "Країна",
        // orderEntry_Popup_field_city: "Місто",
        // orderEntry_Popup_field_logistic: "Перевізник",
        // orderEntry_Popup_field_warehouse: "Номер складу",
        // orderEntry_Popup_field_comment: "Коментар",
        // menu
        pluginMenuTitle: 'Інтернет Магазин',
        pluginMenu_Dashboard: 'Статистика',
        pluginMenu_Products: 'Товари',
        pluginMenu_Orders: 'Замовлення',
        pluginMenu_Offers: 'Акції та Розпродаж',
        pluginMenu_Reports: 'Звіти',
        pluginMenu_Settings: 'Налаштування',
        pluginMenu_Promo: 'Промо-коди',
        //
        productManager_Button_Create_Product: "Товар",
        productManager_Button_Create_Category: "Категорія",
        productManager_Button_Create_Origin: "Виробник",
        // Order list
        pluginMenu_Orders_Grid_Column_ID: "#",
        pluginMenu_Orders_Grid_Column_Shipping: "Доставка",
        pluginMenu_Orders_Grid_Column_Status: "Статус",
        pluginMenu_Orders_Grid_Column_Warehouse: "Склад",
        pluginMenu_Orders_Grid_Column_DateCreated: "Створений",
        pluginMenu_Orders_Grid_Column_DateUpdated: "Оновлений",
        pluginMenu_Orders_Grid_Column_Actions: "",
        pluginMenu_Orders_Grid_Column_AccountFullName: "Покупець",
        pluginMenu_Orders_Grid_Column_AccountPhone: "Телефон",
        pluginMenu_Orders_Grid_Column_InfoTotal: "Сума (грн.)",
        pluginMenu_Orders_Grid_Column_HasPromo: "Промо",
        pluginMenu_Orders_Grid_Column_Discount: "Знижка",
        pluginMenu_Orders_Grid_link_ShowDeleted: "Показати виконані",
        pluginMenu_Orders_Grid_noData_ByStatus: "Немає замовлень по цьому статусу",
        // Order popup
        popup_order_title: "Замовлення #",
        popup_order_section_boughts: "Список придбаних товарів:",
        popup_order_control_status: "Виберіть статус замовлення",
        popup_order_button_Close: "Закрити",
        // Category tree
        pluginMenu_Categories_Tree_Title: "Категорії",
        // Category popup
        popup_category_title_edit: "Редагування категорії",
        popup_category_title_new: "Створення категорії",
        popup_category_section_details_filed_name: "Назва:",
        popup_category_section_details_filed_description: "Опис:",
        popup_category_section_details_filed_parentCategory: "Вкласти в категорію:",
        popup_category_section_props_field_name: "Назва:",
        popup_category_section_props_field_defaultValue: "Початкове значення:",
        popup_category_button_Close: "Закрити",
        popup_category_button_Save: "Зберегти",
        // Origin list
        pluginMenu_Origins_Grid_Title: "Виробники",
        pluginMenu_Origins_Grid_Column_Name: "Назва",
        pluginMenu_Origins_Grid_Column_HomePage: "Сторінка",
        pluginMenu_Origins_Grid_Column_Status: "Статус",
        pluginMenu_Origins_Grid_Column_DateUpdated: "Оновлений",
        pluginMenu_Origins_Grid_Column_DateCreated: "Створений",
        pluginMenu_Origins_Grid_Column_Actions: "",
        pluginMenu_Origins_Grid_noData: "Список виробників порожній",
        // Origin popup
        popup_origin_title_new: "Створення виробника",
        popup_origin_title_edit: "Редагування виробника",
        popup_origin_section_details_filed_Name: "Назва:",
        popup_origin_section_details_filed_Description: "Опис:",
        popup_origin_section_details_filed_HomePage: "Сайт виробника:",
        popup_origin_button_Close: "Закрити",
        popup_origin_button_Save: "Зберегти",
        // popup_origin_button_Update: "Обновити",
        popup_product_title_new: "Створення товару",
        popup_product_title_edit: "Редагування товару",
        popup_product_section_details_filed_CategoryID: "Категорія:",
        popup_product_section_details_filed_OriginID: "Виробник:",
        popup_product_section_details_filed_Name: "Назва:",
        popup_product_section_details_filed_Description: "Опис:",
        popup_product_section_details_filed_Model: "Модель:",
        popup_product_section_details_filed_SKU: "Код товару:",
        popup_product_section_details_filed_Price: "Ціна:",
        popup_product_section_details_filed_IsPromo: "Дозволити знижку",
        popup_product_section_details_filed_Status: "Статус:",
        popup_product_section_details_filed_Tags: "Теги",
        popup_product_section_details_filed_ISBN: "ISBN",
        popup_product_section_details_filed_Features: "Властивості",
        popup_product_error_atField_CategoryID: 'Не вибрано категорію',
        popup_product_error_CategoryIDIsNotInt: 'Код категорії не правильний',
        popup_product_error_atField_Name: 'Помилка в полі Назва',
        popup_product_error_NameIsEmpty: 'Назва товару порожня',
        popup_product_error_NameLengthIsLowerThan_1: 'Назва товару повинна містити, як мінімум 1 символ',
        popup_product_error_atField_OriginID: 'Не вибрано виробника',
        popup_product_error_OriginIDIsNotInt: 'Код виробника не правильний',
        popup_product_error_atField_Price: 'Помилка в полі Ціна',
        popup_product_error_PriceIsNotNumeric: 'Ціна не є число',
        popup_product_error_PriceIsEmpty: 'Порожня ціна',
        popup_product_error_atField_Features: 'Помилка в полі Властивості',
        popup_product_error_FeaturesIsEmpty: 'Не вибрано жодної властивості товару',
        popup_product_button_Close: "Закрити",
        popup_product_button_Save: "Зберегти",
        // Product list
        pluginMenu_Products_Grid_Title: "Товари",
        pluginMenu_Products_Grid_Column_Name: "Назва",
        pluginMenu_Products_Grid_Column_Model: "Модель",
        pluginMenu_Products_Grid_Column_OriginName: "Виробник",
        pluginMenu_Products_Grid_Column_SKU: "SKU",
        pluginMenu_Products_Grid_Column_Price: "Ціна",
        pluginMenu_Products_Grid_Column_Status: "Статус",
        pluginMenu_Products_Grid_Column_DateUpdated: "Оновлений",
        pluginMenu_Products_Grid_Column_DateCreated: "Створений",
        pluginMenu_Products_Grid_Column_Actions: "",
        pluginMenu_Products_Grid_Column_SellMode: "Продаж",
        pluginMenu_Products_Grid_noData_ByStatus: "Немає товарів в цьому списку",
        product_type_Active: "Активні",
        product_type_Inactive: "Неактивні",
        product_type_Uncompleted: "Незаповнені",
        product_type_Sales: "Акції",
        product_type_Defects: "Браковані",
        product_type_Popular: "Популярні",
        product_type_NotPopular: "Непопулярні",
        product_type_Archived: "Архівні",
        origin_status_ACTIVE: "Активний",
        origin_status_REMOVED: "Неактивний",
        product_status_ACTIVE: 'Активний',
        product_status_ARCHIVED: 'Архівний',
        product_status_DISCOUNT: 'Знижка',
        product_status_DEFECT: 'З дефектом',
        product_status_WAITING: 'Очікування',
        product_status_PREORDER: 'Під замовлення',
        orders_manager_listTitle: "Менеджер замовлень",
        categoryTree_Title: 'Дерево категорії',
        listPromos_Column_Actions: '',
        listPromos_Column_Code: 'Код',
        listPromos_Column_DateStart: 'Початок акції',
        listPromos_Column_DateExpire: 'Завершення акції',
        listPromos_Column_Discount: 'Знижка (%)',
        manager_promoCodes_listTitle: 'Промо-коди',
        setting_property_AddedNewCategory: 'додався новий товар(и)',
        setting_property_AddedNewDiscountedProduct: 'ціна товару(ів) знизилася',
        setting_property_AddedNewOrigin: 'почалася промо акція',
        setting_property_NewProductAdded: 'зявився новий виробник',
        setting_property_ProductPriceGoesDown: 'зявилася нова категорія',
        setting_property_PromoIsStarted: 'зявився вживаний/бракований товар',
        setting_property_AllowAlerts: 'Дозволити сповіщення',
        setting_property_ShowAddress: 'адреса доставки',
        setting_property_ShowCity: 'місто',
        setting_property_ShowComment: 'побажання',
        setting_property_ShowCountry: 'країна',
        setting_property_ShowDeliveryAganet: 'перевізник',
        setting_property_ShowEMail: 'ел.пошта',
        setting_property_ShowName: 'імя',
        setting_property_ShowPOBox: 'поштовий індекс',
        setting_property_ShowPhone: 'контактний телефон',
        popup_settingAddress_title_new: 'Створення адреси',
        popup_settingAddress_title_edit: 'Редагування адреси',
        popup_promo_title_new: 'Створення промо-акції',
        popup_promo_title_edit: 'Редагування промо-акції',
        popup_promo_filed_Code: 'Промо-код',
        popup_promo_filed_DateStart: 'Початок акції',
        popup_promo_filed_DateExpire: 'Кінець акції',
        popup_promo_filed_Discount: 'Знижка %',
    }, CustomerPluginShop);
});