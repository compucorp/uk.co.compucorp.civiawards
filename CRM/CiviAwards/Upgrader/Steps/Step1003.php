<?php

use CRM_CiviAwards_Setup_AddApplicationManagementWordReplacement as AddApplicationManagementWordReplacement;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Set up Applicant Management Word replacements.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1003 {

  /**
   * Add Applicant management word replacement option value.
   *
   * @return bool
   *   returns true when the menu items have been created successfully.
   */
  public function apply() {
    $this->deleteAwardsCategoryWordReplacement();
    $step = new AddApplicationManagementWordReplacement();
    $step->apply();

    return TRUE;
  }

  /**
   * Deletes the deprecated Awards case category word replacement option value.
   */
  private function deleteAwardsCategoryWordReplacement() {
    $optionName = CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME . "_word_replacement";
    $result = civicrm_api3('OptionValue', 'get', [
      'name' => $optionName,
      'option_group_id' => 'case_type_category_word_replacement_class',
    ]);

    if (empty($result['id'])) {
      return;
    }

    CRM_Core_BAO_OptionValue::del($result['id']);
  }

}
