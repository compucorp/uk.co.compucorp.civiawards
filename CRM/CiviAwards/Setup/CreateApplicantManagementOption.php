<?php

use CRM_Civicase_Setup_CaseCategoryInstanceSupport as CaseCategoryInstanceSupport;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;

/**
 * Class CreateApplicantManagementOption.
 */
class CRM_CiviAwards_Setup_CreateApplicantManagementOption {

  /**
   * Creates the Applicant management option and sets instance type for Awards.
   */
  public function apply() {
    $this->createApplicantManagementOption();
    $this->setInstanceTypeForAwardsCategory();
  }

  /**
   * Creates the Applicant management option value.
   */
  private function createApplicantManagementOption() {
    $categoryInstance = new CaseCategoryInstanceSupport();
    $categoryInstance->createCaseCategoryInstanceOptionGroup();

    CRM_Core_BAO_OptionValue::ensureOptionValueExists(
      [
        'option_group_id' => CaseCategoryInstanceSupport::INSTANCE_OPTION_GROUP,
        'name' => CaseTypeCategory::APPLICATION_MANAGEMENT_NAME,
        'label' => 'Applicant Management',
        'grouping' => 'CRM_CiviAwards_Service_ApplicantManagementUtils',
        'is_active' => TRUE,
        'is_reserved' => TRUE,
      ]
    );
  }

  /**
   * Sets the instance type for the awards case type category.
   */
  private function setInstanceTypeForAwardsCategory() {
    $params = [
      'category_id' => CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME,
      'instance_id' => CaseTypeCategory::APPLICATION_MANAGEMENT_NAME,
    ];

    $result = civicrm_api3('CaseCategoryInstance', 'get', [
      'category_id' => CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME,
    ]);

    if ($result['count'] == 1) {
      return;
    }

    civicrm_api3('CaseCategoryInstance', 'create', $params);
  }

}
