<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Add Payments Tab module.
 *
 * This will add the payments tab module to awards instances that support
 * finances.
 */
class CRM_CiviAwardsPaymentsTab_Hook_AddCiviCaseDependentAngularModules_PaymentsTabModule {

  /**
   * Adds the module.
   *
   * @param array $dependentModules
   *   A list of angular module dependencies.
   */
  public function run(array &$dependentModules) {
    if (!$this->shouldRun()) {
      return;
    }

    $dependentModules[] = 'civiawards-payments-tab';
  }

  /**
   * Determines if the hook should run.
   *
   * @return bool
   *   True for awards that support finances.
   */
  private function shouldRun() {
    $caseTypeCategoryName = CRM_Utils_Request::retrieve('case_type_category', 'String');

    try {
      $caseTypeCategory = civicrm_api3('OptionValue', 'getsingle', [
        'option_group_id' => 'case_type_categories',
        'name' => $caseTypeCategoryName,
      ]);

      $financeManagement = new FinanceManagementSettingService();
      $financeManagementValue = $financeManagement->get($caseTypeCategory['value']);
      $supportsFinanceTabModule = !empty($financeManagementValue);

      return $supportsFinanceTabModule;
    }
    catch (\Exception $exception) {
      return FALSE;
    }
  }

}
