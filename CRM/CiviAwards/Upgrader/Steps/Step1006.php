<?php

use CRM_CiviAwards_Setup_AddManageWorkflowMenu as AddManageWorkflowMenu;

/**
 * Creates Manage Workflow menu for existing 'applicant management' categories.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1006 {

  /**
   * Adds Custom Group support for Applicant management Case Types.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $step = new AddManageWorkflowMenu();
    $step->apply();

    return TRUE;
  }

}
