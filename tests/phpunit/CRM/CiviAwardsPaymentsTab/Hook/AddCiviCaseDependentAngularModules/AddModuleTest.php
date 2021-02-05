<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;
use CRM_CiviAwards_Test_Fabricator_CaseCategory as CaseCategoryFabricator;
use CRM_CiviAwardsPaymentsTab_Hook_AddCiviCaseDependentAngularModules_AddModule as AddModuleHook;

/**
 * AddModuleTest class.
 *
 * @group headless
 */
class CRM_CiviAwardsPaymentsTab_Hook_AddCiviCaseDependentAngularModules_AddModuleTest extends BaseHeadlessTest {

  /**
   * Test adding the payments tab module for awards that support finances.
   */
  public function testAddingModuleForAwardsWithFinanceSupport() {
    $modulesList = [];
    $financeManagement = new FinanceManagementSettingService();
    $addModuleHook = new AddModuleHook();
    $caseTypeCategory = CaseCategoryFabricator::fabricate(['name' => 'category1']);

    $_REQUEST['case_type_category'] = $caseTypeCategory['name'];
    $financeManagement->saveForCaseCategory($caseTypeCategory['value'], TRUE);

    $addModuleHook->run($modulesList);

    $this->assertEquals(['civiawards-payments-tab'], $modulesList);
  }

  /**
   * Test ignoring payments tab module for awards that don't support finances.
   */
  public function testIgnoringModuleForAwardsWithoutFinanceSupport() {
    $modulesList = [];
    $financeManagement = new FinanceManagementSettingService();
    $addModuleHook = new AddModuleHook();
    $caseTypeCategory = CaseCategoryFabricator::fabricate(['name' => 'category1']);

    $_REQUEST['case_type_category'] = $caseTypeCategory['name'];
    $financeManagement->saveForCaseCategory($caseTypeCategory['value'], FALSE);

    $addModuleHook->run($modulesList);

    $this->assertEquals([], $modulesList);
  }

}
