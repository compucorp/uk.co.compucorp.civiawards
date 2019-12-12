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
      $validReviewFieldOptions = array_filter($reviewFieldOptions, [
        $this,
        'reviewFieldOptionsFilter',
      ], ARRAY_FILTER_USE_BOTH);
      if (count($validReviewFieldOptions) === count($this->reviewFieldsLegalOptions)) {
        continue;
      }

      $missingReviewFieldOptions = array_diff(
        array_keys($this->reviewFieldsLegalOptions),
        array_merge($this->invalidReviewFieldOptions, array_keys($validReviewFieldOptions))
      );
      $exceptionPhrase = $this->getReviewFieldOptionsExceptionPhrase($missingReviewFieldOptions);

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
   */
  private function reviewFieldOptionsFilter(string $value, string $key) {
    
    if (!isset($this->reviewFieldsLegalOptions[$key])) {
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
   */
  private function getReviewFieldOptionsExceptionPhrase(array $missingReviewFieldOptions) {
    $exceptionPhrases = [
      !empty($this->invalidReviewFieldOptions) ? "invalid values passed for [%1]" : "",
      !empty($missingReviewFieldOptions) ? "required field" . (
        count($missingReviewFieldOptions) > 1 ? "s [%2] are" : " [%2] is"
      ) . " missing" : ""
    ];

    return ucfirst(implode(' and ', array_filter($exceptionPhrases)));
  }

}
