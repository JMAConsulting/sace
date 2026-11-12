<?php

/*
 * Copyright (c) 2003-2026, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

declare(strict_types=1);

namespace Drupal\ckeditor5_plugin_pack_font;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Helper service for font settings migration.
 */
class MigrationHelper {

  use StringTranslationTrait;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a MigrationHelper object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger) {
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * Migrates font configuration from old modules to CKEditor 5 Plugin Pack Font.
   */
  public function migrateConfigurations(): void {
    $is_ckeditor5_font = $this->moduleHandler->moduleExists('ckeditor5_font');
    $is_ckeditor_font = $this->moduleHandler->moduleExists('ckeditor_font');

    // Only migrate if the old module is still installed.
    if (!$is_ckeditor5_font && !$is_ckeditor_font) {
      return;
    }

    $storage = $this->entityTypeManager->getStorage('editor');
    /** @var \Drupal\editor\Entity\Editor[] $editors */
    $editors = $storage->loadMultiple();

    foreach ($editors as $editor) {
      // Skip editors not using CKEditor 5.
      if ($editor->getEditor() !== 'ckeditor5') {
        continue;
      }

      $migrated = FALSE;
      $settings = $editor->getSettings();
      if ($is_ckeditor_font) {
        $settings = $this->handleCkeditorFontMigration($settings, $migrated);
      }
      elseif ($is_ckeditor5_font) {
        $settings = $this->handleCkeditor5FontMigration($settings, $migrated);
      }

      if ($migrated) {
        $editor->setSettings($settings);
        $editor->save();
        $this->messenger->addStatus($this->t('Fonts configuration for the "@label" text format has been migrated.', ['@label' => $editor->label()]));
      }
    }
  }

  /**
   * Handles migration from ckeditor_font module.
   *
   * @param array $settings
   *   The editor settings.
   * @param bool $migrated
   *   Flag informimg whether there were any migrations processed.
   *
   * @return array
   *   The updated settings.
   */
  protected function handleCkeditorFontMigration(array $settings, bool &$migrated): array {
    $plugins = $settings['plugins'] ?? [];
    $toolbar = $settings['toolbar']['items'] ?? [];

    // Old plugin config namespace.
    if (in_array('fontFamily', $toolbar)) {
      $plugins["ckeditor5_plugin_pack_font__font_family"]["options"] = implode("\r\n", $plugins["ckeditor_font_font"]["font_names"] ?? []);
      $size_options = [];
      foreach ($plugins["ckeditor_font_font"]["font_sizes"] ?? [] as $value) {
        $size_options[] = $value['model'] . '|' . $value['title'];
      }
      $plugins["ckeditor5_plugin_pack_font__font_size"]["options"] = implode("\r\n", $size_options);
      $migrated = TRUE;
    }
    if (in_array('fontSize', $toolbar)) {
      $size_options = [];
      foreach ($plugins["ckeditor_font_font"]["font_sizes"] ?? [] as $value) {
        $size_options[] = $value['model'] . '|' . $value['title'];
      }
      $plugins["ckeditor5_plugin_pack_font__font_size"]["options"] = implode("\r\n", $size_options);
      $migrated = TRUE;
    }
    if (!empty(array_intersect(['fontColor', 'fontBackgroundColor'], $toolbar))) {
      $colors = [];
      $color_settings = [];
      if (isset($plugins["ckeditor_font_font_background_color"]) && !empty($plugins["ckeditor_font_font_background_color"])) {
        $color_settings['bg_color_columns'] = $plugins["ckeditor_font_font_background_color"]['columns'];
        $color_settings['bg_color_document_colors'] = $plugins["ckeditor_font_font_background_color"]['documentColors'];
        $items = explode("\r\n", $plugins["ckeditor_font_font_background_color"]["font_backgroundcolors"]);
        foreach ($items as $color) {
          if (empty($color)) {
            continue;
          }
          [$value, $label] = explode('|', $color);

          $colors[$label] = [
            'label' => $label,
            'color' => $this->colorStringToText($value),
            'type' => [
              'font' => '0',
              'background' => 'background',
            ],
          ];
        }
      }
      if (isset($plugins["ckeditor_font_font_color"]) && !empty($plugins["ckeditor_font_font_color"])) {
        $color_settings['font_color_columns'] = $plugins["ckeditor_font_font_color"]['columns'];
        $color_settings['font_color_document_colors'] = $plugins["ckeditor_font_font_color"]['documentColors'];
        $items = explode("\r\n", $plugins["ckeditor_font_font_color"]["font_colors"]);
        foreach ($items as $color) {
          if (empty($color)) {
            continue;
          }
          [$value, $label] = explode('|', $color);

          if (isset($colors[$label])) {
            $colors[$label]['type']['font'] = 'font';
          }
          else {
            $colors[$label] = [
              'label' => $label,
              'color' => $this->colorStringToText($value),
              'type' => [
                'font' => 'font',
                'background' => '0',
              ],
            ];
          }
        }
      }
      $color_settings['colors'] = $colors;
      $plugins["ckeditor5_plugin_pack_font__font_color"] = $color_settings;
      $migrated = TRUE;
    }

    $settings['plugins'] = $plugins;
    return $settings;
  }

  /**
   * Handles migration from ckeditor5_font module.
   *
   * @param array $settings
   *   The editor settings.
   * @param bool $migrated
   *   Flag informimg whether there were any migrations processed.
   *
   * @return array
   *   The updated settings.
   */
  protected function handleCkeditor5FontMigration(array $settings, bool &$migrated): array {
    $plugins = $settings['plugins'] ?? [];
    $toolbar = $settings['toolbar']['items'] ?? [];

    $new_colors = [];
    if (!empty(array_intersect(['fontColor', 'fontBackgroundColor'], $toolbar))) {
      $old_colors = json_decode($plugins['ckeditor5_font_colors']['colors'] ?? '', TRUE);
      if (is_array($old_colors)) {
        foreach ($old_colors as $color) {
          $new_colors[] = [
            'label' => $color['label'],
            'color' => $color['color'],
            'type' => [
              'font' => 'font',
              'background' => 'background',
            ],
          ];
        }
      }
    }
    if ($new_colors) {
      $settings['plugins']['ckeditor5_plugin_pack_font__font_color']['colors'] = $new_colors;
      $migrated = TRUE;
    }

    return $settings;
  }

  /**
   * Converts a color string to hex format.
   *
   * @param string $color
   *   The color string.
   *
   * @return string
   *   The color in hex format.
   */
  protected function colorStringToText(string $color): string {
    $color = trim($color);

    // HEX → normalize.
    if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color)) {
      $hex = strtolower($color);

      if (strlen($hex) === 4) {
        // expand #fff → #ffffff.
        return '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
      }

      return $hex;
    }

    // RGB.
    if (preg_match('/rgb\s*\(\s*(\d+),\s*(\d+),\s*(\d+)\s*\)/i', $color, $m)) {
      return sprintf("#%02x%02x%02x", $m[1], $m[2], $m[3]);
    }

    // HSL.
    if (preg_match('/hsl\s*\(\s*(\d+),\s*(\d+)%?,\s*(\d+)%?\s*\)/i', $color, $m)) {
      return $this->hslToHex((float) $m[1], (float) $m[2], (float) $m[3]);
    }

    // Fallback - return without conversion.
    return $color;
  }

  /**
   * Converts HSL to HEX.
   *
   * @param float $h
   *   Hue.
   * @param float $s
   *   Saturation.
   * @param float $l
   *   Lightness.
   *
   * @return string
   *   Hex value.
   */
  protected function hslToHex(float $h, float $s, float $l): string {
    $rgb = $this->hslToRgb($h, $s, $l);
    return sprintf("#%02x%02x%02x", $rgb['r'], $rgb['g'], $rgb['b']);
  }

  /**
   * Converts HSL to RGB.
   *
   * @param float $h
   *   Hue.
   * @param float $s
   *   Saturation.
   * @param float $l
   *   Lightness.
   *
   * @return array
   *   RGB values.
   */
  protected function hslToRgb(float $h, float $s, float $l): array {
    $h = ($h % 360 + 360) % 360;
    $s /= 100;
    $l /= 100;

    $c = (1 - abs(2 * $l - 1)) * $s;
    $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
    $m = $l - $c / 2;

    if ($h < 60) {
      [$r1, $g1, $b1] = [$c, $x, 0];
    }
    elseif ($h < 120) {
      [$r1, $g1, $b1] = [$x, $c, 0];
    }
    elseif ($h < 180) {
      [$r1, $g1, $b1] = [0, $c, $x];
    }
    elseif ($h < 240) {
      [$r1, $g1, $b1] = [0, $x, $c];
    }
    elseif ($h < 300) {
      [$r1, $g1, $b1] = [$x, 0, $c];
    }
    else {
      [$r1, $g1, $b1] = [$c, 0, $x];
    }

    return [
      'r' => (int) round(($r1 + $m) * 255),
      'g' => (int) round(($g1 + $m) * 255),
      'b' => (int) round(($b1 + $m) * 255),
    ];
  }

}
