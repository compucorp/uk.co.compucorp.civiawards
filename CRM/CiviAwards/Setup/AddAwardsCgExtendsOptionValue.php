<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Class CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue.
 */
class CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue {

  const AWARDS_CATEGORY_CG_LABEL = 'Case (Awards)';

  /**
   * Add Awards as a valid Entity that a custom group can extend.
   *
   * We also update the description just incase the module was
   * disabled before uninstallation.
   */
  public function apply() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'label' => self::AWARDS_CATEGORY_CG_LABEL,
    ]);

    if ($result['count'] > 0) {
      $this->toggleOptionValueStatus(TRUE);

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

  /**
   * Enables/Disables the Awards option values.
   *
   * The method also updates the Option Value 'description' to empty when the
   * extension is disabled. Because the 'description' is dynamically loaded,
   * this helps prevent the fatal error that is thrown when Civi tries
   * to load a class from a disabled extension.
   *
   * @param bool $newStatus
   *   True to enable, False to disable.
   */
  public function toggleOptionValueStatus($newStatus) {
    $optionValueDescription = (
      $newStatus ?
        'CRM_CiviAwards_Helper_CaseTypeCategory::getAwardCaseTypes;' :
          ''
    );

    civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'label' => self::AWARDS_CATEGORY_CG_LABEL,
      'api.OptionValue.create' => [
        'id' => '$value.id',
        'description' => $optionValueDescription,
        'is_active' => $newStatus,
      ],
    ]);
  }

}
