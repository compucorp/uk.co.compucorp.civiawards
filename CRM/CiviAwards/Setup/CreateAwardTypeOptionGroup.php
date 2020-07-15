<?php

/**
 * Class CRM_CiviAwards_Setup_CreateAwardTypeOptionGroup.
 */
class CRM_CiviAwards_Setup_CreateAwardTypeOptionGroup {

  /**
   * Award option group name.
   *
   * @var string
   */
  private $awardOptionGroupName = 'civiawards_award_type';

  /**
   * Creates the Awards Type option group with default values.
   */
  public function apply() {
    $this->createOptionGroupForAwardType();
    $this->createOptionValuesForAwardType();
  }

  /**
   * Creates the award type option group.
   */
  private function createOptionGroupForAwardType() {
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => $this->awardOptionGroupName,
      'title' => ts('Award Types'),
      'is_reserved' => 1,
    ]);
  }

  /**
   * Creates default option values for award type option group.
   */
  private function createOptionValuesForAwardType() {
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
        'option_group_id' => $this->awardOptionGroupName,
        'name' => $optionValue,
        'label' => ucfirst($optionValue),
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]);
    }
  }

}
