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
    $fieldKey = $fieldDetails['key'];
    if ($fieldKey === 'source_contact_id') {
      return new SubmittedByCounts('source_contact_id');
    }
    if ($fieldDetails['option_group_id']) {
      return new OptionQuestionSummary($fieldKey, $fieldDetails);
    }

    // for now we treat any non-option question as a text question
    return new TextQuestionSummary($fieldKey, $fieldDetails);
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
    $group = $this->sourceFieldDetails['custom_group_id.name'];
    if (strlen($group) > 16) {
      $group = \substr($group, 0, 16);
    }

    $name = $this->sourceFieldDetails['name'];

    if (strlen($name) > 16) {
      $name = \substr($name, 0, 16);
    }

    $hash = \substr(\md5($this->sourceField), 0, 6);

    return "sum_{$group}_{$name}_{$hash}";
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
   * Get fields needed to store the result of summarising this question.
   * @return array[]
   *   Keys should match those in getElements
   *   Values are params to pass to CustomField::create
   */
  abstract public function getStorageFields(): array;

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
  public static function getOrCreateStorageField(array $storageField): string {
    $key = $storageField['name'];
    $label = $storageField['label'];

    $existingField = \Civi\Api4\CustomField::get(FALSE)
      // should we restrict to a particular custom field group?
      // probably the key is specific enough
      ->addWhere('custom_group_id.name', '=', 'Feedback_Summary')
      ->addWhere('label', '=', $label)
      ->addSelect('name', 'custom_group_id.name', 'label')
      ->execute()
      ->first();

    if ($existingField) {
      if ($existingField['label'] !== $label) {
        \Civi::log()->debug("Existing summary field found for {$key} but label {$existingField['label']} does not match expected {$label}. We will use it anyway but you may want to update the label.");
      }
      return $existingField['custom_group_id.name'] . '.' . $existingField['name'];
    }

    $fieldCreate = \Civi\Api4\CustomField::create(FALSE)
      ->addValue('name', $key)
      ->addValue('custom_group_id:name', 'Feedback_Summary');

    foreach ($storageField as $prop => $value) {
      $fieldCreate->addValue($prop, $value);
    }

    $fieldCreate->execute();

    return 'Feedback_Summary.' . $key;
  }

}
