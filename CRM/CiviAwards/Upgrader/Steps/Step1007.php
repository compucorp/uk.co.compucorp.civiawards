<?php

use CRM_CiviAwards_Setup_RenameAwardTypeOptionGroup as RenameAwardTypeOptionGroup;

/**
 * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1007 {

  /**
   * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $step = new RenameAwardTypeOptionGroup();
    $step->apply();

    return TRUE;
  }

}
