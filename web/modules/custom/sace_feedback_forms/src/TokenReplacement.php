<?php

namespace Drupal\sace_feedback_forms;

class TokenReplacement
{
  public const TEXT_KEYS = ['#text', '#markup', '#title'];

  protected array $tokenValues = [];

  public function __construct(array $tokenValues)
  {
    $this->tokenValues = $tokenValues;
  }

  public static function run(array $tokenValues, array &$form)
  {
    $replacer = new self($tokenValues);
    $replacer->replace($form);
  }

  public function replace(array &$form)
  {
    foreach ($form as &$element) {
      if (!is_array($element)) {
        continue;
      }

      // replace any text fields at this level
      foreach (self::TEXT_KEYS as $key) {
        if (isset($element[$key])) {
          foreach ($this->tokenValues as $token => $value) {
            $element[$key] = str_replace($token, $value, $element[$key]);
          }
        }
      }

      // recurse to any sub arrays
      $this->replace($element);
    }
  }

}
