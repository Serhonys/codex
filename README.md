# Укрпошта API — модуль доставки для OpenCart / OcStore

Модуль інтеграції з **Ukrposhta eCom API** для інтернет-магазинів на OpenCart і OcStore.

**Цільова платформа:** OcStore **3.0.4.1** / OpenCart **3.0.x**  
**Також:** OpenCart 2.3 (`.tpl` + старі коди мов), заготовки під OpenCart 4 — див. `docs/OC4.md`

Офіційна документація API: [dev.ukrposhta.ua/documentation](https://dev.ukrposhta.ua/documentation)

## Можливості

- Способи доставки: **Express/Standard** × **W2W (відділення) / W2D (курʼєр)**
- Розрахунок вартості через `POST /domestic/delivery-price` (+ резервний тариф)
- Checkout: пошук міста, вибір відділення або адреса для курʼєра (Address Classifier)
- Адмін-замовлення: створення ТТН, ярлик PDF, трекінг, видалення
- Післяплата (postPay), SMS, огляд при врученні
- Sandbox (`dev.ukrposhta.ua`) і production
- Мови: `uk-ua` / `ua-ua`, `ru-ru`, `en-gb` (+ english/russian/ukrainian для OC 2.3)

## Встановлення (OcStore / OpenCart 3.x)

1. Скопіюйте вміст `upload/` у корінь магазину (через FTP або файловий менеджер).
2. У адмінці: **Доповнення → Встановлення доповнень** — завантажте `install.xml` (OCMOD).
3. **Доповнення → Менеджер доповнень → Оновити** (синя кнопка).
4. **Доповнення → Доставка → Укрпошта API → Встановити → Редагувати**.
5. Заповніть токени з договору Укрпошти:
   - Bearer eCom
   - Bearer StatusTracking
   - User / Counterparty token
   - UUID відправника (клієнт-відправник у eCom)
   - Індекс відправника
6. Увімкніть потрібні способи доставки та збережіть.

## Структура

```
upload/
  admin/…/shipping/ukrposhta.*     # налаштування + панель ТТН у замовленні
  catalog/…/shipping/ukrposhta.*   # quotes, AJAX міст/відділень, checkout fields
  system/library/ukrposhta/api.php # HTTP-клієнт eCom / Forms / Tracking / Classifier
install.xml                        # OCMOD (order panel + checkout fields + persist)
docs/OC4.md                        # як перенести на OpenCart 4
```

## API-ендпоінти, що використовуються

| Сервіс | Base URL |
|--------|----------|
| eCom | `https://www.ukrposhta.ua/ecom/0.0.1/` |
| Forms (ярлики) | `https://www.ukrposhta.ua/forms/ecom/0.0.1/` |
| StatusTracking | `https://www.ukrposhta.ua/status-tracking/0.0.1/` |
| Address Classifier | `https://www.ukrposhta.ua/address-classifier-ws/` |
| Sandbox eCom | `https://dev.ukrposhta.ua/ecom/0.0.1/` |

## Checkout AJAX

- `index.php?route=extension/shipping/ukrposhta/cities&term=Київ`
- `index.php?route=extension/shipping/ukrposhta/offices&city_id=…`
- `index.php?route=extension/shipping/ukrposhta/save` (POST)

Для кастомних чекаутів (Simple, Journal тощо) підключіть ці URL або шаблон `ukrposhta_fields`.

## Таблиці БД

Створюються при install:

- `oc_ukrposhta_order` — місто/відділення/адреса з чекауту
- `oc_ukrposhta_shipment` — ТТН (uuid, barcode, статус, відповідь API)

## OpenCart 2.3

Файли `.tpl` і коди мов `english` / `russian` / `ukrainian` вже є.  
У 2.3 адмін-роути розширень: `extension/extension` замість `marketplace/extension`, токен сесії — `token` замість `user_token`. За потреби підправте редіректи в admin-контролері.

## Ліцензія / токени

Модуль не містить і не генерує токени Укрпошти — їх видає менеджер після договору. Без валідних Bearer/token створення відправлень і точний розрахунок недоступні; резервна вартість усе одно покаже способи доставки на вітрині.
