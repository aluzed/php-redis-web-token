<?php

require "./redis-web-token.php";
require "./configuration.php";

use RedisWebToken\RedisWebToken;

$rwt = new RedisWebToken($configuration);
$secretKey = $configuration['security']['secret'];

$date = new DateTime();
$dateToStr = $date->format('Y-m-d');

// Authenticate
$token = $rwt->sign([
  'username' => 'john',
  'pasword' => 'doe123',
  'age'  => 21,
  'date' => $dateToStr
], $secretKey, [
  'expire' => 60 * 60 // 1 hour
]);
print_r($token);
echo "<br>";

// Verifying token
// $rwt->verify("__PASTE_YOUR_TOKEN_HERE__", $secretKey);
$user = $rwt->verify("cebe-709e-b6d3-bcd9-fa9b", $secretKey);
print_r($user);

// Extend token
// $rwt->extend("__PASTE_YOUR_TOKEN_HERE__", $secretKey);

// Destroy token
// $rwt->destroy("__PASTE_YOUR_TOKEN_HERE__", $secretKey);

?>
