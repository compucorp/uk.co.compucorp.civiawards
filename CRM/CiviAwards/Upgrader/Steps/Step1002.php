<?php

use CRM_CiviAwards_Setup_CreateApplicantManagementOption as CreateApplicantManagementOption;

/**
 * Creates the Applicant management option value.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1002 {

  /**
   * Creates the Applicant management option value.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    $step = new CreateApplicantManagementOption();
    $step->apply();

    return TRUE;
  }

}
