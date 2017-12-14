<?php
/**
* redis-web-token
*
* Library that is designed to be multiple technology compliant.
*
* Author: Alexandre PENOMBRE <aluzed@gmail.com>
* Copyright 2017
*/
namespace RedisWebToken;

use \Predis;
use \Exception;

class RedisWebToken {
  var $redis = null;
  var $redisDefaultConfiguration = [
    "scheme" => "tcp",
    "host" => "localhost",
    "port" => 6379
  ];
  var $defaultConfig = [
    'expire' => 60 * 60,
    'verifyExtendsToken' => false
  ];

  /**
  * constructor
  *
  * @param {Array} [
  *   'redis'  => redis config,
  *   'custom' => rwt config
  * ]
  */
  function __construct($config){
    try {
      $customConfig = $this->redisDefaultConfiguration;
      $options = [];

      if(!empty($config['redis'])){
        $customConfig = array_merge($customConfig, $config['redis']);

        if(!empty($config['redis']['prefix']))
          $options += ['prefix' => $config['redis']['prefix']];
      }

      if(!empty($config['custom']))
        $this->defaultConfig = array_merge($this->defaultConfig, $config['custom']);

    	$this->redis = new \Predis\Client($customConfig, $options);
      $this->connect();
    }
    catch (Exception $e) {
    	die($e->getMessage());
    }
  }

  /**
  * generate uid private method
  *
  * Generates a random string
  *
  * @return {String}
  */
  private function generate_uid(){
    return (
      dechex(mt_rand(0, 0xFFFF)) . '-' .
      dechex(mt_rand(0, 0xFFFF)) . '-' .
      dechex(mt_rand(0, 0xFFFF)) . '-' .
      dechex(mt_rand(0, 0xFFFF)) . '-' .
      dechex(mt_rand(0, 0xFFFF))
    );
  }

  /**
  * array_to_hm private method
  *
  * Turns a normal array into a hash multiple for redis
  *
  * @param {Array} $arary
  * @return {Array}
  * @constraint $array length must be > 0
  */
  private function array_to_hm($array){
    if(gettype($array) !== "array")
      throw new Exception('Error, $array must be type of array.');

    if(sizeof($array) === 0)
      throw new Exception('Error, $array length must be greater than 0.');

    $result = [];

    foreach($array as $k => $v) {
      array_push($result, $k);
      array_push($result, $v);
    }

    return $result;
  }

  /**
  * extend_token_validity private method
  *
  * Extend the token TTL
  *
  * @param {String} $hkey
  * @constraint $hkey cannot be null
  */
  private function extend_token_validity($hkey = null){
    if($hkey === null)
      throw new Exception('Error, $hkey cannot be null.');

    $this->redis->expire($hkey, $this->defaultConfig['expire']);
  }

  /**
  * disconnect method
  */
  public function connect(){
    $this->redis->connect();
  }

  /**
  * disconnect method
  */
  public function disconnect(){
    $this->redis->disconnect();
  }

  /**
  * sign method
  *
  * Create a token in redis
  *
  * @param {Array} $array
  * @param {String} $secret
  * @param {Array} $extraParams
  * @constraint $array length must be > 0
  * @constraint $secret cannot be null
  * @constraint $extraParams must be type of array
  */
  public function sign($array = [], $secret = null, $extraParams = []) {
    if(sizeof($array) === 0)
      throw new Exception('Error, $array length must be greater than 0.');

    if($secret === null)
      throw new Exception('Error $secret cannot be null.');

    if(gettype($extraParams) !== "array")
      throw new Exception('Error, $extraParams must be type of array.');

    $token = $this->generate_uid();
    $hkey  = base64_encode($token . $secret);
    $this->redis->hmset($hkey, $this->array_to_hm($array));

    // Set the TTL
    $params = array_merge($this->defaultConfig, $extraParams);
    if(!empty($params['expire']))
      $this->redis->expire($hkey, $params['expire']);

    // Disconnect the socket
    $this->redis->disconnect();

    return $token;
  }

  /**
  * verify method
  *
  * Check the token validity
  *
  * @param {String} $token
  * @param {String} $secret
  * @constraint $token cannot be null
  * @constraint $secret cannot be null
  */
  public function verify($token = null, $secret = null) {
    if($token === null)
      throw new Exception('Error $token cannot be null.');

    if($secret === null)
      throw new Exception('Error $secret cannot be null.');

    $hkey = base64_encode($token . $secret);

    $user = $this->redis->hgetall($hkey);

    // Extend if it is enabled
    if(sizeof($user) > 0 && $this->defaultConfig['verifyExtendsToken']) {
      $this->redis->expire($hkey, $this->defaultConfig['expire']);
    }

    // We does not need redis connection anymore
    $this->redis->disconnect();

    // If $user is empty return false
    return (sizeof($user) === 0) ? false : $user;
  }

  /**
  * extend method
  *
  * Extend the token life time
  *
  * @param {String} $token
  * @param {String} $secret
  * @constraint $token cannot be null
  * @constraint $secret cannot be null
  */
  public function extend($token = null, $secret = null) {
    if($token === null)
      throw new Exception('Error $token cannot be null.');

    if($secret === null)
      throw new Exception('Error $secret cannot be null.');

    $ext = $this->extend_token_validity(base64_encode($token . $secret));

    $this->redis->disconnect();
    return $ext;
  }

  /**
  * destroy method
  *
  * Destroy a token
  *
  * @param {String} $token
  * @param {String} $secret
  * @constraint $token cannot be null
  * @constraint $secret cannot be null
  */
  public function destroy($token = null, $secret = null) {
    if($token === null)
      throw new Exception('Error $token cannot be null.');

    if($secret === null)
      throw new Exception('Error $secret cannot be null.');

    $hkey = base64_encode($token . $secret);

    $del = $this->redis->del($hkey);

    $this->redis->disconnect();
    return $del;
  }
}

?>
