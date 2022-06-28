<?php

namespace Drupal\smart_date\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldFilteredMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeWidgetBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\smart_date\Plugin\Field\FieldType\SmartDateListItemBase;
use Drupal\smart_date_recur\Entity\SmartDateRule;

/**
 * Base class for the 'smartdate_*' widgets.
 */
class SmartDateWidgetBase extends DateTimeWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $field_def = $this->fieldDefinition;
    if ($field_def instanceof FieldConfigInterface) {
      $allow_recurring = $field_def->getThirdPartySetting('smart_date_recur', 'allow_recurring');
    }
    elseif ($field_def instanceof BaseFieldDefinition) {
      // TODO: Document that for custom entities, you must enable recurring
      // functionality by adding ->setSetting('allow_recurring', TRUE)
      // to your field definition.
      $allow_recurring = $field_def->getSetting('allow_recurring');
    }
    else {
      // Not sure what other method we can provide to define this.
      $allow_recurring = FALSE;
    }

    // TODO: more elegant way to handle hiding recurring instances?
    if ($allow_recurring && $items[$delta]->rrule) {
      $rrule = SmartDateRule::load($items[$delta]->rrule);
      if ($rrule && isset($form['#rules_processed'][$items[$delta]->rrule])) {
        // Not the first instance, so skip this delta.
        $element['#access'] = FALSE;
        return $element;
      }
      else {
        // Keep track of this rule as having been processed.
        $form['#rules_processed'][$items[$delta]->rrule] = $items[$delta]->rrule;
        $items[$delta]->value = (int) $rrule->start->getString();
        $items[$delta]->end_value = (int) $rrule->end->getString();
        $items[$delta]->duration = ($items[$delta]->end_value - $items[$delta]->value) / 60;
      }
    }
    $form['#attached']['library'][] = 'smart_date/smart_date';

    $defaults = $this->fieldDefinition->getDefaultValueLiteral()[0];

    $timezone = NULL;
    if (!empty($items[$delta]->timezone)) {
      $timezone = new \DateTimezone($items[$delta]->timezone);
    }
    $temp_tz = date_default_timezone_get();
    $values['start'] = isset($items[$delta]->value) ? DrupalDateTime::createFromTimestamp($items[$delta]->value, $timezone) : '';
    $values['end'] = isset($items[$delta]->end_value) ? DrupalDateTime::createFromTimestamp($items[$delta]->end_value, $timezone) : '';
    $values['duration'] = isset($items[$delta]->duration) ? $items[$delta]->duration : $defaults['default_duration'];
    $values['timezone'] = isset($items[$delta]->timezone) ? $items[$delta]->timezone : '';

    $this->createWidget($element, $values, $defaults);

    if ($allow_recurring && function_exists('smart_date_recur_widget_extra_fields')) {
      smart_date_recur_widget_extra_fields($element, $items[$delta], $this->getSetting('modal'));
    }

    return $element;
  }

  /**
   * Helper method to create SmartDate element.
   */
  public static function createWidget(&$element, $values, ?array $defaults) {
    // If an empty set of defaults provided, create our own.
    if (empty($defaults)) {
      $defaults = [
        'default_duration_increments' => "30\n60|1 hour\n90\n120|2 hours\ncustom",
        'default_duration' => '60',
      ];
    }
    // Wrap all of the select elements with a fieldset.
    $element['#theme_wrappers'][] = 'fieldset';

    $element['#element_validate'][] = [static::class, 'validateStartEnd'];
    $element['value']['#title'] = t('Start');
    $element['value']['#date_year_range'] = '1902:2037';
    // Ensure values always display relative to the site.
    $element['value']['#default_value'] = self::remapDatetime($values['start']);

    $element['end_value'] = [
      '#title' => t('End'),
      // Ensure values always display relative to the site.
      '#default_value' => self::remapDatetime($values['end']),
    ] + $element['value'];
    $element['value']['#attributes']['class'] = ['time-start'];
    $element['end_value']['#attributes']['class'] = ['time-end'];

    // Parse the allowed duration increments and create labels if not provided.
    $increments = SmartDateListItemBase::parseValues($defaults['default_duration_increments']);
    foreach ($increments as $key => $label) {
      if (strcmp($key, $label) !== 0) {
        // Label provided, so no extra logic required.
        continue;
      }
      if (is_numeric($key)) {
        // Anything but whole minutes will create errors with the time field.
        $num = (int) $key;
        $increments[$key] = t('@count minutes', ['@count' => $num]);
      }
      elseif ($key == 'custom') {
        $increments[$key] = t('Custom');
      }
      else {
        // Note sure what else we would encounter, so escape it.
        $increments[$key] = t('@key (unrecognized format)', ['@key' => $key]);
      }
    }
    $default_duration = $values['duration'];
    if (!array_key_exists($default_duration, $increments)) {
      if (array_key_exists('custom', $increments)) {
        $default_duration = 'custom';
      }
      else {
        // TODO: throw some kind of error/warning if invalid duration?
        $default_duration = '';
      }
    }
    $element['duration'] = [
      '#title' => t('Duration'),
      '#type' => 'select',
      '#options' => $increments,
      '#default_value' => $default_duration,
      '#attributes' => [
        'data-default' => $defaults['default_duration'],
        'class' => ['field-duration'],
      ],
    ];

    // No true input, so preserve an existing value otherwise use site default.
    $default_tz = (isset($values['timezone'])) ? $values['timezone'] : NULL;
    $element['timezone'] = [
      '#type' => 'hidden',
      '#title' => t('Time zone'),
      '#default_value' => $default_tz,
    ];

  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    // The widget form element type has transformed the value to a
    // DrupalDateTime object at this point. We need to convert it back to the
    // storage timestamp.
    foreach ($values as &$item) {
      $timezone = NULL;
      if (!empty($item['timezone'])) {
        $timezone = new \DateTimezone($item['timezone']);
      }
      if (!empty($item['value']) && $item['value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $start_time */
        $start_time = $item['value'];
        // Adjust the date for storage.
        $item['value'] = $this->smartGetTimestamp($item['value'], $timezone);
      }

      if (!empty($item['end_value']) && $item['end_value'] instanceof DrupalDateTime) {
        /** @var \Drupal\Core\Datetime\DrupalDateTime $end_time */
        $end_time = $item['end_value'];
        // Adjust the date for storage.
        $item['end_value'] = $this->smartGetTimestamp($item['end_value'], $timezone);
      }
      if ($item['duration'] == 'custom') {
        // If using a custom duration, calculate based on start and end times.
        if (isset($start_time) && isset($end_time) && $start_time instanceof DrupalDateTime && $end_time instanceof DrupalDateTime) {
          $item['duration'] = (int) ($item['end_value'] - $item['value']) / 60;
        }
      }
    }

    if (!$form_state->isValidationComplete()) {
      // Make sure we only process once, after validation.
      return $values;
    }

    // Skip any additional processing if the field doesn't allow recurring.
    $field_def = $this->fieldDefinition;
    if ($field_def instanceof FieldConfigInterface) {
      $allow_recurring = $field_def->getThirdPartySetting('smart_date_recur', 'allow_recurring');
    }
    elseif ($field_def instanceof BaseFieldDefinition) {
      // TODO: Document that for custom entities, you must enable recurring
      // functionality by adding ->setSetting('allow_recurring', TRUE)
      // to your field definition.
      $allow_recurring = $field_def->getSetting('allow_recurring');
    }
    else {
      // Not sure what other method we can provide to define this.
      $allow_recurring = FALSE;
    }

    if ($allow_recurring && function_exists('smart_date_recur_widget_extra_fields')) {
      // Provide extra parameters to be stored with the recurrence rule.
      $month_limit = $field_def
        ->getThirdPartySetting('smart_date_recur', 'month_limit');
      if ($form_state->getFormObject() instanceof EntityFormInterface) {
        $entity = $form_state->getformObject()->getEntity();
        $entity_type = $entity->getEntityTypeId();
        $bundle = $entity->bundle();
      }
      $field_name = $field_def->getName();
      smart_date_recur_generate_rows($values, $entity_type, $bundle, $field_name, $month_limit);
    }

    return $values;
  }

  /**
   * Conditionally convert a DrupalDateTime object to a timestamp.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $time
   *   The time to be converted.
   * @param DateTimezone|null $timezone
   *   An optional timezone to use for conversion.
   */
  private function smartGetTimestamp(DrupalDateTime $time, $timezone = NULL) {
    // Map the date to be relative to a provided timezone, if supplied.
    if ($timezone) {
      $time = $this->remapDatetime($time, $timezone);
    }
    return $time->getTimestamp();
  }

  /**
   * Conditionally convert a DrupalDateTime object to a timestamp.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime|null $time
   *   The time to be converted.
   * @param DateTimezone|null $timezone
   *   An optional timezone to use for conversion.
   */
  public static function remapDatetime($time, $timezone = NULL) {
    if (empty($time)) {
      return '';
    }
    $time = new DrupalDateTime($time->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT), $timezone);
    return $time;
  }

  /**
   * Ensure that the start date <= the end date via #element_validate callback.
   *
   * @param array $element
   *   An associative array containing the properties and children of the
   *   generic form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   */
  public static function validateStartEnd(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $start_time = $element['value']['#value']['object'];
    $end_time = $element['end_value']['#value']['object'];

    if ($start_time instanceof DrupalDateTime && $end_time instanceof DrupalDateTime) {
      if ($start_time->getTimestamp() !== $end_time->getTimestamp()) {
        $interval = $start_time->diff($end_time);
        if ($interval->invert === 1) {
          $form_state->setError($element, t('The @title end date cannot be before the start date', ['@title' => $element['#title']]));
        }
      }
    }
  }

  /**
   * Special handling to create form elements for multiple values.
   *
   * Handles generic features for multiple fields:
   * - number of widgets
   * - AHAH-'add more' button
   * - table display and drag-n-drop value reordering.
   */
  protected function formMultipleElements(FieldItemListInterface $items, array &$form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition
      ->getName();
    $cardinality = $this->fieldDefinition
      ->getFieldStorageDefinition()
      ->getCardinality();
    $parents = $form['#parents'];

    // Determine the number of widgets to display.
    switch ($cardinality) {
      case FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED:
        $field_state = static::getWidgetState($parents, $field_name, $form_state);
        $max = $field_state['items_count'];
        $is_multiple = TRUE;
        break;

      default:
        $max = $cardinality - 1;
        $is_multiple = $cardinality > 1;
        break;

    }
    $title = $this->fieldDefinition
      ->getLabel();
    $description = FieldFilteredMarkup::create(\Drupal::token()
      ->replace($this->fieldDefinition
        ->getDescription()));
    $elements = [];
    for ($delta = 0; $delta <= $max; $delta++) {

      // Add a new empty item if it doesn't exist yet at this delta.
      if (!isset($items[$delta])) {
        $items
          ->appendItem();
      }

      // For multiple fields, title and description are handled by the wrapping
      // table.
      if ($is_multiple) {
        $element = [
          '#title' => $this
            ->t('@title (value @number)', [
              '@title' => $title,
              '@number' => $delta + 1,
            ]),
          '#title_display' => 'invisible',
          '#description' => '',
        ];
      }
      else {
        $element = [
          '#title' => $title,
          '#title_display' => 'before',
          '#description' => $description,
        ];
      }
      $element = $this
        ->formSingleElement($items, $delta, $element, $form, $form_state);
      if ($element && (!isset($element['#access']) || $element['#access'] !== FALSE)) {

        // Input field for the delta (drag-n-drop reordering).
        if ($is_multiple) {

          // We name the element '_weight' to avoid clashing with elements
          // defined by widget.
          $element['_weight'] = [
            '#type' => 'weight',
            '#title' => $this
              ->t('Weight for row @number', [
                '@number' => $delta + 1,
              ]),
            '#title_display' => 'invisible',
            // Note: this 'delta' is the FAPI #type 'weight' element's property.
            '#delta' => $max,
            '#default_value' => $items[$delta]->_weight ?: $delta,
            '#weight' => 100,
          ];
        }
        $elements[$delta] = $element;
      }
    }
    if ($elements) {
      $elements += [
        '#theme' => 'field_multiple_value_form',
        '#field_name' => $field_name,
        '#cardinality' => $cardinality,
        '#cardinality_multiple' => $this->fieldDefinition
          ->getFieldStorageDefinition()
          ->isMultiple(),
        '#required' => $this->fieldDefinition
          ->isRequired(),
        '#title' => $title,
        '#description' => $description,
        '#max_delta' => $max,
      ];

      // Add 'add more' button, if not working with a programmed form.
      if ($cardinality == FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED && !$form_state
        ->isProgrammed()) {
        $id_prefix = implode('-', array_merge($parents, [
          $field_name,
        ]));
        $wrapper_id = Html::getUniqueId($id_prefix . '-add-more-wrapper');
        $elements['#prefix'] = '<div id="' . $wrapper_id . '">';
        $elements['#suffix'] = '</div>';
        $elements['add_more'] = [
          '#type' => 'submit',
          '#name' => strtr($id_prefix, '-', '_') . '_add_more',
          '#value' => t('Add another item'),
          '#attributes' => [
            'class' => [
              'field-add-more-submit',
            ],
          ],
          '#limit_validation_errors' => [
            array_merge($parents, [
              $field_name,
            ]),
          ],
          '#submit' => [
            [
              get_class($this),
              'addMoreSubmit',
            ],
          ],
          '#ajax' => [
            'callback' => [
              get_class($this),
              'addMoreAjax',
            ],
            'wrapper' => $wrapper_id,
            'effect' => 'fade',
          ],
        ];
      }
    }
    return $elements;
  }

}
