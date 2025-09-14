<?php

namespace Drupal\sace_feedback_forms\FeedbackSummary;

class TextQuestionSummary extends QuestionSummary {

  /**
   * @inheritdoc
   */
  public function getStorageFields(): array {
    $prefix = $this->getPrefix();
    $sourceLabel = $this->sourceFieldDetails['label'];

    return [
      "{$prefix}_count" => [
        'name' => "{$prefix}_count",
        'label' => "{$sourceLabel} - Response Count",
        'data_type' => 'Int',
        'html_type' => 'Text',
      ],
      "{$prefix}_sentiment" => [
        'name' => "{$prefix}_sentiment",
        'label' => "{$sourceLabel} - Summary of Responses",
        'html_type' => 'Text',
        'data_type' => 'Memo',
      ],
      "{$prefix}_notable" => [
        'name' => "{$prefix}_notable",
        'label' => "{$sourceLabel} - Notable Responses",
        'html_type' => 'Textarea',
        'data_type' => 'Memo',
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function getElements(): array {
    $prefix = $this->getPrefix();

    return [
      "{$prefix}_list" => [
        '#type' => 'markup',
        '#markup' => '<div>[itemised responses]</div>',
      ],
      "{$prefix}_summary" => [
        "#type" => 'webform_flexbox',
        "{$prefix}_count" => [
          '#type' => 'number',
          '#title' => 'Number of responses',
          //'#default_value' => $responses['count'],
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
        ],
        "{$prefix}_sentiment" => [
          '#type' => 'textfield',
          '#title' => 'Overall sentiment',
          '#flex' => 1,
          '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
          '#suffix' => '</div></div>',
        ],
      ],
      "{$prefix}_notable" => [
        '#type' => 'textarea',
        '#title' => 'Notable responses',
      ],
    ];

    return $summaryFields;
  }

  public function prepopulateValues(array &$fieldset, array $feedbackRecords): void {
    $prefix = $this->getPrefix();
    $responses = array_filter(array_map(fn ($feedback) => $feedback[$this->sourceField], $feedbackRecords));

    if (!$responses) {
      $fieldset["{$prefix}_list"]['#markup'] = '<div>No responses</div>';
      $fieldset["{$prefix}_summary"]["{$prefix}_count"]['#default_value'] = 0;
      return;
    }

    $list = array_map(fn($r) => sprintf('<li>%s</li>', $r), $responses);
    $list = implode(' ', $list);
    $markup = <<<HTML
        <details class="form-wrapper">
          <summary>
            See responses
          </summary>
          <div>
            <ul>{$list}</ul>
          </div>
        </details>
      HTML;

    $fieldset["{$prefix}_list"]['#markup'] = $markup;
    $fieldset["{$prefix}_summary"]["{$prefix}_count"]['#default_value'] = count($responses);
  }

}