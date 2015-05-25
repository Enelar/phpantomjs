<?php

require('tunnel.php');

class phpantomjs
{
  public $cookies;
  public $page_settings;

  public function Inject( $url, $code )
  {
    $arguments =
    [
      "url" => $url,
      "cookies" => $cookies,
      "page_settings" => $page_settings,
      "inject_code" => $code,
    ];

    $tunnel = new tunnel();
    return $tunnel->ExecuteTask(__DIR__."/inject.js", $arguments);
  }

  public function __construct()
  {
    $this->cookies = [];
    $this->page_settings = [];
  }
}