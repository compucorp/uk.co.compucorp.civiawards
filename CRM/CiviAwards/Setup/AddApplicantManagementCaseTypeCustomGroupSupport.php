<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Custom group setup for Applicant management case types.
 */
class CRM_CiviAwards_Setup_AddApplicantManagementCaseTypeCustomGroupSupport {

  const APPLICANT_MANAGEMENT_CG_LABEL = 'Applicant Management Case Types';

  /**
   * Add Applicant Management as a valid Entity that a custom group can extend.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->create(
      CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME,
      self::APPLICANT_MANAGEMENT_CG_LABEL,
      'CRM_CiviAwards_Helper_CaseTypeCategory::getApplicantManagementCaseTypes;'
    );
  }

}
