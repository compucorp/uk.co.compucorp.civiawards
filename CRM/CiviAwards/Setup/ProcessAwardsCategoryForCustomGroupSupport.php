<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Process Awards Category For CustomGroup Support.
 */
class CRM_CiviAwards_Setup_ProcessAwardsCategoryForCustomGroupSupport {

  const AWARDS_CATEGORY_CG_LABEL = 'Case (Awards)';

  /**
   * Add Awards as a valid Entity that a custom group can extend.
   */
  public function apply() {
    $caseCategoryCustomData = new CaseCategoryCustomDataType();
    $caseCategoryCustomFieldExtends = new CaseCategoryCustomFieldExtends();
    $caseCategoryCustomFieldExtends->create(
      CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME,
      self::AWARDS_CATEGORY_CG_LABEL,
      'CRM_CiviAwards_Helper_CaseTypeCategory::getAwardSubtypes;'
    );
    $caseCategoryCustomData->create(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
  }

}
