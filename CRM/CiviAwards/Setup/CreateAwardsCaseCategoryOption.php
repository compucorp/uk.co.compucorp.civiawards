<?php

/**
 * Class CreateAwardsCategoryOptionValue.
 */
class CRM_CiviAwards_Setup_CreateAwardsCaseCategoryOption {

  /**
   * Creates the Awards option as a case type category option value.
   */
  public function apply() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'case_type_categories',
      'name' => 'Awards',
      'label' => 'Awards',
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

}
