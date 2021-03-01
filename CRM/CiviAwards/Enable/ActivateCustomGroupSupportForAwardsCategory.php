<?php

use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Class ActivateCustomGroupSupportForAwardsCategory.
 */
class CRM_CiviAwards_Enable_ActivateCustomGroupSupportForAwardsCategory {

  /**
   * Activates the Awards option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryDetails = array_column(CRM_Civicase_Helper_CaseCategory::getCaseCategories(), 'name', 'value');
    $applicantCaseCategoryIds = CRM_CiviAwards_Helper_CaseTypeCategory::getApplicantManagementCaseCategories();
    $caseCategoryCustomFieldExtends = new CaseCategoryCustomFieldExtends();
    foreach ($applicantCaseCategoryIds as $caseCategoryValue) {
      $caseCategoryCustomFieldExtends->activate($caseCategoryDetails[$caseCategoryValue]);
    }
  }

}
