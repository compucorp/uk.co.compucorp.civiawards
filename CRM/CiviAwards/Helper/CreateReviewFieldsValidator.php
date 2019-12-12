<?php

/**
 * Class CRM_CiviAwards_Helper_CreateReviewFieldsValidator.
 */
class CRM_CiviAwards_Helper_CreateReviewFieldsValidator {

  /**
   * Array of accepted review_fields with their validation filters
   *
   * @var array
   */
  private $reviewFieldsLegalOptions = [
    'id' => FILTER_VALIDATE_INT,
    'weight' => FILTER_VALIDATE_INT,
    'required' => FILTER_VALIDATE_BOOLEAN
  ];

  /**
   * Array to hold invalid field options
   *
   * @var array
   */
  private $invalidReviewFieldOptions = [];

  /**
   * Array to hold unknown review field options passeed
   *
   * @var array
   */
  private $unknownReviewFieldOptions = [];

  /**
   * Validates review_field options to ensure legal options are passed.
   *
   * @param array $reviewFields
   *  Review fields
   *
   * @throws \Exception
   */
  public function validate(array $reviewFields) {
    if (empty($reviewFields)) {
      return;
    }

    foreach ($reviewFields as $reviewFieldOptions) {
      $validReviewFieldOptions = array_filter(
        $reviewFieldOptions, [$this, 'reviewFieldOptionsFilter'], ARRAY_FILTER_USE_BOTH
      );
      if (!empty($this->unknownReviewFieldOptions)) {
        throw new Exception(ts('Unknown review field options [%1] passed', [
          1 => implode(', ', $this->unknownReviewFieldOptions),
        ]));
      }

      if (count($validReviewFieldOptions) === count($this->reviewFieldsLegalOptions)) {
        continue;
      }

      $missingReviewFieldOptions = array_diff(
        array_keys($this->reviewFieldsLegalOptions),
        array_merge($this->invalidReviewFieldOptions, array_keys($validReviewFieldOptions))
      );
      $exceptionPhrase = $this->getReviewFieldValidationExceptionPhrase($missingReviewFieldOptions);

      throw new Exception(ts($exceptionPhrase, [
        1 => implode(', ', $this->invalidReviewFieldOptions),
        2 => implode(', ', $missingReviewFieldOptions),
      ]));
    }
  }

  /**
   * Callback to filter review options.
   *
   * @param string $value
   *  Value of review field option
   * @param string $key
   *  Review field option
   *
   * @return boolean
   *  Returns TRUE if option passes validation
   */
  private function reviewFieldOptionsFilter($value, $key) {
    if (!isset($this->reviewFieldsLegalOptions[$key])) {
      $this->unknownReviewFieldOptions[] = $key;

      return FALSE;
    }

    $options = (
      $this->reviewFieldsLegalOptions[$key] === FILTER_VALIDATE_BOOLEAN ? FILTER_NULL_ON_FAILURE : [
        'options' => ['default' => NULL],
      ]
    );
    if (filter_var($value, $this->reviewFieldsLegalOptions[$key], $options) !== NULL) {
      return TRUE;
    }
    $this->invalidReviewFieldOptions[] = $key;

    return FALSE;
  }

  /**
   * Constructs error message given the missing review field options
   *
   * @param array $missingReviewFieldOptions
   *  Missing review field options
   *
   * @return string
   *  Excption message
   */
  private function getReviewFieldValidationExceptionPhrase(array $missingReviewFieldOptions) {
    $exceptionPhrases = [
      !empty($this->invalidReviewFieldOptions) ? "invalid values passed for [%1]" : "",
      !empty($missingReviewFieldOptions) ? "required field" . (
        count($missingReviewFieldOptions) > 1 ? "s [%2] are" : " [%2] is"
      ) . " missing" : ""
    ];

    return ucfirst(implode(' and ', array_filter($exceptionPhrases)));
  }

}
