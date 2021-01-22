<?php

use CRM_CiviAwards_Setup_AddCurrencyOptionGroupToCustomFields as AddCurrencyOptionGroupToCustomFields;
use CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes as CreateAwardPaymentActivityTypes;

/**
 * Installs the Award Payment related field sets and activity types.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1009 {

  /**
   * Installs the Award Payment related field sets and activity types.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    (new CreateAwardPaymentActivityTypes())->apply();
    $upgrader = CRM_CiviAwards_Upgrader_Base::instance();
    $customFieldSetsToCreate = [
      'Awards_Payment_Bank_Details' => 'payment_bank_details',
      'Payment_Approval' => 'payment_approval',
      'Awards_Payment_Information' => 'payment_information',
    ];

    foreach ($customFieldSetsToCreate as $customFieldName => $xmlFileName) {
      $result = civicrm_api3('CustomGroup', 'get', [
        'name' => $customFieldName,
      ]);

      if (!empty($result['id'])) {
        continue;
      }

      $upgrader->executeCustomDataFile("xml/custom_fields/{$xmlFileName}_install.xml");
    }
    (new AddCurrencyOptionGroupToCustomFields())->apply();

    return TRUE;
  }

}
