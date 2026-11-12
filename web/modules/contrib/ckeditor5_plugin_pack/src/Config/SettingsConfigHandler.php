<?php

/*
 * Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_plugin_pack\Config;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class SettingsConfigHandler implements SettingsConfigHandlerInterface {

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected LibraryDiscoveryInterface $libraryDiscovery;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The module configuration.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**

  /**
   * Constructs the handler.
   *
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   Library discovery service.
   */
  public function __construct(protected LibraryDiscoveryInterface $library_discovery, protected ConfigFactoryInterface $config_factory, protected readonly ModuleHandlerInterface $module_handler) {
    $this->libraryDiscovery = $library_discovery;
    $this->configFactory = $config_factory;
    $this->config = $config_factory->get('ckeditor5_plugin_pack.settings');
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getDllLocations(): array {
    $paths = [];
    $isVersionOverrideActive = FALSE;

    $local_path = $this->config?->get('dll_location');
    if (!empty($local_path)) {
      // Ensure trailing slash is always present.
      $local_path = rtrim($local_path, ' /') . '/';
      $local_path = $this->replaceTokens($local_path);
      $paths[] = $local_path;
    }

    if ($this->moduleHandler->moduleExists('ckeditor5_premium_features_version_override')) {
      $versionOverride = $this->configFactory->get('ckeditor5_premium_features_version_override.settings');
      if ($versionOverride->get('enabled') && $versionOverride->get('version')) {
        $isVersionOverrideActive = TRUE;
      }
    }

    if (!$isVersionOverrideActive) {
      // Use default local path only if version override is not active,
      // to prevent plugin version incompatibilities.
      $paths[] = '/core/assets/vendor/ckeditor5/';
    }

    return $paths;
  }

  /**
   * {@inheritdoc}
   */
  public function getRemoteDllLocation(): string {
    return 'https://cdn.ckeditor.com/ckeditor5/' . $this->getDllVersion() . '/dll/';
  }

  /**
   * Gets the DLLs version.
   *
   * @return string
   *   The DLLs version.
   */
  public function getDllVersion(): string {
    $library = $this->libraryDiscovery->getLibraryByName('core', 'ckeditor5');
    // Core rebuilds can carry a "-drupal.N" suffix that has no CDN build.
    $version = preg_replace('/-drupal\.\d+$/', '', $library['version']);

    // Mapping for versions that do not match.
    $mapping = [
      '47.6.3' => '47.7.4',
    ];

    return $mapping[$version] ?? $version;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocalLibraryPathSpecified(): bool {
    return !empty($this->config?->get('dll_location'));
  }

  /**
   * Replaces supported tokens in passed parameter path.
   *
   * @param string $path
   *   A URL with potential tokens to replace.
   *
   * @return string
   */
  protected function replaceTokens(string $path): string {
    $tokens = [
      SettingsConfigHandlerInterface::DLL_PATH_VERSION_TOKEN => $this->getDllVersion(),
    ];

    foreach ($tokens as $token => $value) {
      if (!$value) {
        continue;
      }
      $path = str_replace($token, $value, $path);
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function isCdnBlocked(): bool {
    return (bool) $this->config?->get('block_cdn');
  }

  /**
   * {@inheritdoc}
   */
  public function libraryExists(string $library): bool {
    $dllLocations = $this->getDllLocations();
    foreach ($dllLocations as $location) {
      $path = './' . $location . '/' . $library . '/' . $library . '.js';
      if (realpath($path)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
