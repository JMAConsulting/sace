<?php

namespace Drupal\smart_date\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'smartdate_timezone' widget.
 *
 * @FieldWidget(
 *   id = "smartdate_timezone",
 *   label = @Translation("Date and time range with timezone"),
 *   field_types = {
 *     "smartdate"
 *   }
 * )
 */
class SmartDateTimezoneWidget extends SmartDateDefaultWidget implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'default_tz' => '',
      'custom_tz' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Set default, based on field config.
    $default_label = t('- default: @tz_label -', ['@tz_label' => $this->getSiteTimezone()]);
    switch ($this->getSetting('default_tz')) {
      case '':
        $default_timezone = '';
        break;

      case 'user':
        $default_timezone = date_default_timezone_get();
        break;

      case 'custom':
        $default_timezone = $this->getSetting('custom_tz');
        break;
    }

    $element['timezone']['#type'] = 'select';
    $element['timezone']['#options'] = ['' => $default_label] + $this->getTimezones();

    $element['timezone']['#default_value'] = $items[$delta]->timezone ? $items[$delta]->timezone : $default_timezone;

    $element['timezone']['#attributes']['class'][] = 'field-timezone';
    $element['timezone']['#weight'] = 100;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['default_tz'] = [
      '#type' => 'select',
      '#title' => $this->t('Default timezone'),
      '#default_value' => $this->getSetting('default_tz'),
      '#options' => [
        '' => $this->t('Site default (ignores any user override)'),
        'user' => $this->t("User's timezone, defaulting to site (always saved)"),
        'custom' => $this->t('A custom timezone (always saved)'),
      ],
    ];

    $custom_tz = $this->getSetting('custom_tz') ? $this->getSetting('custom_tz') : $this->getSiteTimezone();

    $element['custom_tz'] = [
      '#type' => 'select',
      '#title' => $this->t('Custom timezone'),
      '#default_value' => $custom_tz,
      '#options' => $this->getTimezones(),
      '#states' => [
        // Show this select only if the 'default_tz' select is set to custom.
        'visible' => [
          'select[name$="[settings][default_tz]"]' => ['value' => 'custom'],
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    switch ($this->getSetting('default_tz')) {
      case '':
        $summary[] = $this->t("The site's timezone will be used unless overridden");
        break;

      case 'user':
        $summary[] = $this->t("The user's timezone will be used by default");
        break;

      case 'custom':
        $summary[] = $this->t('Custom default timezone: @custom_tz', ['@custom_tz' => $this->getSetting('custom_tz')]);
        break;
    }

    return $summary;
  }

  /**
   * Helper function to retrieve available timezones.
   */
  public function getTimezones() {
    return system_time_zones(FALSE, TRUE);
  }

  /**
   * Helper function to return only the site's timezone.
   */
  public function getSiteTimezone() {
    // Ignore PHP strict notice if time zone has not yet been set in the php.ini
    // configuration.
    $config = \Drupal::config('system.date');
    $config_data_default_timezone = $config
      ->get('timezone.default');
    return !empty($config_data_default_timezone) ? $config_data_default_timezone : @date_default_timezone_get();
  }

}
