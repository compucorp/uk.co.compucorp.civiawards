<?php

use CRM_CiviAwards_Setup_CreateAwardsMenus as CreateAwardsMenus;

/**
 * Upgrader that creates the Civi Awards menu items.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1001 {

  /**
   * Creates the Awards menu items.
   *
   * @return bool
   *   returns true when the menu items have been created successfully.
   */
  public function apply() {
    $step = new CreateAwardsMenus();
    $step->apply();

    return TRUE;
  }

}
