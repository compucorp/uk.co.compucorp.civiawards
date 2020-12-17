<?php

/**
 * Installs the Award Payment Bank Details Field Set.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1009 {

  /**
   * Installs the Award Payment Bank Details Field Set.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    $upgrader = CRM_CiviAwards_Upgrader_Base::instance();
    $upgrader->executeCustomDataFile('xml/custom_fields/payment_bank_details_install.xml');

    return TRUE;
  }

}
