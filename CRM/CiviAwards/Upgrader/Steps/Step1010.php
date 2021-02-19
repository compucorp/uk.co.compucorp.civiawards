<?php

/**
 * Adds the Euro currency option.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1010 {

  /**
   * Apply function that runs on setup.
   *
   * @return bool
   *   true when successfull.
   */
  public function apply() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'currencies_enabled',
      'name' => 'EUR (€)',
      'label' => 'EUR',
      'value' => 'EUR',
      'is_default' => 0,
      'is_active' => TRUE,
      'is_reserved' => FALSE,
    ]);

    return TRUE;
  }

}
