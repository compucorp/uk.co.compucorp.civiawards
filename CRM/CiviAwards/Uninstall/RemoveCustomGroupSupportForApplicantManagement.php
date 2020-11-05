<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Class RemoveCustomGroupSupportForApplicantManagement.
 */
class CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForApplicantManagement {

  /**
   * Deletes the Award Sub Type option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->delete(CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME);
  }

}
