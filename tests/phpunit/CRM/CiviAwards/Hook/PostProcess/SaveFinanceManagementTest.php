<?php

use CRM_Civicase_Hook_PostProcess_SaveCaseCategoryInstance as CaseCategoryInstanceHook;
use CRM_CiviAwards_Hook_PostProcess_SaveFinanceManagement as SaveFinanceManagementHook;
use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Test class for the SaveFinanceManagementTest Hook.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_PostProcess_SaveFinanceManagementTest extends BaseHeadlessTest {

  /**
   * Tests setting is not saved if not applicant management.
   */
  public function testSettingNotSavedWhenNotApplicantManagementInstance() {
    $instanceOptions = array_flip(CRM_Civicase_BAO_CaseCategoryInstance::buildOptions('instance_id', 'validate'));
    $caseCategoryId = 5;
    $instanceType = $instanceOptions['case_management'];
    $form = $this->getCaseCategoryFormObject($caseCategoryId, $instanceType);
    $hook = new SaveFinanceManagementHook();
    $hook->run($form, CRM_Admin_Form_Options::class);
    $financeManagementService = new FinanceManagementSettingService();
    $this->assertEmpty($financeManagementService->get());
  }

  /**
   * Tests setting is saved.
   */
  public function testSettingIsSavedWhenApplicantManagementInstance() {
    $instanceOptions = array_flip(CRM_Civicase_BAO_CaseCategoryInstance::buildOptions('instance_id', 'validate'));
    $caseCategoryId = 5;
    $instanceType = $instanceOptions[CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME];
    $form = $this->getCaseCategoryFormObject($caseCategoryId, $instanceType, TRUE);
    $hook = new SaveFinanceManagementHook();
    $hook->run($form, CRM_Admin_Form_Options::class);
    $financeManagementService = new FinanceManagementSettingService();
    $result = $financeManagementService->get();
    $this->assertCount(1, $result);
    $this->assertTrue($result[$caseCategoryId]);
  }

  /**
   * Gets case category form object.
   *
   * @param int $caseCategoryId
   *   Case category Id.
   * @param int $instanceType
   *   Instance Type.
   * @param bool $financialManagementValue
   *   Financial management value.
   *
   * @return \CRM_Admin_Form_Options
   *   Form object.
   */
  private function getCaseCategoryFormObject($caseCategoryId, $instanceType, $financialManagementValue = TRUE) {
    $form = new CRM_Admin_Form_Options();
    $form->setVar('_gName', 'case_type_categories');
    $form->setVar('_action', CRM_Core_Action::ADD);
    $submitValues = [
      'value' => $caseCategoryId,
      CaseCategoryInstanceHook::INSTANCE_TYPE_FIELD_NAME => $instanceType,
      FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME => $financialManagementValue,
    ];
    $form->setVar('_submitValues', $submitValues);

    return $form;
  }

}
