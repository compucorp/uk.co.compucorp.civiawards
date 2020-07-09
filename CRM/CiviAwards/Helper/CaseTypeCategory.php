<?php

/**
 * Class CRM_CiviAwards_Helper_CaseTypeCategory.
 */
class CRM_CiviAwards_Helper_CaseTypeCategory {

  const AWARDS_CASE_TYPE_CATEGORY_NAME = 'awards';
  const APPLICATION_MANAGEMENT_NAME = 'applicant_management';

  /**
   * Returns the case types for the applicant management instance.
   *
   * @return array
   *   Array of Case Types indexed by Id.
   */
  public static function getApplicantManagementCaseTypes() {
    $instance = civicrm_api3('CaseCategoryInstance', 'get', [
      'instance_id' => self::APPLICATION_MANAGEMENT_NAME,
    ]);

    if (empty($instance['values'])) {
      return [];
    }

    $applicantManagementCategories = array_column($instance['values'], 'category_id');
    $result = civicrm_api3('CaseType', 'get', [
      'return' => ['title', 'id'],
      'case_type_category' => ['IN' => $applicantManagementCategories],
    ]);

    return array_column($result['values'], 'title', 'id');
  }

}
