<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_CiviAwards_Service_ApplicantManagementUtils as ApplicantManagementUtils;
use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;

/**
 * Set CustomGroup Award Sub Type Values.
 */
class CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues {

  /**
   * Displays Case Category Custom Group on custom group form page.
   *
   * This hook properly displays the sub type values for the applicant
   * management related custom group. Without these function, the default
   * values for the subtype will not be selected as civicrm will only
   * display case type values based on the fact that the case entity is
   * extended.
   *
   * @param CRM_Core_Form $form
   *   Form Class object.
   * @param string $formName
   *   Form Name.
   */
  public function run(CRM_Core_Form &$form, $formName) {
    if (!$this->shouldRun($formName, $form)) {
      return;
    }

    $this->setDefaultFormValueForCaseCategory($form);
  }

  /**
   * Sets default form value if the extended entity is for a case category.
   *
   * Sets the extends values default value for the award subtypes for an
   * applicant management case category related custom group.
   *
   * @param CRM_Core_Form $form
   *   Form Object.
   */
  private function setDefaultFormValueForCaseCategory(CRM_Core_Form &$form) {
    $defaults = $form->getVar('_defaults');
    $extendsId = $defaults['extends_entity_column_id'];

    $caseCategoryInstance = CaseCategoryHelper::getInstanceObject($extendsId);
    if (empty($caseCategoryInstance || !$caseCategoryInstance instanceof ApplicantManagementUtils)) {
      return;
    }

    $customGroupId = $form->getVar('_id');
    $postProcessHelper = new CustomGroupPostProcessHelper();
    $customGroupSubTypes = $postProcessHelper->getCustomGroupSubTypesList($customGroupId);
    $caseTypeCategories = (CRM_Case_BAO_CaseType::buildOptions('case_type_category', 'validate'));
    $defaults['extends'][0] = $caseTypeCategories[$extendsId];
    $hierSelect = $form->getElement('extends');
    $hierSelectElements = $hierSelect->getElements();
    $hierSelectElements[1]->setValue(!empty($customGroupSubTypes) ? array_filter($customGroupSubTypes) : '');
  }

  /**
   * Determines if the hook will run.
   *
   * Runs when extending case entities.
   *
   * @param string $formName
   *   Form name.
   * @param CRM_Core_Form $form
   *   Form class object.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($formName, CRM_Core_Form $form) {
    $defaults = $form->getVar('_defaults');
    $extends = $defaults['extends'][0];

    return $formName == CRM_Custom_Form_Group::class &&
      $form->getVar('_action') != CRM_Core_Action::ADD &&
      $extends === 'Case' &&
      !empty($defaults['extends_entity_column_id']);
  }

}
