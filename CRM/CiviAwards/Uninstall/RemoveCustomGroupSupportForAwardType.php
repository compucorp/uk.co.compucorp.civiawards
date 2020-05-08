<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Service_CaseCategoryCaseTypeCustomFieldExtends as CaseCategoryCaseTypeCustomFieldExtends;

/**
 * Class CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForAwardType.
 */
class CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForAwardType {

  /**
   * Deletes the Award Type option from the CG Extends option values.
   */
  public function apply() {
    $caseCategoryCaseTypeCustomFieldExtends = new CaseCategoryCaseTypeCustomFieldExtends();
    $caseCategoryCaseTypeCustomFieldExtends->delete(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
  }

}
