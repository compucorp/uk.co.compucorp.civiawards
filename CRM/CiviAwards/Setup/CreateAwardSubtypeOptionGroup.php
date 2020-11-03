<?php

/**
 * Sets up the Award sub type option group and values.
 */
class CRM_CiviAwards_Setup_CreateAwardSubtypeOptionGroup {

  /**
   * Award option group name.
   */
  const AWARD_OPTION_GROUP_NAME = 'civiawards_award_subtype';

  /**
   * Award option group title.
   */
  const AWARD_OPTION_GROUP_TITLE = 'Award Sub Types';

  /**
   * Creates the Awards Type option group with default values.
   */
  public function apply() {
    $this->createOptionGroupForAwardSubtype();
    $this->createOptionValuesForAwardSubtype();
  }

  /**
   * Creates the award sub type option group.
   */
  private function createOptionGroupForAwardSubtype() {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => self::AWARD_OPTION_GROUP_NAME,
      'title' => ts(self::AWARD_OPTION_GROUP_TITLE),
      'is_reserved' => 1,
    ]);
  }

  /**
   * Creates default option values for award sub type option group.
   */
  private function createOptionValuesForAwardSubtype() {
    $optionValues = [
      'medal',
      'prize',
      'programme',
      'scholarship',
      'award',
      'grant',
    ];

    foreach ($optionValues as $optionValue) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => self::AWARD_OPTION_GROUP_NAME,
        'name' => $optionValue,
        'label' => ucfirst($optionValue),
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]);
    }
  }

}
