<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;

/**
 * Update menus with new URL.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1011 {

  /**
   * Runs the upgrader changes.
   *
   * @return bool
   *   Return value in boolean.
   */
  public function apply() {
    (new ApplicantManagementMenu())->resetCaseCategorySubmenusUrl(
      CRM_CiviAwards_Helper_CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME
    );

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

}
