<?php

use CRM_CiviAwards_Setup_RenameAwardTypeField as RenameAwardTypeField;

/**
 * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1007 {

  /**
   * Renames the 'Award Type' field to 'Award Subtype'`.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $step = new RenameAwardTypeField();
    $step->apply();

    return TRUE;
  }

}
