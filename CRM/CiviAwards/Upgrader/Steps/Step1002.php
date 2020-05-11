<?php

use CRM_CiviAwards_Setup_AddAwardCaseTypeCustomGroupSupport as AddAwardCaseTypeCustomGroupSupport;

/**
 * Upgrader that adds custom group support for Award Case Type.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1002 {

  /**
   * Adds Custom Group support for Award Case Type.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $step = new AddAwardCaseTypeCustomGroupSupport();
    $step->apply();

    return TRUE;
  }

}
