<?php

/**
 * @file
 * Contains ruuds\Composer\Plugin.
 */

namespace ruuds\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvent;

/**
 * Class Plugin
 *
 * @package ruuds\Composer
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

  /**
   * @var \ruuds\Composer\PluginWrapper
   */
  protected $wrapper;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->wrapper = new PluginWrapper($composer, $io);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array(
      PackageEvents::POST_PACKAGE_INSTALL => 'postPackage',
      PackageEvents::POST_PACKAGE_UPDATE => 'postPackage',
      PackageEvents::POST_PACKAGE_UNINSTALL => 'postPackage',
    );
  }

  /**
   * Pre Package event behaviour for backing up removed paths.
   *
   * @param \Composer\Installer\PackageEvent $event
   *
   * @throws \Exception
   */
  public function postPackage(PackageEvent $event) {
    $this->wrapper->postPackage($event);
  }

}