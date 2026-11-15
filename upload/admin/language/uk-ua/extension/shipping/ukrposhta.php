<?php
// Heading
$_['heading_title']    = 'Укрпошта API';

// Text
$_['text_extension']   = 'Доповнення';
$_['text_success']     = 'Налаштування модуля Укрпошта API збережено!';
$_['text_edit']        = 'Редагування модуля Укрпошта API';
$_['text_enabled']     = 'Увімкнено';
$_['text_disabled']    = 'Вимкнено';
$_['text_all_zones']   = 'Усі зони';
$_['text_none']        = '— Немає —';
$_['text_home']        = 'Головна';
$_['text_no_shipments']= 'Накладних ще немає';
$_['text_shipment_created'] = 'Створено відправлення. ШКІ: %s';
$_['text_shipment_deleted'] = 'Відправлення видалено';

// Entry
$_['entry_status']     = 'Статус';
$_['entry_sort_order'] = 'Порядок сортування';
$_['entry_geo_zone']   = 'Географічна зона';
$_['entry_tax_class']  = 'Податковий клас';
$_['entry_bearer_ecom']= 'Bearer eCom';
$_['entry_bearer_tracking'] = 'Bearer StatusTracking';
$_['entry_token']      = 'User / Counterparty token';
$_['entry_sender_uuid']= 'UUID відправника';
$_['entry_sender_postcode'] = 'Індекс відправника';
$_['entry_sandbox']    = 'Sandbox (dev.ukrposhta.ua)';
$_['entry_debug']      = 'Режим налагодження (лог)';
$_['entry_timeout']    = 'cURL timeout (сек)';
$_['entry_api_calc']   = 'Розрахунок вартості через API';
$_['entry_fallback_cost'] = 'Резервна вартість доставки';
$_['entry_free_total'] = 'Безкоштовно від суми';
$_['entry_min_order']  = 'Мін. сума замовлення';
$_['entry_max_order']  = 'Макс. сума замовлення';
$_['entry_default_weight'] = 'Вага за замовчуванням, г';
$_['entry_min_weight'] = 'Мін. вага, г';
$_['entry_default_length'] = 'Довжина за замовчуванням, см';
$_['entry_default_width']  = 'Ширина за замовчуванням, см';
$_['entry_default_height'] = 'Висота за замовчуванням, см';
$_['entry_min_declared'] = 'Мін. оголошена вартість';
$_['entry_description']= 'Опис відправлення';
$_['entry_middlename'] = 'По батькові за замовчуванням';
$_['entry_paid_by_recipient'] = 'Доставку оплачує одержувач';
$_['entry_postpay']    = 'Післяплата (накладений платіж)';
$_['entry_cod_codes']  = 'Коди оплати для післяплати';
$_['entry_sms']        = 'SMS про прибуття';
$_['entry_check_on_delivery'] = 'Огляд при врученні';
$_['entry_add_tracking'] = 'Додавати ШКІ до способу доставки';
$_['entry_methods']    = 'Способи доставки';
$_['entry_method_express_w2w'] = 'Експрес → відділення (W2W)';
$_['entry_method_express_w2d'] = 'Експрес → курʼєр (W2D)';
$_['entry_method_standard_w2w'] = 'Стандарт → відділення (W2W)';
$_['entry_method_standard_w2d'] = 'Стандарт → курʼєр (W2D)';

// Tab
$_['tab_general']      = 'Загальні';
$_['tab_api']          = 'API';
$_['tab_sender']       = 'Відправник / посилка';
$_['tab_methods']      = 'Способи';
$_['tab_payment']      = 'Оплата';

// Help
$_['help_tokens']      = 'Токени видає менеджер Укрпошти після договору (eCom Bearer, StatusTracking Bearer, Counterparty token). UUID відправника — клієнт, створений у кабінеті/API.';
$_['help_description'] = 'Макроси: {order_id}, {total}';
$_['help_cod_codes']   = 'Через кому, наприклад: cod,bank_transfer';

// Button
$_['button_save']      = 'Зберегти';
$_['button_cancel']    = 'Скасувати';
$_['button_create_shipment'] = 'Створити ТТН';
$_['button_sticker']   = 'Ярлик PDF';
$_['button_track']     = 'Статус';
$_['button_delete_shipment'] = 'Видалити';

// Column
$_['column_barcode']   = 'ШКІ';
$_['column_status']    = 'Статус';
$_['column_price']     = 'Вартість доставки';
$_['column_date']      = 'Дата';
$_['column_action']    = 'Дія';

// Error
$_['error_permission'] = 'У вас немає прав для зміни модуля Укрпошта API!';
