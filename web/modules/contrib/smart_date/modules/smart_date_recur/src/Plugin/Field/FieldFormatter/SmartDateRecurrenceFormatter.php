<?php

namespace Drupal\smart_date_recur\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\smart_date\Entity\SmartDateFormat;
use Drupal\smart_date\Plugin\Field\FieldFormatter\SmartDateDefaultFormatter;
use Drupal\smart_date\SmartDateTrait;
use Drupal\smart_date_recur\Entity\SmartDateRule;

/**
 * Plugin for a recurrence-optimized formatter for 'smartdate' fields.
 *
 * This formatter renders the start time range using <time> elements, with
 * recurring dates given special formatting.
 *
 * @FieldFormatter(
 *   id = "smartdate_recurring",
 *   label = @Translation("Recurring"),
 *   field_types = {
 *     "smartdate"
 *   }
 * )
 */
class SmartDateRecurrenceFormatter extends SmartDateDefaultFormatter {

  use SmartDateTrait;

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'past_display' => '2',
      'upcoming_display' => '2',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Use the upstream settings form, which gives us a control to override the
    // timezone.
    $form = parent::settingsForm($form, $form_state);

    // Ask the user to choose how many past and upcoming instances to display.
    $form['past_display'] = [
      '#type' => 'number',
      '#title' => $this->t('Recent Instances'),
      '#description' => $this->t('Specify how many recent instances to display'),
      '#default_value' => $this->getSetting('past_display'),
    ];

    $form['upcoming_display'] = [
      '#type' => 'number',
      '#title' => $this->t('Upcoming Instances'),
      '#description' => $this->t('Specify how many upcoming instances to display'),
      '#default_value' => $this->getSetting('upcoming_display'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary[] = $this->getSetting('timezone_override') === ''
      ? $this->t('No timezone override.')
      : $this->t('Timezone overridden to %timezone.', [
        '%timezone' => $this->getSetting('timezone_override'),
      ]);

    $summary[] = $this->t('Smart date format: %format.', [
      '%format' => $this->getSetting('format'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    // TODO: intellident switching between retrieval methods
    // Look for a defined format and use it if specified.
    $format_label = $this->getSetting('format');
    if ($format_label) {
      $format = SmartDateFormat::load($format_label);
      $settings = $format->getOptions();
    }
    else {
      $settings = [
        'separator' => $this->getSetting('separator'),
        'join' => $this->getSetting('join'),
        'time_format' => $this->getSetting('time_format'),
        'time_hour_format' => $this->getSetting('time_hour_format'),
        'date_format' => $this->getSetting('date_format'),
        'date_first' => $this->getSetting('date_first'),
        'ampm_reduce' => $this->getSetting('ampm_reduce'),
        'allday_label' => $this->getSetting('allday_label'),
      ];
    }
    $rrules = [];
    foreach ($items as $delta => $item) {
      $timezone = $item->timezone ? $item->timezone : NULL;
      if (empty($item->value) || empty($item->end_value)) {
        continue;
      }
      if (empty($item->rrule)) {
        // No rule so include the item directly.
        $elements[$delta] = static::formatSmartDate($item->value, $item->end_value, $settings, $timezone);
      }
      else {
        // Uses a rule, so use a placeholder instead.
        if (!isset($rrules[$item->rrule])) {
          $elements[$delta] = $item->rrule;
          $rrules[$item->rrule]['delta'] = $delta;
        }
        // Add this instance to our array of instances for the rule.
        $rrules[$item->rrule]['instances'][] = $item;
      }
    }
    foreach ($rrules as $rrid => $rrule_collected) {
      $rrule_output = [
        '#theme' => 'smart_date_recurring_formatter',
      ];
      $instances = $rrule_collected['instances'];
      if (empty($instances)) {
        continue;
      }
      $delta = $rrule_collected['delta'];
      // Retrieve the text of the rrule.
      $rrule = SmartDateRule::load($rrid);
      $rrule_output['#rule_text'] = $rrule->getTextRule();

      // Find the 'next' instance after now.
      $next_index = $this->findNextInstance($instances);
      // Get the specified number of past instances.
      $past_display = $this->getSetting('past_display');
      // Display past instances if set and at least one instances in the past.
      if ($past_display && $next_index) {
        if ($next_index == -1) {
          $begin = count($instances) - $past_display;
        }
        else {
          $begin = $next_index - $past_display;
        }
        if ($begin < 0) {
          $begin = 0;
          $past_display = $next_index;
        }
        $past_instances = array_slice($instances, $begin, $past_display);
        $rrule_output['#past_display'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
        ];
        foreach ($past_instances as $item) {
          $output = static::formatSmartDate($item->value, $item->end_value, $settings, $item->timezone, 'string');
          $rrule_output['#past_display']['#items'][] = $output;
        }
      }
      $upcoming_display = $this->getSetting('upcoming_display');
      // Display upcoming instances if set and at least one instance upcoming.
      if ($upcoming_display && $next_index < count($instances) && $next_index != -1) {
        $upcoming_instances = array_slice($instances, $next_index, $upcoming_display);
        $rrule_output['#upcoming_display'] = [
          '#theme' => 'item_list',
          '#list_type' => 'ul',
        ];
        foreach ($upcoming_instances as $item) {
          $output = static::formatSmartDate($item->value, $item->end_value, $settings, $item->timezone, 'string');
          $rrule_output['#upcoming_display']['#items'][] = $output;
        }
      }

      $elements[$delta] = $rrule_output;
    }

    return $elements;
  }

  /**
   * Helper function to find the next instance from now in a provided range.
   */
  private function findNextInstance(array $instances) {
    $next_index = -1;
    $time = time();
    foreach ($instances as $index => $instance) {
      if ($instance->value > $time) {
        $next_index = $index;
        break;
      }
    }
    return $next_index;
  }

}
