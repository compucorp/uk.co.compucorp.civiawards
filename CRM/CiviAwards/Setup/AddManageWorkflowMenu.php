<?php

use CRM_Civicase_Service_ManageWorkflowMenu as ManageWorkflowMenu;

/**
 * Adds the Manage Workflow Menu item for existing Case types.
 */
class CRM_CiviAwards_Setup_AddManageWorkflowMenu {

  /**
   * And Create Manage Workflow Menu for applicant management categories.
   */
  public function apply() {
    (new ManageWorkflowMenu())->create('applicant_management', TRUE);
  }

}
