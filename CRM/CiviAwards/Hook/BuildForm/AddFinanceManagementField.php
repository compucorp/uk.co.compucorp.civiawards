<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

/**
 * Manages adding the finance management field to case category form.
 */
class CRM_CiviAwards_Hook_BuildForm_AddFinanceManagementField extends CRM_CiviAwards_Hook_CaseCategoryFormBase {

  /**
   * Adds the Enable Finance Management Form field.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($form, $formName)) {
      return;
    }

    $this->addFinanceManagementFormField($form);
    $this->addFinanceManagementTemplate();
    $instanceTypeOptions = array_flip(CRM_Civicase_BAO_CaseCategoryInstance::buildOptions('instance_id', 'validate'));
    $applicantManagementOptionValue = $instanceTypeOptions[CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME];
    $form->assign('applicantManagement', $applicantManagementOptionValue);
  }

  /**
   * Adds the finance management form field.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   */
  private function addFinanceManagementFormField(CRM_Core_Form $form) {
    $financeManagement = $form->add(
      'advcheckbox',
      FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME,
      ts('Enable Finance Management')
    );

    if ($form->getVar('_id')) {
      $caseCategoryValues = $form->getVar('_values');
      $defaultFinanceManagementValue = $this->getDefaultValue($caseCategoryValues['value']);
      $financeManagement->setValue($defaultFinanceManagementValue);
    }
  }

  /**
   * Adds the template for the finance management field.
   */
  private function addFinanceManagementTemplate() {
    $templatePath = CRM_CiviAwards_ExtensionUtil::path() . '/templates';
    CRM_Core_Region::instance('page-body')->add(
      [
        'template' => "{$templatePath}/CRM/CiviAwards/Form/FinanceManagement.tpl",
      ]
    );
  }

  /**
   * Returns the default value for the finance management field.
   *
   * @param int $categoryValue
   *   Category value.
   *
   * @return mixed|null
   *   Default value.
   */
  private function getDefaultValue($categoryValue) {
    $financeManagementValues = Civi::settings()->get(FinanceManagementSettingService::FINANCE_MANAGEMENT_NAME);
    if (empty($financeManagementValues[$categoryValue])) {
      return NULL;
    }

    return $financeManagementValues[$categoryValue];
  }

}
