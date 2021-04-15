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
    $applicationMenu->createItems([
      'name' => CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME,
      'label' => CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_LABEL,
      'singular_label' => CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_SINGULAR_LABEL,
    ]);
  }

}
