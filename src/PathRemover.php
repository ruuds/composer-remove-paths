<?php

/**
 * Contains \ruuds\Composer\PathRemover
 */

namespace ruuds\Composer;

/**
 * Class PathRemover
 */
class PathRemover {

  /**
   * @var string[]
   */
  protected $installPaths;

  /**
   * @var string[]
   */
  protected $removePaths;

  /**
   * @var \Composer\Util\FileSystem
   */
  protected $filesystem;

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var string[string]
   */
  protected $backups = array();

  /**
   * @var string[string]
   */
  protected $filepermissions = array();

  /**
   * Constructor.
   *
   * @param string[] $installPaths
   *   Array of install paths (must be absolute)
   * @param string[] $removePaths
   *   Array of paths to remove (must be absolute)
   * @param \Composer\Util\FileSystem $filesystem
   *   The filesystem provided by composer to work with.
   * @param \Composer\IO\IOInterface $io
   *   IO interface for writing messages.
   */
  public function __construct($installPaths, $removePaths, \Composer\Util\FileSystem $filesystem, \Composer\IO\IOInterface $io) {
    $this->installPaths = array_unique($installPaths);
    $this->removePaths = array_unique($removePaths);
    $this->filesystem = $filesystem;
    $this->io = $io;
  }

  /**
   * Remove the paths.
   */
  public function remove() {

    foreach ($this->installPaths as $installPath) {
      $installPathNormalized = $this->filesystem->normalizePath($installPath);

      // Check if any path may be affected by modifying the install path.
      $relevant_paths = array();
      foreach ($this->removePaths as $path) {
        $normalizedPath = $this->filesystem->normalizePath($path);
        if (static::file_exists($path) && strpos($normalizedPath, $installPathNormalized) === 0) {
          $relevant_paths[] = $normalizedPath;
        }
      }

      foreach ($relevant_paths as $original) {
        if (is_dir($original)) {
          // Remove directory
          $this->removeDirectoryRecursively($original);
        }
        else {
          // Remove file
          unlink($original);
        }

      }
    }
  }

  private function removeDirectoryRecursively($path) {
    foreach (glob($path . '/*') as $file) {
      if (is_dir($file)) {
        $this->removeDirectoryRecursively($file);
      }
      else {
        unlink($file);
      }
    }
    rmdir($path);
  }

  /**
   * Check if file really exists.
   *
   * As php can only determine, whether a file or folder exists when the parent
   * directory is executable, we need to provide a workaround.
   *
   * @param $path
   *   The path as in file_exists()
   *
   * @return bool
   *   Returns TRUE if file exists, like in file_exists(),
   *   but without restriction.
   *
   * @see file_exists()
   */
  static public function file_exists($path) {

    // Get all parent directories.
    $folders = array();
    $reset_perms = array();
    $folder = $path;
    while ($folder = dirname($folder)) {
      if ($folder === '.' || $folder === '/' || preg_match("/^.:\\\\$/", $folder)) {
        break;
      }
      elseif ($folder === '') {
        continue;
      }
      $folders[] = $folder;
    }

    foreach (array_reverse($folders) as $current_folder) {
      // In the case a parent folder does not exist, the file cannot exist.
      if (!is_dir($current_folder)) {
        $return = FALSE;
        break;
      }
      // In the case the folder is really a folder, but not executable, we need
      // to change that, so we can check if the file really exists.
      elseif (!is_executable($current_folder)) {
        $reset_perms[$current_folder] = fileperms($current_folder);
        chmod($current_folder, 0755);
      }
    }

    if (!isset($return)) {
      $return = file_exists($path);
    }

    // Reset permissions in reverse order.
    foreach (array_reverse($reset_perms, TRUE) as $folder => $mode) {
      chmod($folder, $mode);
    }

    return $return;
  }
}