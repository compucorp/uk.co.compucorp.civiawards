<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Class CRM_CiviAwards_Setup_AddAwardCaseTypeCustomGroupSupport.
 */
class CRM_CiviAwards_Setup_AddAwardCaseTypeCustomGroupSupport {

  const AWARD_CASE_TYPE_CG_LABEL = 'Award Case Type';

  /**
   * Add Award Case Type as a valid Entity that a custom group can extend.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->create(
      CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME,
      self::AWARD_CASE_TYPE_CG_LABEL,
      'CRM_CiviAwards_Helper_CaseTypeCategory::getAwardCaseTypes;'
    );
  }

}
