<?php

use CRM_Civicase_Service_CaseCategoryMenu as CaseCategoryMenu;

/**
 * Adds the Manage Workflow Menu item for existing Case types.
 */
class CRM_CiviAwards_Setup_AddManageWorkflowMenu {

  /**
   * And Create Manage Workflow Menu for applicant management categories.
   */
  public function apply() {
    CaseCategoryMenu::createManageWorkflowMenuForExistingCaseCategories('applicant_management', TRUE);
  }

}
