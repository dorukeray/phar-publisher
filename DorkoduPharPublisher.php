<?php
  namespace Loom\Utils;

  use Phar;
  use FilesystemIterator;

  # the phar publisher utility for simple publishing of phar archives
  class DorkoduPharPublisher
  {
    protected $sourceRoot;    # source root directory
    protected $publishRoot;   # publish root directory
    protected $name;          # file name for PHAR package
    protected $afterEffect;   # executes this after publishing the package
    protected $beforeEffect;  # executes this before publishing the package
    protected $filePattern;   # file filter for package
    protected $defaultStubFileName;   # file for setting the optional default stub

    public function __construct(string $name, string $sourceRoot, string $publishRoot)
    {
      $this->name = $name;
      $this->sourceRoot = $sourceRoot;
      $this->publishRoot = $publishRoot;
      $this->filePattern = NULL;
      $this->defaultStubFileName = 'index.php';
    }

    public function setFilePattern(string $filePattern)
    {
      $this->filePattern = $filePattern;
    }

    # assigns a function before publishing the package
    public function setBeforeEffect($closure)
    {
      if(is_callable($closure)) {
        $this->beforeEffect = $closure;
        return true;
      } else return false; # not a callable
    }
    
    # assigns a function after publishing the package
    public function setAfterEffect($closure)
    {
      if(is_callable($closure)) {
        $this->afterEffect = $closure;
        return true;
      } else return false; # not a callable
    }

    public function setDefaultStub(string $defaultStubFileName)
    {
      $this->defaultStubFileName = $defaultStubFileName;
    }

    # builds the Phar archive and publishes to the given path
    public function publish()
    {
      if (!empty($this->beforeEffect) && is_callable($this->beforeEffect)) {
        call_user_func_array($this->beforeEffect, []);
      }
      
      $pharFileName = $this->publishRoot.'/'.$this->name;

      $phar = new Phar($pharFileName, FileSystemIterator::CURRENT_AS_FILEINFO | FileSystemIterator::KEY_AS_FILENAME, $this->name);
      echo PHP_EOL."breakpoint passed!".PHP_EOL;
      
      

      if (!is_null($this->filePattern)) {
        $phar->buildFromDirectory($this->sourceRoot, $this->filePattern);
      } else {
        $phar->buildFromDirectory($this->sourceRoot);
      }

      $phar->setStub($phar->createDefaultStub($this->defaultStubFileName));      
      
      if (!empty($this->afterEffect) && is_callable($this->afterEffect)) {
        call_user_func_array($this->afterEffect, []);
      }
    }
  }