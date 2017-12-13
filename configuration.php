<?php

$configuration = [
  'redis' => [
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => 6379,
    'prefix' => 'sess:'
  ],
  'custom' => [
    'expire' => (60 * 60 * 4), // 4 hours
    'verifyExtendsToken' => true
  ],
  'security' => [
    'secret'     => 'superNinja'
  ]
];

?>
