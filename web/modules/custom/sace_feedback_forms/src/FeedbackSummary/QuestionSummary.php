<?php

namespace Drupal\sace_feedback_forms\FeedbackSummary;

abstract class QuestionSummary {

  /**
   * CiviCRM field key for the feedback form question to be summarised
   * e.g. MyCustomGroup.MyCustomField
   */
  protected string $sourceField;

  /**
   * Details of the question on the feedback form to be summarised
   *
   * Expect a CiviCRM CustomField record
   */
  protected array $sourceFieldDetails;

  public static function createForField(array $fieldDetails): QuestionSummary {
    if ($fieldDetails['option_group_id']) {
      return new OptionQuestionSummary($fieldDetails['key'], $fieldDetails);
    }

    // for now we treat any non-option question as a text question
    return new TextQuestionSummary($fieldDetails['key'], $fieldDetails);
  }

  public function __construct(string $sourceField, array $sourceFieldDetails = []) {
    $this->sourceField = $sourceField;

    // load source field details using Api if not provided
    if (!$sourceFieldDetails) {
      [$group, $field] = explode('.', $this->sourceField);
      $sourceFieldDetails = \Civi\Api4\CustomField::get(FALSE)
        ->addWhere('custom_group_id.name', '=', $group)
        ->addWhere('name', '=', $field)
        ->execute()
        ->single();
    }

    $this->sourceFieldDetails = $sourceFieldDetails;
  }

  public function getPrefix(): string {
    $prefix = 'summary_' . str_replace('.', '_', $this->sourceField);

    if (strlen($prefix) > 32) {
      $prefix = \substr($prefix, 0, 24) . \substr(\md5($prefix), 0, 8);
    }

    return $prefix;
  }

  /**
   * Get the elements to include on the summary form for this question
   */
  public function getFieldset(): array {
    return [
      "#type" => "fieldset",
      "{$this->getPrefix()}_title" => [
        '#type' => 'markup',
        '#markup' => "<strong>{$this->sourceFieldDetails['label']}</strong>",
      ],
      ...$this->getElements(),
    ];
  }

  /**
   * Get the elements to include on the summary form for this question
   */
  abstract public function getElements(): array;

  /**
   * Preopulate summary values based on set of feedback activity records
   */
  abstract public function prepopulateValues(array &$fieldset, array $feedbackRecords): void;

  public function getPrepopulatedFieldset(array $feedbackRecords): array {
    $fieldset = $this->getFieldset();

    $this->prepopulateValues($fieldset, $feedbackRecords);

    return $fieldset;
  }

  /**
   * This allow get or create a CustomField in CiviCRM to store values
   * for one of the generated summary fields
   *
   * @return string CiviCRM field key on Activity record
   */
  public static function getOrCreateSummaryDataField(string $summaryFieldKey): string {
    $summaryFieldKey = str_replace('summary_', '', $summaryFieldKey);

    $label = \str_replace('_', ' ', $summaryFieldKey);

    if (\strlen($summaryFieldKey) >= 64) {
      $parts = explode('_option_', $summaryFieldKey);
      $partLength = 64 / count($parts);
      foreach ($parts as $part) {
        \substr($summaryFieldKey, 0, 54) . \substr(md5($summaryFieldKey), 10);

      }

    }
    // TODO: might summary field key be too long for custom field name column?
    $existingField = \Civi\Api4\CustomField::get(FALSE)
      // should we restrict to a particular custom field group?
      // probably the key is specific enough
      //->addWhere('custom_group_id.name', '=', 'Feedback_Summary')
      ->addWhere('name', '=', $summaryFieldKey)
      ->addSelect('name', 'custom_group_id.name')
      ->execute()
      ->first();

    if ($existingField) {
      return $existingField['custom_group_id.name'] . '.' . $existingField['name'];
    }

    \Civi\Api4\CustomField::create(FALSE)
      ->addValue('label', $label)
      ->addValue('name', $summaryFieldKey)
      ->addValue('custom_group_id.name', 'Feedback_Summary')
      ->addValue('html_type', 'Text')
      ->execute()
      ->first();

    return 'Feedback_Summary.' . $summaryFieldKey;
  }

}
