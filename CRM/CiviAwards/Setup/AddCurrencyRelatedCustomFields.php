<?php

/**
 * Setup Class for AddCurrencyRelatedCustomFields.
 */
class CRM_CiviAwards_Setup_AddCurrencyRelatedCustomFields {

  /**
   * Attaches the Currency related custom fields.
   *
   * These custom fields are not added in the XML file because
   * we need to attach the currencies enabled option group and
   * this can not be attached via the xml as it would attempt to
   * recreate the option group and throw an error.
   */
  public function apply() {
    $params = [
      'Awards_Payment_Bank_Details' => [
        'custom_group_id' => 'Awards_Payment_Bank_Details',
        'name' => 'Currency_In_Which_Account_Is_Held',
        'label' => 'Currency In Which Account Is Held',
        'data_type' => 'String',
        'html_type' => 'Select',
        'is_required' => '0',
        'is_searchable' => '0',
        'is_search_range' => '0',
        'weight' => '9',
        'is_active' => '1',
        'is_view' => '0',
        'text_length' => '255',
        'note_columns' => '60',
        'note_rows' => '4',
        'option_group_id' => 'currencies_enabled',
        'in_selector' => '0',
      ],
    ];

    foreach ($params as $customFieldName => $param) {
      civicrm_api3('CustomField', 'create', $param);
    }
  }

}
