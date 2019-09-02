<?php

use CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue as AddAwardsCgExtendsOptionValue;

/**
 * Class CRM_CiviAwards_Setup_DeleteAwardsCgExtendsOptionValue.
 */
class CRM_CiviAwards_Setup_DeleteAwardsCgExtendsOption {

  /**
   * Deletes the Awards option from the CG Extends option values.
   */
  public function apply() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'label' => AddAwardsCgExtendsOptionValue::AWARDS_CATEGORY_CG_LABEL,
      'option_group_id' => 'cg_extend_objects',
    ]);

    if ($result['count'] == 0) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['values'][0]['id']);
  }

}
