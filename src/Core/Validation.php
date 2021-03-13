<?php
declare(strict_types=1);

namespace App\Core;

use App\Helpers\StringHelper;
use Exception;

class Validation
{
  /**
   * @var array
   */
  protected $data = [];

  /**
   * @var array
   */
  protected $rules = [];

  /**
   * @var array
   */
  protected $errors = [];

  /**
   * Validation constructor.
   * 
   * @param array $data
   * @param array $rules
   */
  public function __construct(array $data, array $rules = [])
  {
    $this->data = $data;
    $this->rules = $rules;
  }

  /**
   * Gets the rules
   * 
   * @return array
   */
  public function getRules(): array
  {
    return $this->rules;
  }

  /**
   * Sets the rules
   * 
   * @param array $rules
   */
  public function setRules(array $rules)
  {
    $this->rules = $rules;
  }

  /**
   * Gets the errors
   * 
   * @return array
   */
  public function getErrors(): array
  {
    return $this->errors;
  }

  /**
   * Gets the data
   * 
   * @return array
   */
  public function getData(): array
  {
    return $this->data;
  }

  /**
   * Sets the data
   * 
   * @param array $data
   */
  public function setData(array $data)
  {
    $this->data = $data;
  }

  /**
   * Runs the validation
   * 
   * @return bool
   */
  public function runValidation(): bool
  {
    $this->errors = [];

    if (count($this->rules) == 0) {
      $this->errors[] = "No rules where specified.";
      return false;
    }

    return $this->runRules();
  }

  /**
   * Run the rules foreach field
   *
   * @return bool
   */
  private function runRules(): bool
  {
    foreach($this->rules as $field => $rule) {
      if (!isset($rule['rules'])) {
        $this->errors[] = "Rules for the field {$field} is not set.";
        return false;
      }
        
      $rules = explode('|', $rule['rules']);

      if (in_array('optional', $rules) && (!isset($this->data[$field]) || empty($this->data[$field])))
        continue;

      //if (in_array('htmlescape', $rules))
        //$this->data[$field] = htmlentities($this->data[$field]);

      if (in_array('trim', $rules))
        $this->data[$field] = trim(filter_var($this->data[$field], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));

      foreach($rules as $rule)
        if (!$this->runRule($field, $rule))
          return false;
    }

    return true;
  }

  /**
   * Run the rule
   * 
   * @return bool
   */
  private function runRule(string $field, string $rule): bool 
  {
    $label = isset($this->rules[$field]['label']) ? $this->rules[$field]['label'] : StringHelper::snakeCaseToPascalCase($field);
    $value = $this->data[$field];
    $message = $this->rules[$field]['message'][$rule] ?? '';

    switch($rule) {
      case 'required':
        return $this->required($value, $label, $message);
      case 'alpha':
        return $this->alpha($value, $label, $message);
      case 'alphanum':
        return $this->alphanum($value, $label, $message);
      case 'integer':
        return $this->integer($value, $label, $message);
      case 'decimal':
        return $this->decimal($value, $label, $message);
      case 'username':
        return $this->username($value, $label, $message);
      case 'special':
        return $this->special($value, $label, $message);
    }

    return true;
  }

  /**
   * Required Rule
   * Checks if the value is empty
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function required(string $value, string $label, string $message = ''): bool
  {
    if ($value !== '')
      return true;
      
    $this->errors[] = $message === '' ? "{$label} field is required." : str_replace('{{field}}', $label, $message);
    return false;
  }

  /**
   * Alpha Rule
   * Checks if the value contain characters aside from A-Z and space
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function alpha(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^[a-zA-Z ]+$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from A-Z and a space." : str_replace('{{field}}', $label, $message);
    return false;
  }

  /**
   * Alphanum Rule
   * Checks if the value contain characters aside from A-Z, 0-9, and space
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function alphanum(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^[a-zA-Z0-9 ]+$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from A-Z, 0-9, and a space." : str_replace('{{field}}', $label, $message);
    return false;
  }

  /**
   * Integer Rule
   * Checks if the value contain characters aside from 0-9
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function integer(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^[0-9]+$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from 0-9." : str_replace('{{field}}', $label, $message);
    return false;
  }

  /**
   * Decimal Rule
   * Checks if the value contain characters aside from 0-9 and period
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function decimal(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^(?!.*[.]{2})[0-9.]+(?<![.])$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from 0-9 and 1 decimal point." : str_replace('{{field}}', $label, $message);
    return false;
  }

  /**
   * Username Rules
   * Check if the value contain characters aside from A-Z, underscore, and number
   * 
   * @param string $value
   * @param string $label
   * @param string $message
   * @return bool
   */
  private function username(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^[a-zA-Z0-9_]+$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from A-Z, underscore, and number." : str_replace('{{field}}', $label, $message);
    return false;
  }

  private function special(string $value, string $label, string $message = ''): bool
  {
    $pattern = "/^[a-zA-Z0-9_\-.,\"'\/\(\)\[\] ]+$/";
    if (preg_match($pattern, $value))
      return true;

    $this->errors[] = $message === '' ? "{$label} field should only contain characters from A-Z, numbers, and characters { _-.,\"'/()[] }." : str_replace('{{field}}', $label, $message);
    return false;
  }
}