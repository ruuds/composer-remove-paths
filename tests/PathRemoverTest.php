<?php

namespace ruuds\Composer\Tests;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use ruuds\Composer\PathRemover;
use \RuntimeException;

/**
 * Class PathRemoverTest
 *
 * @package ruuds\Composer\Tests
 */
class PathRemoverTest extends TestCase {

  /**
   * @var \Composer\Util\Filesystem
   */
  protected $fs;

  /**
   * @var \Composer\IO\IOInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $io;

  /**
   * @var string
   */
  protected $workDirectory;

  private function removeDirectory($path) {
    foreach (glob($path . '/*') as $file) {
      if (is_dir($file)) {
        $this->removeDirectory($file);
      }
      else {
        unlink($file);
      }

    }
    rmdir($path);
  }

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->fs = new Filesystem();
    $this->io = $this->createMock(IOInterface::class);

    $this->workDirectory = __DIR__ . '/workdir_' . uniqid();
    mkdir($this->workDirectory);

    if (!is_dir($this->workDirectory)) {
      throw new RuntimeException("Failed creating workDirectory");
    }
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown(): void {
    $this->removeDirectory($this->workDirectory);

    parent::tearDown();
  }

  /**
   * TestRemove.
   */
  public function testRemove(): void {
    // Create testfiles
    $file1 = $this->workDirectory . '/file1.txt';
    file_put_contents($file1, 'Contents of file1');

    $file2 = $this->workDirectory . '/file2.txt';
    file_put_contents($file2, 'Contents of file2');

    $file3 = $this->workDirectory . '/file3.txt';
    file_put_contents($file3, 'Contents of file3');

    // Create files nested in directories
    $dir1 = $this->workDirectory . '/dir1';
    mkdir($dir1);

    $file4 = $dir1 . '/file4.txt';
    file_put_contents($file4, 'Contents of file4');

    $dir2 = $dir1 . '/dir2';
    mkdir($dir2);

    $file5 = $dir2 . '/file5.txt';
    file_put_contents($file5, 'Contents of file5');

    $dir3 = $this->workDirectory . '/dir3';
    mkdir($dir3);

    $installPaths = array(
      $this->workDirectory,
    );

    $removePaths = array(
      $file1,
      $file2,
      $file2,
      $dir1,
    );

    $remover = new PathRemover($installPaths, $removePaths, $this->fs, $this->io);
    $this->assertFileExists($file1, 'File1 created');
    $this->assertFileExists($file2, 'File2 created');
    $this->assertFileExists($file3, 'File3 created');
    $this->assertFileExists($dir1, 'Dir1 created');
    $this->assertFileExists($file4, 'File4 created');
    $this->assertFileExists($dir2, 'Dir2 created');
    $this->assertFileExists($file5, 'File5 created');
    $this->assertFileExists($dir3, 'Dir3 created');

    $remover->remove();

    $this->assertFileDoesNotExist($file1, 'File1 removed');
    $this->assertFileDoesNotExist($file2, 'File2 removed');
    $this->assertFileExists($file3, 'File3 still exists');
    $this->assertFileDoesNotExist($file4, 'File4 removed');
    $this->assertFileDoesNotExist($file5, 'File5 removed');
    $this->assertFileDoesNotExist($dir1, 'Dir1 removed');
    $this->assertFileDoesNotExist($dir2, 'Dir2 removed');
    $this->assertFileExists($dir3, 'Dir3 still exists');
  }
}