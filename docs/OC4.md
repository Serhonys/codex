# OpenCart 4 adaptation

OpenCart 4 moved extensions into namespaced packages:

```
extension/ukrposhta/
  admin/
  catalog/
  system/
  install.json
```

## Mapping from this package (OC 3 / OcStore 3.0.4.1)

| OC 3 path | OC 4 path |
|-----------|-----------|
| `upload/admin/controller/extension/shipping/ukrposhta.php` | `extension/ukrposhta/admin/controller/shipping/ukrposhta.php` |
| `upload/catalog/model/extension/shipping/ukrposhta.php` | `extension/ukrposhta/catalog/model/shipping/ukrposhta.php` |
| `upload/system/library/ukrposhta/api.php` | `extension/ukrposhta/system/library/ukrposhta/api.php` |

### Class names (OC 4)

```php
namespace Opencart\Admin\Controller\Extension\Ukrposhta\Shipping;
class Ukrposhta extends \Opencart\System\Engine\Controller {}
```

```php
namespace Opencart\Catalog\Model\Extension\Ukrposhta\Shipping;
class Ukrposhta extends \Opencart\System\Engine\Model {}
```

### install.json example

```json
{
  "name": "Ukrposhta API",
  "version": "1.0.0",
  "author": "Cursor Agent",
  "link": "https://dev.ukrposhta.ua/documentation"
}
```

Setting keys stay as `shipping_ukrposhta_*`. Routes become `extension/ukrposhta/shipping/ukrposhta`.

The eCom API client (`UkrposhtaApi`) is framework-agnostic and can be reused as-is.
