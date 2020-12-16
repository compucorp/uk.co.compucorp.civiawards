<?php

use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Test class for the finance management settings service.
 */
class CRM_CiviAwards_Service_FinanceManagementSettingServiceTest extends BaseHeadlessTest {

  /**
   * Tests save for case category.
   */
  public function testSaveForCaseCategory() {
    $caseCategoryId1 = 5;
    $caseCategoryId2 = 8;
    $financeManagement = new FinanceManagementSettingService();
    $financeManagement->saveForCaseCategory($caseCategoryId1, TRUE);
    $financeManagement->saveForCaseCategory($caseCategoryId2, FALSE);
    $results = $this->getFinanceManagementValues();
    $this->assertTrue($results[$caseCategoryId1]);
    $this->assertFalse($results[$caseCategoryId2]);
    $this->assertCount(2, $results);
  }

  /**
   * Test delete for case category.
   */
  public function testDeleteForCaseCategory() {
    $caseCategoryId = 5;
    $financeManagement = new FinanceManagementSettingService();
    $financeManagement->saveForCaseCategory($caseCategoryId, TRUE);
    $results = $financeManagementValues = Civi::settings()->get(FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME);
    $this->assertTrue($results[$caseCategoryId]);
    $financeManagement->deleteForCaseCategory($caseCategoryId);
    $results = $this->getFinanceManagementValues();
    $this->assertCount(0, $results);
  }

  /**
   * Test get without passing a parameter.
   */
  public function testGetWithoutCaseCategoryParameter() {
    $caseCategoryId1 = 5;
    $caseCategoryId2 = 8;
    $financeManagement = new FinanceManagementSettingService();
    $financeManagement->saveForCaseCategory($caseCategoryId1, FALSE);
    $financeManagement->saveForCaseCategory($caseCategoryId2, TRUE);
    $results = $financeManagement->get();
    $this->assertCount(2, $results);
    $this->assertFalse($results[$caseCategoryId1]);
    $this->assertTrue($results[$caseCategoryId2]);
  }

  /**
   * Test get when parameter is passed.
   */
  public function testGetWithCaseCategoryParameter() {
    $caseCategoryId1 = 5;
    $caseCategoryId2 = 8;
    $financeManagement = new FinanceManagementSettingService();
    $financeManagement->saveForCaseCategory($caseCategoryId1, FALSE);
    $financeManagement->saveForCaseCategory($caseCategoryId2, TRUE);
    $result = $financeManagement->get($caseCategoryId2);
    $this->assertTrue($result);
  }

  /**
   * Get financial management values.
   *
   * @return array
   *   Financial management values.
   */
  private function getFinanceManagementValues() {
    return Civi::settings()->get(FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME);
  }

}
