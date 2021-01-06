<?php

use CRM_CiviAwards_Setup_CreateApplicantReviewActivityType as CreateApplicantReviewActivityType;

/**
 * Helper class CRM_CiviAwards_Helper_ApplicantReview.
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

  /**
   * Return the Custom Group ID attached to the Applicant review activity type.
   */
  public static function getApplicantReviewCustomGroupId() {
    $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
      'extends_entity_column_value' => self::getActivityTypeId(),
    ]);

    return $customGroup['id'];
  }

}
