<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Class ActivateCustomGroupSupportForApplicantManagement.
 */
class CRM_CiviAwards_Enable_ActivateCustomGroupSupportForApplicantManagement {

  /**
   * Activates the Award Sub Type option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->activate(CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME);
  }

}
