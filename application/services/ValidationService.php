

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ValidationService
{
  // protected $CI;

  public function __construct()
  {
    // $this->CI = &get_instance();
    // $this->CI->load->database();
  }

  private function validateDataType($value, $dataType)
  {
    switch ($dataType) {
      case 'integer':
        return is_int($value);
      case 'string':
        return is_string($value);
      case 'array':
        return is_array($value);
      default:
        return true;
    }
  }

  private function isOptionalEmpty($options)
  {
    return isset($options['optional_empty']) && $options['optional_empty'];
  }

  // private function validateDataRecursion($data, $requiredKeys, $errors, $parentKey = null)
  // {
  //   foreach ($data as $index => $item) {

  //     if (!is_array($item)) {
  //     } else {
  //       foreach ($requiredKeys as $key => $options) {
  //         $currentKey = $parentKey ? "$parentKey.$key" : $key;

  //         if (!array_key_exists($key, $item)) {
  //           if (!$options['optional_empty']) {
  //             $errors[] = "$currentKey at index $index is required. 123";
  //           }
  //           continue;
  //         }

  //         $value = $item[$key];
  //         $dataType = $options['data_type'] ?? null;

  //         if (!$this->isOptionalEmpty($options) && empty($value)) {
  //           $errors[] = "$currentKey at index $index is required but is empty.";
  //         } elseif ($dataType && !$this->validateDataType($value, $dataType)) {
  //           $errors[] = "$currentKey at index $index must be a valid $dataType.";
  //         }

  //         if (isset($options['min_length']) && is_array($value)) {
  //           $minLength = $options['min_length'];
  //           if (count($value) < $minLength) {
  //             $errors[] = "$currentKey at index $index must have at least $minLength items.";
  //           }
  //         }

  //         if (isset($options['max_length']) && is_array($value)) {
  //           $maxLength = $options['max_length'];
  //           if (count($value) > $maxLength) {
  //             $errors[] = "$currentKey at index $index must have at most $maxLength items.";
  //           }

  //           if (isset($options['data']) && is_array($value)) {
  //             $errors = $this->validateDataRecursion($value, $options['data'], $errors, "$currentKey.$index");
  //           }
  //         }
  //       }
  //     }
  //   }

  //   return $errors;
  // }

  private function validateDataRecursion($data, $requiredKeys, $errors, $parentKey = null)
  {
    $exempted = [];

    foreach ($data as $index => $item) {
      foreach ($requiredKeys as $key => $options) {
        $currentKey = $parentKey ? "$parentKey.$key" : $key;

        if (is_array($item)) {
          if (!array_key_exists($key, $item)) {
            if (!$options['optional_empty']) {
              $errors[] = "$currentKey at index $index is required. 123";
            }
            continue;
          }

          $value = $item[$key];
          $dataType = $options['data_type'] ?? null;

          if (!$this->isOptionalEmpty($options) && empty($value)) {
            $errors[] = "$currentKey at index $index is required but is empty.";
          } elseif ($dataType && !$this->validateDataType($value, $dataType)) {
            $errors[] = "$currentKey at index $index must be a valid $dataType.";
          }

          if (isset($options['min_length']) && is_array($value)) {
            $minLength = $options['min_length'];
            if (count($value) < $minLength) {
              $errors[] = "$currentKey at index $index must have at least $minLength items.";
            }
          }

          if (isset($options['max_length']) && is_array($value)) {
            $maxLength = $options['max_length'];
            if (count($value) > $maxLength) {
              $errors[] = "$currentKey at index $index must have at most $maxLength items.";
            }

            if (isset($options['data']) && is_array($value)) {
              $errors = $this->validateDataRecursion($value, $options['data'], $errors, "$currentKey.$index");
            }
          }
        }

        $exempted[] = $index;
      }
    }

    return $errors;
  }

  public function validate_data($data, $requiredKeys)
  {
    $errors = [];

    if (!isset($data) || !isset($data['data']) || !is_array($data['data'])) {
      $errors[] = 'data must be set.';
    } else {
      $errors = $this->validateDataRecursion($data['data'], $requiredKeys, $errors);
    }

    return $errors;
  }

  // function validate_data($data, $criteria) {
  //   $errors = [];
  //   foreach ($criteria as $key => $rule) {
  //       if (!array_key_exists($key, $data)) {
  //           if (!$rule['optional_empty']) {
  //               return "Required key '$key' is missing.";
  //           }
  //       } else {
  //           $value = $data[$key];
  //           if (gettype($value) !== $rule['data_type']) {
  //               return "Key '$key' has an invalid data type.";
  //           }

  //           if (is_array($value) && isset($rule['data'])) {
  //               $error = $this->validate_data($value, $rule['data']);
  //               if ($error) {
  //                   return $error;
  //               }
  //           }
  //       }
  //   }

  //   return null;
  // }

  // doesn't cater array and nested array
  public function simple_validate_data($data, $required_keys)
  {
    $errors = [];

    // check data (required set)
    if (!isset($data) || !isset($data['data'])) {
      $errors[] = 'data must be set';
    } else {
      $data = $data['data'];

      foreach ($required_keys as $key => $rules) {
        // check optional_empty (optional set)
        if ((!isset($data[$key]) || $data[$key] == '') && !$rules['optional_empty']) {
          $errors[] = "$key must be set";
        } elseif (isset($data[$key])) {
          $value = $data[$key];
          $data_type = gettype($value);

          // check data_type (required set)
          if (!isset($rules['data_type'])) {
            $errors[] = "$key's data_type must be set";
          } else {
            if ($rules['data_type'] != $data_type) {
              $errors[] = "$key's data_type must be {$rules['data_type']}, $data_type given";
            }

            switch ($rules['data_type']) {
              case 'string':
                // check min_length (optional set)
                if (isset($rules['min_length'])) {
                  if (strlen($value) < $rules['min_length']) {
                    $errors[] = "$key's characters must be greater or equal to {$rules['min_length']}";
                  }
                }

                // check max_length (optional set)
                if (isset($rules['max_length'])) {
                  if (strlen($value) > $rules['max_length']) {
                    $errors[] = "$key's characters must be less than or equal to {$rules['max_length']}";
                  }
                }

                // check if date
                if (isset($rules['date_format'])) {
                  $date_format = $rules['date_format'];
                  $date = DateTime::createFromFormat($date_format, $value);

                  if (!$date || $date->format($date_format) !== $value) {
                    $errors[] = "$key's date format must be $date_format";
                  }
                }
                break;
              case 'integer':
                // check min_count (optional set)
                if (isset($rules['min_count'])) {
                  if ($value < $rules['min_count']) {
                    $errors[] = "$key's count must be greater or equal to {$rules['min_count']}";
                  }
                }

                // check max_count (optional set)
                if (isset($rules['max_count'])) {
                  if ($value > $rules['max_count']) {
                    $errors[] = "$key's count must be less than or equal to {$rules['max_count']}";
                  }
                }
                break;
              case 'boolean':
                if (!is_bool(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
                  $errors[] = "$key must be a boolean value (true or false)";
                }
                break;
            }
          }
        }
      }
    }

    return $errors;
  }
}
