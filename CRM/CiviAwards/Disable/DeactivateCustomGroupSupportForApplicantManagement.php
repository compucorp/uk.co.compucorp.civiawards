<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Class Disable_DeactivateCustomGroupSupportForApplicantManagement.
 */
class CRM_CiviAwards_Disable_DeactivateCustomGroupSupportForApplicantManagement {

  /**
   * Deactivate the Award Sub Type option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->deactivate(CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME);
  }

}
