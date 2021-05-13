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
   * Award category label.
   */
  const AWARDS_CASE_TYPE_CATEGORY_LABEL = 'Awards';

  /**
   * Award category singular label.
   */
  const AWARDS_CASE_TYPE_CATEGORY_SINGULAR_LABEL = 'Award';

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
   * Returns the option values for Award Subtypes.
   *
   * @return array
   *   Award Subtypes.
   */
  public static function getSubTypes() {
    return AwardDetail::buildOptions('award_subtype');
  }

  /**
   * Get data for creating the menu.
   *
   * @return string[]
   *   Category data.
   */
  public static function getDataForMenu() {
    return [
      'name' => self::AWARDS_CASE_TYPE_CATEGORY_NAME,
      'label' => self::AWARDS_CASE_TYPE_CATEGORY_LABEL,
      'singular_label' => self::AWARDS_CASE_TYPE_CATEGORY_SINGULAR_LABEL,
    ];
  }

}
