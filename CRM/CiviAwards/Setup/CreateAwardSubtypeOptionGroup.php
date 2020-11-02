<?php

/**
 * Sets up the Award sub type option group and values.
 */
class CRM_CiviAwards_Setup_CreateAwardSubtypeOptionGroup {

  /**
   * Award option group name.
   *
   * @var string
   */
  public $awardOptionGroupName = 'civiawards_award_subtype';

  /**
   * Award option group title.
   *
   * @var string
   */
  public $awardOptionGroupTitle = 'Award Sub Types';

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
      'name' => $this->awardOptionGroupName,
      'title' => ts($this->awardOptionGroupTitle),
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
        'option_group_id' => $this->awardOptionGroupName,
        'name' => $optionValue,
        'label' => ucfirst($optionValue),
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]);
    }
  }

}
