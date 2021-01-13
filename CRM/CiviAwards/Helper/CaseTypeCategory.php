<?php

use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Helper class with useful functions for managing case type categories.
 */
class CRM_CiviAwards_Helper_CaseTypeCategory {

  /**
   * Award category name.
   */
  const AWARDS_CASE_TYPE_CATEGORY_NAME = 'awards';

  /**
   * Applicant management instance name.
   */
  const APPLICATION_MANAGEMENT_NAME = 'applicant_management';

  /**
   * Returns the case types for the applicant management instance.
   *
   * @return array
   *   Array of Case Types indexed by Id.
   */
  public static function getApplicantManagementCaseTypes() {
    $applicantManagementCategories = self::getApplicantManagementCaseCategories();
    if (empty($applicantManagementCategories)) {
      return [];
    }

    $result = civicrm_api3('CaseType', 'get', [
      'return' => ['title', 'id'],
      'case_type_category' => ['IN' => $applicantManagementCategories],
      'options' => ['limit' => 0],
    ]);

    return array_column($result['values'], 'title', 'id');
  }

  /**
   * Returns the case categories for the applicant management instance.
   */
  public static function getApplicantManagementCaseCategories() {
    $instance = civicrm_api3('CaseCategoryInstance', 'get', [
      'instance_id' => self::APPLICATION_MANAGEMENT_NAME,
      'options' => ['limit' => 0],
    ]);

    if (empty($instance['values'])) {
      return [];
    }

    return array_column($instance['values'], 'category_id');
  }

  /**
   * Returns the option values for Award Subtypes that has been used.
   *
   * @return array
   *   Award Subtypes.
   */
  public static function getSubTypes() {
    $usedSubTypes = civicrm_api3('AwardDetail', 'get', [
      'return' => ['award_subtype'],
      'options' => ['limit' => 0],
    ]);
    if (empty($usedSubTypes['values'])) {
      return [];
    }
    $usedSubTypes = array_unique(array_column($usedSubTypes['values'], 'award_subtype'));

    $allSubTypes = AwardDetail::buildOptions('award_subtype');
    $allSubTypes = array_filter($allSubTypes, function ($key) use ($usedSubTypes) {
      return in_array($key, $usedSubTypes);
    }, ARRAY_FILTER_USE_KEY);

    return $allSubTypes;
  }

}
