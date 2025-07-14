<?php

namespace Drupal\sace_feedback_forms\FeedbackSummary;

class OptionQuestionSummary extends QuestionSummary {

  protected array $options;

  protected function getOptionElementKey(string $optionName, string $prefix = ''): string {
    if (!$prefix) {
      $prefix = $this->getPrefix();
    }
    $elementKey = "{$prefix}_opt_{$optionName}";
    if (\strlen($elementKey) > 64) {
      $elementKey = \substr($elementKey, 0, 56) . \substr(\md5($elementKey), 0, 8);
    }
    return $elementKey;
  }

  /**
   * @inheritdoc
   */
  public function getElements(): array {
    $optionsFlexbox = [
      '#type' => 'webform_flexbox',
    ];

    $this->options = (array) \Civi\Api4\OptionValue::get(FALSE)
      ->addWhere('option_group_id', '=', $this->sourceFieldDetails['option_group_id'])
      // TODO should this be included
      // ->addWhere('is_active', '=', TRUE)
      ->execute();

    $prefix = $this->getPrefix();

    foreach ($this->options as &$option) {
      $option['summary_field_key'] = $this->getOptionElementKey($option['name'], $prefix);
      $optionsFlexbox[$option['summary_field_key']] = [
        '#type' => 'number',
        '#title' => "# {$option['label']}",
        '#title_display' => 'invisible',
        '#description' => "{$option['label']}",
        '#flex' => 1,
        '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
        '#suffix' => '</div></div>',
      ];
    }

    // see summary_form.js for how this values is display live client side
    $optionsFlexbox["{$prefix}_total_all_options"] = [
      '#type' => 'markup',
      '#markup' => "
        <div class='form-item sace-feedback-forms-option-summary-total'>
          <strong class='form-item__label'>Total</strong>
          <span class='sace-feedback-forms-option-summary-total__value'>[total]</span>
        </div>
      ",
      '#flex' => 1,
      '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
      '#suffix' => '</div></div>',
    ];

    return [
      "{$prefix}_options" => $optionsFlexbox,
    ];
  }

  public function prepopulateValues(array &$fieldset, array $feedbackRecords): void {
    foreach ($this->options as $option) {
      // count how many of the feedback records have this option value
      // set for the source field
      $count = count(array_filter($feedbackRecords, fn ($record) => ($record[$this->sourceField] === $option['value'])));

      $fieldset["{$this->getPrefix()}_options"][$option['summary_field_key']]['#default_value'] = $count;
    }
  }

}
