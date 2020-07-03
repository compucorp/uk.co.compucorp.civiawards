<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Creates the menu items for Civi Awards.
 */
class CRM_CiviAwards_Setup_CreateAwardsMenus {

  /**
   * Creates the Awards menu items.
   */
  public function apply() {
    $applicationMenu = new ApplicantManagementMenu();
    $applicationMenu->createItems(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
  }

}
