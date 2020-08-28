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
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UninstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;

/**
 * Class PluginWrapper
 *
 * @package ruuds\Composer
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
   * PluginWrapper constructor.
   *
   * @param \Composer\Composer $composer
   *
   * @param \Composer\IO\IOInterface $io
   */
  public function __construct(Composer $composer, IOInterface $io) {
    $this->io = $io;
    $this->composer = $composer;
    $this->filesystem = new Filesystem();
  }

  /**
   * Pre Package event behaviour for backing up removed paths.
   *
   * @param \Composer\Installer\PackageEvent $event
   *
   * @throws \Exception
   */
  public function postPackage(PackageEvent $event) {
    $packages = $this->getPackagesFromEvent($event);
    $paths = $this->getInstallPathsFromPackages($packages);

    $remover = new PathRemover(
      $paths,
      $this->getPathsToRemove(),
      $this->filesystem,
      $this->io
    );

    $remover->remove();
  }

  /**
   * Retrieve install paths from package installers.
   *
   * @param \Composer\Package\PackageInterface[] $packages
   *
   * @return string[]
   */
  protected function getInstallPathsFromPackages(array $packages) {
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
   * Get remove-paths from root configuration.
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
   *
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

  /**
   * Retrieves relevant package from the event.
   *
   * In the case of update, the target package is retrieved, as that will
   * provide the path the package will be installed to.
   *
   * @param \Composer\Installer\PackageEvent $event
   *
   * @return \Composer\Package\PackageInterface[]
   * @throws \Exception
   */
  protected function getPackagesFromEvent(PackageEvent $event) {
    $packages = [];
    $operation = $event->getOperation();
    if ($operation instanceof InstallOperation) {
      $packages = array($operation->getPackage());
    }
    elseif ($operation instanceof UpdateOperation) {
      $packages = array(
        $operation->getInitialPackage(),
        $operation->getTargetPackage(),
      );
    }
    elseif ($operation instanceof UninstallOperation) {
      $packages = array($operation->getPackage());
    }
    return $packages;
  }
}