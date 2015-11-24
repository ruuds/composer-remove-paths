<?php

namespace ruuds\Composer\Tests;

use ruuds\Composer\PathRemover;
use Composer\Util\Filesystem;
use Composer\Config;
use Symfony\Component\Yaml\Exception\RuntimeException;

class PathRemoverTest extends \PHPUnit_Framework_TestCase {

  /**
   * @var string
   */
  private $workDirectory;

  public function __construct($name = NULL, array $data = array(), $dataName = '') {

    $this->workDirectory = __DIR__ . '/workdir_' . uniqid();
    mkdir($this->workDirectory);

    if (!is_dir($this->workDirectory)) {
      throw new RuntimeException("Failed creating workDirectory");
    }

    parent::__construct($name, $data, $dataName);
  }

  public function __destruct() {
    foreach (glob($this->workDirectory . '/*') as $file) {
      unlink($file);
    }
    rmdir($this->workDirectory);

  }


  /**
   * set up test environmemt
   */
  public function setUp() {
    $this->fs = new Filesystem();
    $this->io = $this->getMock('Composer\IO\IOInterface');
  }

  public function testRemove() {
    // Create testfiles
    $file1 = $this->workDirectory . '/file1.txt';
    file_put_contents($file1, 'Contents of file1');

    $file2 = $this->workDirectory . '/file2.txt';
    file_put_contents($file2, 'Contents of file2');

    $file3 = $this->workDirectory . '/file3.txt';
    file_put_contents($file3, 'Contents of file3');

    $installPaths = array(
      $this->workDirectory
    );

    $removePaths = array(
      $file1,
      $file2,
      $file2
    );

    $remover = new PathRemover($installPaths,$removePaths,$this->fs,$this->io);
    $this->assertFileExists($file1,'File1 created');
    $this->assertFileExists($file2,'File2 created');
    $this->assertFileExists($file3,'File3 created');

    $remover->remove();

    $this->assertFileNotExists($file1,'File1 removed');
    $this->assertFileNotExists($file2,'File2 removed');
    $this->assertFileExists($file3,'File3 still exists');
  }
}