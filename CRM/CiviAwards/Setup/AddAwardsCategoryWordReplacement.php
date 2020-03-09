<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Class CRM_CiviAwards_Setup_AddAwardsCategoryWordReplacement.
 */
class CRM_CiviAwards_Setup_AddAwardsCategoryWordReplacement {

  /**
   * Creates the Awards word replacement option value.
   */
  public function apply() {
    $optionName = CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME . "_word_replacement";
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'case_type_category_word_replacement_class',
      'name' => $optionName,
      'label' => $optionName,
      'value' => 'CRM_CiviAwards_WordReplacement_AwardsCategory',
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

}
