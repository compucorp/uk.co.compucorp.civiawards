<?php

use CRM_CiviAwards_Setup_UpdateAwardPaymentActivityStatusLabel as UpdateAwardPaymentActivityStatusLabel;

/**
 * Updates the Applicant review activity status label.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1010 {

  /**
   * Installs the Award Payment related field sets and activity types.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    (new UpdateAwardPaymentActivityStatusLabel())->apply();

    return TRUE;
  }

}
