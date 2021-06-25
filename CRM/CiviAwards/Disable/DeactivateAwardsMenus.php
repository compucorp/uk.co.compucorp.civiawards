<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;

/**
 * Deactivates the menu items for Civi Awards.
 */
class CRM_CiviAwards_Disable_DeactivateAwardsMenus {

  /**
   * Deactivates the Awards menu items.
   */
  public function apply() {
    $caseCategoryDetails = array_column(CRM_Civicase_Helper_CaseCategory::getCaseCategories(), 'name', 'value');
    $applicantCaseCategoryIds = CRM_CiviAwards_Helper_CaseTypeCategory::getApplicantManagementCaseCategories();
    $applicationMenu = new ApplicantManagementMenu();
    foreach ($applicantCaseCategoryIds as $caseCategoryValue) {
      $applicationMenu->deactivateItem($caseCategoryDetails[$caseCategoryValue]);
    }
  }

}
