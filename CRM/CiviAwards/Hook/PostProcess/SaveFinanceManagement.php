<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Hook_PostProcess_SaveCaseCategoryInstance as CaseCategoryInstanceHook;
use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Finance Management Post Process Hook class.
 */
class CRM_CiviAwards_Hook_PostProcess_SaveFinanceManagement extends CRM_CiviAwards_Hook_CaseCategoryFormBase {

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
    $financialManagementService = new FinanceManagementSettingService();

    if ($formAction == CRM_Core_Action::DELETE) {
      $formValues = $form->getVar('_values');
      $financialManagementService->deleteForCaseCategory($formValues['value']);
    }
    else {
      $submitValues = $form->getVar('_submitValues');
      $caseCategoryValue = $submitValues['value'];
      $instanceTypeValue = $submitValues[CaseCategoryInstanceHook::INSTANCE_TYPE_FIELD_NAME];
      if (!$this->isApplicantManagement($instanceTypeValue)) {
        return;
      }

      $financeManagementValue = $submitValues[FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME];
      $financialManagementService->saveForCaseCategory($caseCategoryValue, $financeManagementValue);
    }
  }

  /**
   * Checks if the instance type is of applicant management.
   *
   * @param int $instanceType
   *   Category Instance Type.
   *
   * @return bool
   *   Applicant management instance or not.
   */
  private function isApplicantManagement($instanceType) {
    $instanceOptions = CRM_Civicase_BAO_CaseCategoryInstance::buildOptions('instance_id', 'validate');

    return $instanceOptions[$instanceType] == CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME;
  }

}
