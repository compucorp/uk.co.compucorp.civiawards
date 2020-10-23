<?php

use CRM_Civicase_Setup_CaseCategoryInstanceSupport as CaseCategoryInstanceSupport;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;
use CRM_Civicase_BAO_CaseCategoryInstance as CaseCategoryInstance;

/**
 * Class CreateApplicantManagementOption.
 */
class CRM_CiviAwards_Setup_CreateApplicantManagementOption {

  /**
   * Creates the Applicant management option and sets instance type for Awards.
   */
  public function apply() {
    $this->createApplicantManagementOption();
  }

  /**
   * Creates the Applicant management option value.
   */
  private function createApplicantManagementOption() {
    $categoryInstance = new CaseCategoryInstanceSupport();
    $categoryInstance->createCaseCategoryInstanceOptionGroup();

    $applicantManagement = CRM_Core_BAO_OptionValue::ensureOptionValueExists(
      [
        'option_group_id' => CaseCategoryInstanceSupport::INSTANCE_OPTION_GROUP,
        'name' => CaseTypeCategory::APPLICATION_MANAGEMENT_NAME,
        'label' => 'Applicant Management',
        'grouping' => 'CRM_CiviAwards_Service_ApplicantManagementUtils',
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]
    );

    $this->setInstanceTypeForAwardsCategory($applicantManagement['value']);
  }

  /**
   * Sets the instance type for the awards case type category.
   *
   * @param mixed $instanceValue
   *   Applicant management instance value.
   */
  private function setInstanceTypeForAwardsCategory($instanceValue) {
    $caseCategories = CRM_Core_OptionGroup::values('case_type_categories', TRUE, FALSE, TRUE, NULL, 'name');
    $awardCaseCategoryValue = $caseCategories[CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME];
    $caseCategoryInstance = new CaseCategoryInstance();
    $caseCategoryInstance->category_id = $awardCaseCategoryValue;
    $caseCategoryInstance->find(TRUE);
    $caseCategoryInstance->instance_id = $instanceValue;
    $caseCategoryInstance->save();
  }

}
