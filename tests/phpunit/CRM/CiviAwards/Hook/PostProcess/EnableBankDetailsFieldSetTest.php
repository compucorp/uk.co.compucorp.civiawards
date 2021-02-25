<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;
use CRM_CiviAwards_Hook_PostProcess_EnableBankDetailsFieldSet as EnableBankDetailsFieldSetHook;

/**
 * Test class for the EnableBankDetailsFieldSet Hook.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_PostProcess_EnableBankDetailsFieldSetTest extends BaseHeadlessTest {

  /**
   * Tests that bank details field set is enabled.
   */
  public function testBankDetailsFieldSetIsEnabledWhenFinanceManagementSettingIsTrue() {
    $this->assertEquals(0, $this->getBankDetailsCustomGroupStatus());
    $form = $this->getCaseCategoryFormObject(TRUE);
    $hook = new EnableBankDetailsFieldSetHook();
    $hook->run($form, CRM_Admin_Form_Options::class);

    // The custom group is now enabled.
    $this->assertEquals(1, $this->getBankDetailsCustomGroupStatus());
  }

  /**
   * Tests that bank details field set remains disabled.
   */
  public function testBankDetailsFieldSetIsNotEnabledWhenFinanceManagementSettingIsFalse() {
    $this->assertEquals(0, $this->getBankDetailsCustomGroupStatus());
    $form = $this->getCaseCategoryFormObject(FALSE);
    $hook = new EnableBankDetailsFieldSetHook();
    $hook->run($form, CRM_Admin_Form_Options::class);
    $this->assertEquals(0, $this->getBankDetailsCustomGroupStatus());
  }

  /**
   * Tests that the bank details set is not disabled after it has been enabled.
   */
  public function testBankDetailsFieldSetIsNotDisabledWhenFinanceManagementSettingIsFalseIfItHasBeenPreviouslyEnabled() {
    // Enable the bank details field set.
    $form = $this->getCaseCategoryFormObject(TRUE);
    (new EnableBankDetailsFieldSetHook())->run($form, CRM_Admin_Form_Options::class);
    $this->assertEquals(1, $this->getBankDetailsCustomGroupStatus());

    // Make sure it remains enabled even if finance management is false.
    $form = $this->getCaseCategoryFormObject(FALSE);
    (new EnableBankDetailsFieldSetHook())->run($form, CRM_Admin_Form_Options::class);
    $this->assertEquals(1, $this->getBankDetailsCustomGroupStatus());
  }

  /**
   * Gets case category form object.
   *
   * @param bool $financialManagementValue
   *   Financial management value.
   *
   * @return \CRM_Admin_Form_Options
   *   Form object.
   */
  private function getCaseCategoryFormObject($financialManagementValue = TRUE) {
    $form = new CRM_Admin_Form_Options();
    $form->setVar('_gName', 'case_type_categories');
    $form->setVar('_action', CRM_Core_Action::ADD);
    $submitValues = [
      FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME => $financialManagementValue,
    ];
    $form->setVar('_submitValues', $submitValues);

    return $form;
  }

  /**
   * Returns bank details custom group status.
   *
   * @return bool
   *   Custom group status.
   */
  private function getBankDetailsCustomGroupStatus() {
    $result = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => 'Awards_Payment_Bank_Details',
    ]);

    return $result['is_active'];
  }

}
