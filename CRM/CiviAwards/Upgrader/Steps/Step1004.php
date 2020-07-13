<?php

use CRM_CiviAwards_Setup_AddApplicantManagementCaseTypeCustomGroupSupport as AddApplicantManagementCaseTypeCustomGroupSupport;

/**
 * Upgrader that adds custom group support for Applicant management Case Types.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1004 {

  /**
   * Adds Custom Group support for Applicant management Case Types.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $step = new AddApplicantManagementCaseTypeCustomGroupSupport();
    $step->apply();

    return TRUE;
  }

}
