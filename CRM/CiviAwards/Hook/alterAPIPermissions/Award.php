<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseAwardHelper;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * Class CRM_Civicase_Hook_APIPermissions_alterPermissions.
 */
class CRM_CiviAwards_Hook_alterAPIPermissions_Award {

  /**
   * Alters the API permissions.
   *
   * @param string $entity
   *   The API entity.
   * @param string $action
   *   The API action.
   * @param array $params
   *   The API parameters.
   * @param array $permissions
   *   The API permissions.
   */
  public function run($entity, $action, array &$params, array &$permissions) {
    $permissionService = new CaseCategoryPermission();
    $caseCategoryPermissions = $permissionService->get(CaseAwardHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);
    $permissions['award_detail']['get'] = [
      [
        $caseCategoryPermissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name'],
        $caseCategoryPermissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name'],
        $caseCategoryPermissions['BASIC_CASE_CATEGORY_INFO']['name'],
      ],
    ];
    $awardCreatePermission = [
      ['administer CiviCase', 'create/edit awards']
    ];
    $permissions['award_detail']['create'] = $permissions['award_detail']['update'] = $awardCreatePermission;

    if ($this->modifyCaseTypeApiPermission($entity, $action, $params)) {
      $permissions['case_type']['create'] = $permissions['case_type']['update'] = $awardCreatePermission;
    }
  }

  /**
   * @param $entity
   * @param $action
   * @param array $params
   * @return bool
   */
  private function modifyCaseTypeApiPermission($entity, $action, array &$params) {
    $isCaseTypeCreateOrEdit = $entity == 'case_type' && in_array($action, ['create', 'update']);
    if (!$isCaseTypeCreateOrEdit) {
      return FALSE;
    }

    $caseCategoryName = $this->getCaseCategoryNameForCaseTypeWhenActionIsCreateOrEdit($params);
    if ($caseCategoryName == CaseAwardHelper::AWARDS_CASE_TYPE_CATEGORY_NAME) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Returns the case category name when action is create/edit
   *
   * @param array $params
   *   API parameters.
   *
   * @return string|null
   *   Case category name.
   */
  private function getCaseCategoryNameForCaseTypeWhenActionIsCreateOrEdit(array $params) {
    if (empty($params['case_type_category'])) {
      return;
    }

    return $this->getCaseTypeCategoryNameFromOptions($params['case_type_category']);
  }

  /**
   * Returns the case category name from case type id or name.
   *
   * @param mixed $caseTypeCategory
   *   Case category name.
   *
   * @return string
   *   Case category name.
   */
  private function getCaseTypeCategoryNameFromOptions($caseTypeCategory) {
    if (!is_numeric($caseTypeCategory)) {
      return $caseTypeCategory;
    }

    return CaseCategoryHelper::getCaseCategoryNameFromOptionValue($caseTypeCategory);
  }

}
