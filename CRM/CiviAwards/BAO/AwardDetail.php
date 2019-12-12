<?php

use CRM_CiviAwards_Service_AwardDetail as AwardDetailService;

/**
 * Class CRM_CiviAwards_BAO_AwardDetail.
 */
class CRM_CiviAwards_BAO_AwardDetail extends CRM_CiviAwards_DAO_AwardDetail {

  /**
   * Create a new AwardDetail based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return CRM_CiviAwards_DAO_AwardDetail
   *   AwardDetail instance.
   */
  public static function create(array $params) {
    $entityName = 'AwardDetail';
    $hook = empty($params['id']) ? 'create' : 'edit';
    self::validateParams($params);
    // We don't want this to be updated via API.
    unset($params['profile_id']);
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
    $instance->runAfterSave($hook, $params);
    return $instance;
  }

  /**
   * Runs after save actions for the entity using the Award detail service.
   *
   * @param string $hook
   *   Hook Value.
   * @param array $params
   *   Parameters.
   */
  private function runAfterSave($hook, array $params) {
    $awardDetailService = new AwardDetailService();
    if ($hook == 'create') {
      $awardDetailService->postCreateActions($this, $params);
    }
    else {
      $awardDetailService->postUpdateActions($this, $params);
    }
  }

  /**
   * Validation function.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateParams(array &$params) {
    self::validateDates($params);
    self::validateReviewFields($params);
  }

  /**
   * Ensures that the start and end dates are validated.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateDates(array $params) {
    if (!empty($params['start_date']) && !empty($params['end_date'])) {
      $startDate = new DateTime($params['start_date']);
      $endDate = new DateTime($params['end_date']);

      if ($startDate > $endDate) {
        throw new Exception("Award Start Date must not be greater than the End Date");
      }
    }
  }


  /**
   * Validates review_field options to ensure legal options are passed.
   *
   * @param array $params
   *  Request Parameters
   *
   * @throws \Exception
   */
  private static function validateReviewFields(array &$params) {
    $reviewFieldsLegalOptions = [
      'id' => FILTER_VALIDATE_INT,
      'weight' => FILTER_VALIDATE_INT,
      'required' => FILTER_VALIDATE_BOOLEAN
    ];
    $reviewFields = CRM_Utils_Array::value('review_fields', $params);
    if (empty($reviewFields)) {
      return;
    }

    $filteredReviewFields = [];
    foreach ($reviewFields as $reviewFieldOptions) {
      $invalidReviewFieldOptions = [];
      $validReviewFieldOptions = array_filter(
        $reviewFieldOptions,
        function($value, $key) use ($reviewFieldsLegalOptions, &$invalidReviewFieldOptions) {
          if (!isset($reviewFieldsLegalOptions[$key])) {
            return FALSE;
          }

          if (filter_var($value, $reviewFieldsLegalOptions[$key], FILTER_NULL_ON_FAILURE) !== NULL) {
            return TRUE;
          }
          $invalidReviewFieldOptions[] = $key;
        },
        ARRAY_FILTER_USE_BOTH
      );
      if (count($validReviewFieldOptions) === count($reviewFieldsLegalOptions)) {
        continue;
      }

      $missingReviewFieldOptions = array_diff(
        array_keys($reviewFieldsLegalOptions),
        array_merge($invalidReviewFieldOptions, array_keys($validReviewFieldOptions))
      );
      $exceptionPhrases = [
        !empty($invalidReviewFieldOptions) ? "invalid values passed for [%1]" : "",
        !empty($missingReviewFieldOptions) ? "required field" . (
          count($missingReviewFieldOptions) > 1 ? "s [%2] are" : " [%2] is"
        ) . " missing" : ""
      ];

      throw new Exception(ts(implode(' and ', array_filter($exceptionPhrases)), [
        1 => implode(', ', $invalidReviewFieldOptions),
        2 => implode(', ', $missingReviewFieldOptions),
      ]));

      $filteredReviewFields[] = $validReviewFieldOptions;
    }

    $params['review_fields'] = $filteredReviewFields;
  }

}
