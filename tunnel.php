<?php namespace phpantomjs;

class tunnel
{
  private $prefix = "";
  private $postfix = "";
  private $infix = "";

  public function ExecuteTask($jsfile, $arguments = NULL)
  {
    $tunnels = $this->CreateInfrastructure($arguments);

    $res = $this->Execute($jsfile, $tunnels);

    $result = $this->RemoveInfrastructure($tunnels);

    return json_decode($result, true);
  }

  private function CreateFixes($arguments)
  {
    $this->infix = implode(" ", $arguments['command_line']);
  }

  private function RemoveFixes()
  {
    $this->infix = "";
    $this->prefix = "";
    $this->postfix = "";
  }

  private function CreateInfrastructure($arguments)
  {
    $this->CreateFixes($arguments);

    $random = 0;//time();
    $ret =
    [
      "in" => $this->MakeTmpFile("{$random}.in"),
      "out" => $this->MakeTmpFile("{$random}.out"),
    ];

    $in_string = json_encode($arguments);
    file_put_contents($ret['in'], $in_string);

    return $ret;
  }

  private function RemoveInfrastructure($tunnels)
  {
    $this->RemoveFixes();

    $result = file_get_contents($tunnels['out']);
    foreach ($tunnels as $file)
      unlink($file);
    return $result;
  }

  private function MakeTmpFile($name = NULL)
  {
    if ($name == NULL)
      $name = "".time();
    $dir = "/tmp";
    if (defined("PHPANTOMJSDIR"))
      $dir = PHPANTOMJSDIR;
    $result = "$dir/phpantomjs_".$name;
    if (!touch($result))
      die("phpantomjs: failure to create temporary file ".__FILE__.":".__LINE__);
    return $result;
  }

  public function Execute( $file, $arguments )
  {
    $args = [];
    foreach ($arguments as $a)
      $args[] = escapeshellarg($a);

    if ($this->ExtendedFileNameCheck($file) !== false)
      die("phpantomjs: Security warning ".__FILE__.":".__LINE__);

    $phantomjs = __DIR__."/bin/phantomjs";
    $query = "{$phantomjs} {$this->infix} '{$file}' ".(implode(' ', $args));
    $exec = $this->prefix.$query.$this->postfix;

    $res = exec($exec);

    return $res;
  }

  private function ExtendedFileNameCheck( $file )
  {
    $needle = ["..", '"', "'", "\b"];
    foreach ($needle as $need)
      if (($ret = strpos($file, $need, $offset)) !== false)
        return $ret;
    return false;
  }
}
