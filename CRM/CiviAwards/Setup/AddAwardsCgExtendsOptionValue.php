<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Class CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue.
 */
class CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue {

  const AWARDS_CATEGORY_CG_LABEL = 'Case (Awards)';

  /**
   * Add Awards as a valid Entity that a custom group can extend.
   */
  public function apply() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'label' => self::AWARDS_CATEGORY_CG_LABEL,
    ]);

    if ($result['count'] > 0) {
      return;
    }

    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case',
      'label' => self::AWARDS_CATEGORY_CG_LABEL,
      'value' => CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME,
      'description' => 'CRM_CiviAwards_Helper_CaseTypeCategory::getAwardCaseTypes;',
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

}
