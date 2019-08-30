<?php

/**
 * Class CRM_CiviAwards_Setup_DeleteAwardsCaseCategoryOption.
 */
class CRM_CiviAwards_Setup_DeleteAwardsCaseCategoryOption {

  /**
   * Deletes the Awards option from the case type category option values.
   */
  public function apply() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'name' => 'Awards',
      'option_group_id' => 'case_type_categories',
    ]);

    if ($result['count'] == 0) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['values'][0]['id']);
  }

}
