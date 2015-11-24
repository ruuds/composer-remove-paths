<?php

/**
 * @file
 * Contains ruuds\Composer\Plugin.
 */

namespace ruuds\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\PackageEvent;
use Composer\Util\Filesystem;

/**
 * Wrapper for making Plugin debuggable.
 */
class PluginWrapper {

  /**
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * @var \Composer\Util\Filesystem
   */
  protected $filesystem;

  /**
   * @var \ruuds\Composer\PathRemover[string]
   */
  protected $preservers;

  /**
   * {@inheritdoc}
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->io = $io;
    $this->composer = $composer;
    $this->filesystem = new Filesystem();
  }

  /**
   * Pre Package event behaviour for backing up preserved paths.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function postPackage(PackageEvent $event) {
    $packages = $this->getPackagesFromEvent($event);
    $key = $this->getUniqueNameFromPackages($packages);
    if ($this->preservers[$key]) {
      $this->preservers[$key]->rollback();
      unset($this->preservers[$key]);
    }
  }

  /**
   * Retrieve install paths from package installers.
   *
   * @param \Composer\Package\PackageInterface[] $packages
   *
   * @return string[]
   */
  protected function getInstallPathsFromPackages(array $packages) {
    /** @var \Composer\Installer\InstallationManager $installationManager */
    $installationManager = $this->composer->getInstallationManager();

    $paths = array();
    foreach ($packages as $package) {
      $paths[] = $installationManager->getInstallPath($package);
    }
    return $this->absolutePaths($paths);
  }

  /**
   * Provides a unique string for a package combination.
   *
   * @param \Composer\Package\PackageInterface[] $packages
   *
   * @return string
   */
  protected function getUniqueNameFromPackages(array $packages) {
    $return = array();
    foreach ($packages as $package) {
      $return[] = $package->getUniqueName();
    }
    sort($return);
    return implode(', ', $return);
  }

  /**
   * Get preserve paths from root configuration.
   *
   * @return string[]
   */
  protected function getPathsToRemove() {
    $extra = $this->composer->getPackage()->getExtra();

    if (!isset($extra['remove-paths'])) {
      $paths = $extra['remove-paths'];
    }
    elseif (!is_array($extra['remove-paths']) && !is_object($extra['remove-paths'])) {
      $paths = array($extra['remove-paths']);
    }
    else {
      $paths = array_values((array) $extra['remove-paths']);
    }

    return $this->absolutePaths($paths);
  }

  /**
   * Helper to convert relative paths to absolute ones.
   *
   * @param string[] $paths
   * @return string[]
   */
  protected function absolutePaths($paths) {
    $return = array();
    foreach ($paths as $path) {

      if (!$this->filesystem->isAbsolutePath($path)) {
        $path = getcwd() . '/' . $path;
      }
      $return[] = $path;
    }
    return $return;
  }
}