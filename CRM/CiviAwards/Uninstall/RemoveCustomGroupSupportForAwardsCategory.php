<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCustomDataType as CaseCategoryCustomDataType;
use CRM_Civicase_Service_CaseCategoryCustomFieldExtends as CaseCategoryCustomFieldExtends;

/**
 * Class CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForAwardsCategory.
 */
class CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForAwardsCategory {

  /**
   * Deletes the Awards option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCustomData = new CaseCategoryCustomDataType();
    $caseCategoryCustomFieldExtends = new CaseCategoryCustomFieldExtends();
    $caseCategoryCustomFieldExtends->delete(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
    $caseCategoryCustomData->delete(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
  }

}
