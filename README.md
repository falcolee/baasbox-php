# baasbox-php
A simple PHP client for [Baasbox](https://github.com/baasbox/baasbox).
Inspired by [hook-php](https://github.com/doubleleft/hook-php).

# Usage
---

```php
<?php
$box = Baasbox\Client::configure(array(
  'app_id' => 1234567890,
  'endpoint' => 'http://www.test.com:9000/',
  'authorization'=>'fdc:123456'	
));

$box->collection('scores')->create(array(
  'name' => 'Endel',
  'score' => 7
));
```