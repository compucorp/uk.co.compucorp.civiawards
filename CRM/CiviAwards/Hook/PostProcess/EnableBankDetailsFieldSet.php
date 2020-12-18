<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Enable Ban kDetails FieldSet Post Process Hook class.
 */
class CRM_CiviAwards_Hook_PostProcess_EnableBankDetailsFieldSet extends CRM_CiviAwards_Hook_CaseCategoryFormBase {

  /**
   * Saves the case category instance type relationship.
   *
   * @param CRM_Core_Form $form
   *   The Form instance.
   * @param string $formName
   *   The Form class name.
   */
  public function run(CRM_Core_Form $form, $formName) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    $formAction = $form->getVar('_action');

    if ($formAction != CRM_Core_Action::DELETE) {
      $submitValues = $form->getVar('_submitValues');
      $financeManagementValue = $submitValues[FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME];

      $bankDetailsFieldSet = $this->getBankDetailsFieldSet();
      $isBankDetailsFieldSetEnabled = $bankDetailsFieldSet['is_active'] == 1;
      if (!empty($financeManagementValue) && !$isBankDetailsFieldSetEnabled) {
        $this->enableBankDetailsFieldSet($bankDetailsFieldSet['id']);
      }
    }
  }

  /**
   * Gets bank details field set data.
   *
   * @return array
   *   Bank details data.
   */
  private function getBankDetailsFieldSet() {
    $result = civicrm_api3('CustomGroup', 'get', ['name' => 'Awards_Payment_Bank_Details']);

    return array_shift($result['values']);
  }

  /**
   * Sets the Bank details field set to active.
   *
   * @param int $customFieldSetId
   *   Custom field set id.
   */
  private function enableBankDetailsFieldSet($customFieldSetId) {
    civicrm_api3('CustomGroup', 'create', [
      'id' => $customFieldSetId,
      'is_active' => 1,
    ]);
  }

}
