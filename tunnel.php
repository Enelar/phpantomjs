<?php

class tunnel
{
  public function ExecuteTask($jsfile, $arguments = NULL)
  {
    $tunnels = $this->CreateInfrastructure();
    $in_string = json_encode($arguments);

    file_put_contents($tunnels['in'], $in_string);
    $res = $this->Execute($jsfile, [$tunnels['in'], $tunnels['out']]);
    $result = file_get_contents($tunnels['out']);

    $this->RemoveInfrastructure($tunnels);

    return json_decode($result, true);
  }

  private function CreateInfrastructure()
  {
    $random = time();
    return
    [
      "in" => $this->MakeTmpFile("{$random}.in"),
      "out" => $this->MakeTmpFile("{$random}.out"),
    ];
  }

  private function RemoveInfrastructure($i)
  {
    foreach ($i as $file)
      unlink($file);
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

    $query = "phantomjs '{$file}' ".(implode(' ', $args));
    $res = exec($query);

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