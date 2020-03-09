<?php

use CRM_CiviAwards_Setup_CreateApplicantReviewActivityType as CreateApplicantReviewActivityType;

/**
 * Class CRM_CiviAwards_Helper_ApplicantReview.
 */
class CRM_CiviAwards_Helper_ApplicantReview {

  /**
   * Return the Activity Applicant review Id.
   *
   * @return int
   *   Activity Type Id.
   */
  public static function getActivityTypeId() {
    $result = civicrm_api3('OptionValue', 'get', [
      'return' => ['value'],
      'sequential' => 1,
      'option_group_id' => 'activity_type',
      'name' => CreateApplicantReviewActivityType::APPLICANT_REVIEW,
    ]);

    return $result['values'][0]['value'];
  }

}
