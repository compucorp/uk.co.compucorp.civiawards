<?php

/**
 * Setup Class for AddCurrencyOptionGroupToCustomFields.
 */
class CRM_CiviAwards_Setup_AddCurrencyOptionGroupToCustomFields {

  /**
   * Attaches the currencies enabled option group.
   *
   * These currency option group for the are not added in the XML file because
   * the option group Id can not be evaluated in the XML hence adding it here
   * after the custom fields have been created.
   */
  public function apply() {
    $params = [
      'Currency_In_Which_Account_Is_Held' => 'Awards_Payment_Bank_Details',
      'Payment_Amount_Currency_Type' => 'Awards_Payment_Information',
      'Value_in_Functional_Currency_Type' => 'Awards_Payment_Information',
      'Payment_Currency' => 'Awards_Payment_Information',
    ];

    foreach ($params as $customFieldName => $customGroupName) {
      $result = civicrm_api3('CustomField', 'get', [
        'custom_group_id' => $customGroupName,
        'name' => $customFieldName,
      ]);

      if (empty($result['id'])) {
        continue;
      }

      $params = [
        'id' => $result['id'],
        'option_group_id' => 'currencies_enabled',
      ];

      civicrm_api3('CustomField', 'create', $params);
    }
  }

}
