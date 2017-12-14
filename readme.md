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

---

## RWT Parameters

When you require RWT, you should pass extra parameters to the function :

* {Array} Redis configuration, for example host, port, prefix...
* {Array} Custom RWT configuration, see options

## RWT Custom Options

| Parameter          | Type     | Details                                                             |
|--------------------|:---------|:--------------------------------------------------------------------|
| expire             | Integer  | Set the token TTL in seconds                                        |
| verifyExtendsToken | Boolean  | Extend automatically the token life each time we check its validity |

---

## Methods


### sign

Generate the redis token.

**Parameters**

* {Object} User object
* {String} Secret
* {Object} expire key : Custom expire date (be careful with this value, if you use revive, your global expire configuration will overwrite this value each time we'll call verify method)


### verify

Check if hour token is alive an return the User object values we set at connection, if you edit user values during the session, those data may be outdated. You must call the sign method each time you update your user's values.

**Parameters**

* {String} Token
* {String} Secret


### extend

Reset the TTL of our token with default expire value in our configuration.

**Parameters**

* {String} Token
* {String} Secret

### destroy

Destroy the token.

**Parameters**

* {String} Token
* {String} Secret


---
