# transit-php

```php
use transit\JSONReader;
use transit\JSONWriter;
use transit\Transit;

$transit = new Transit(new JSONReader(), new JSONWriter());
$transit->read('["^ ","foo","bar"]');
$transit->write([(object)['foo' => ['bar', true, 1.25]]]);
```