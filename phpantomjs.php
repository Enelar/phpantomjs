<?php namespace phpantomjs;

require('tunnel.php');

class phpantomjs
{
  public $cookies;
  public $page_settings;
  public $command_line;

  public function Inject( $url, $code )
  {
    $params = $this->BuildParams($url, $code);
    return $this->Execute(__DIR__."/inject.js", $params);
  }

  public function Execute($script, $params)
  {
    $tunnel = new tunnel();
    return $tunnel->ExecuteTask($script, $params);
  }

  public function BuildParams($url, $code)
  {
    return
    [
      "url" => $url,
      "cookies" => $this->cookies,
      "page_settings" => $this->page_settings,
      "inject_code" => $code,
      "command_line" => $this->command_line,
    ];
  }

  public function __construct()
  {
    $this->cookies = [];
    $this->page_settings = [];
    $this->command_line = [];

    $this->command_line[] = "--ignore-ssl-errors=true";
    $this->command_line[] = "--ssl-protocol=tlsv1";

    $this->page_settings['loadImages'] = false;
    $this->page_settings['localToRemoteUrlAccessEnabled'] = true;
    $this->page_settings['resourceTimeout'] = 5000;
    $this->page_settings['userAgent'] = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36';
  }
}
