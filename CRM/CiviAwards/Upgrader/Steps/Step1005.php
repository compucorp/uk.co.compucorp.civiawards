<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;

/**
 * Update fetch entity type function for applicant management categories.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1005 {

  /**
   * Update fetch entity type function for applicant management categories.
   *
   * Some Applicant management instance case categories have been set up for
   * custom group setup. Now that subtypes will have to be selected as part
   * of configuring these custom group entities, this PR updates these entities
   * with the function for fetching the award subtypes.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $caseCategoryValues = CaseTypeCategoryHelper::getApplicantManagementCaseCategories();
    $caseCategoryDetails = array_column(CaseCategoryHelper::getCaseCategories(), 'name', 'value');
    $caseCategoryNames = array_intersect_key($caseCategoryDetails, array_flip($caseCategoryValues));

    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'cg_extend_objects',
      'name' => 'civicrm_case',
      'value' => ['IN' => $caseCategoryNames],
    ]);

    if (empty($result['values'])) {
      return TRUE;
    }

    foreach ($result['values'] as $id => $value) {
      civicrm_api3('OptionValue', 'create', [
        'id' => $id,
        'description' => 'CRM_CiviAwards_Helper_CaseTypeCategory::getAwardSubtypes;',
      ]);
    }
  }

}
