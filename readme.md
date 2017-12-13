# Redis Web Token with PHP

## Why ?

In order to share our authentication between multiple instance and whatever technology you use, using RWT will bring some simplicity to all that stuff.
By using third part component (redis), we'll be able to check token in each microservice.

```
  ----------       ----------------
  | redis  | <-----|  PHP worker  |
  ----------       ----------------
    ^   ^
    |   |        ---------------------
    |   ---------|  Node instance 1  |
    |            ---------------------
    |
    |            ---------------------
    -------------|  Node instance 2  |
                 ---------------------

```

## Usage

```php
<?php
require "./redis-web-token.php";
use RedisWebToken\RedisWebToken;

$rwt = new RedisWebToken([
  'redis' => [
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => 6379,
    'prefix' => 'sess:'
  ],
  'custom' => [
    'expire' => (60 * 60 * 4), // 4 hours
    'verifyExtendsToken' => true
  ]
]);

// Authenticate
$token = $rwt->sign([
  'username' => 'john',
  'pasword' => 'doe123',
  'age'  => 21,
  'date' => $dateToStr
], $secretKey, [
  'expire' => 60 * 60 // 1 hour
]);

// Verifying token
$rwt->verify($token, $secretKey);

// Extend token
$rwt->extend($token, $secretKey);

// Destroy token
$rwt->destroy($token, $secretKey);

?>
```
