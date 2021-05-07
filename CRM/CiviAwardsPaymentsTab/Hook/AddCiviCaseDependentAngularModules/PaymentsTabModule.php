<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;
use CRM_Civicase_Helper_CaseUrl as CaseUrlHelper;

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
    [$caseCategoryId, $caseCategoryName] = CaseUrlHelper::getCategoryParamsFromUrl();
    $financeManagement = new FinanceManagementSettingService();
    $financeManagementValue = $financeManagement->get($caseCategoryId);
    $supportsFinanceTabModule = !empty($financeManagementValue);

    return $supportsFinanceTabModule;
  }

}
