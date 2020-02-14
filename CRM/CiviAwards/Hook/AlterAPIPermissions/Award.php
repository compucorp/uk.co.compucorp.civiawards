<?php

use CRM_CiviAwards_Helper_ApplicantReview as ApplicantReviewHelper;
use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseAwardHelper;
use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * Class CRM_Civicase_Hook_APIPermissions_alterPermissions.
 */
class CRM_CiviAwards_Hook_AlterAPIPermissions_Award {

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
      ['administer CiviCase', 'create/edit awards'],
    ];
    $permissions['award_detail']['create'] = $permissions['award_detail']['update'] = $awardCreatePermission;
    $permissions['award_manager']['get'] = $awardCreatePermission;

    if ($this->modifyCaseTypeApiPermission($entity, $action, $params)) {
      $permissions['case_type']['create'] = $permissions['case_type']['update'] = $awardCreatePermission;
    }

    if ($this->modifyCustomFieldApiPermission($entity, $action, $params)) {
      $permissions['custom_field']['get'] = [
        ['access review custom field set', 'access all custom data'],
      ];
    }
  }

  /**
   * Checks whether to modify case type API permissions.
   *
   * When the case type to be created is of type award, the permission
   * is to be modified and function returns true.
   *
   * @param string $entity
   *   The API entity.
   * @param string $action
   *   The API action.
   * @param array $params
   *   Params.
   *
   * @return bool
   *   Whether to modify API permission or not.
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
   * Returns the case category name when action is create/edit.
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

  /**
   * Checks whether to modify custom field API permissions.
   *
   * When the custom fields to be modified/fetched is belongs to a Custom
   * group attached to the reserved Applicant Review Activity type,
   * it returns TRUE.
   *
   * @param string $entity
   *   The API entity.
   * @param string $action
   *   The API action.
   * @param array $params
   *   Params.
   *
   * @return bool
   *   Whether to modify API permission or not.
   */
  private function modifyCustomFieldApiPermission($entity, $action, array &$params) {
    $isCustomFieldGet = $entity == 'custom_field' && in_array($action, ['get']);
    $hasGroupIdParameter = !empty($params['custom_group_id']);
    if (!$isCustomFieldGet || !$hasGroupIdParameter) {
      return FALSE;
    }

    $groupDetails = $this->getCustomGroupDetails($params['custom_group_id']);

    if (empty($groupDetails)) {
      return FALSE;
    }

    $applicantReviewId = ApplicantReviewHelper::getActivityTypeId();
    $isOfApplicantReview = FALSE;
    foreach ($groupDetails as $groupDetail) {
      $groupExtendsActivity = $groupDetail['extends'] == 'Activity';
      if (!$groupExtendsActivity) {
        return FALSE;
      }
      if (!empty($groupDetail['extends_entity_column_value'])) {
        // Custom Group should only extend the Applicant review activity type.
        $isOfApplicantReview = in_array($applicantReviewId, $groupDetail['extends_entity_column_value'])
          && count($groupDetail['extends_entity_column_value']) == 1;
      }
    }

    if ($isOfApplicantReview) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Returns the custom group details group id or name.
   *
   * @param mixed $customGroup
   *   Custom group name or Id.
   *
   * @return array
   *   Case category name.
   */
  private function getCustomGroupDetails($customGroup) {
    $params = [];
    if (is_array($customGroup)) {
      if (array_key_exists('IN', $customGroup)) {
        is_numeric($customGroup['IN'][0]) ? ($params['id'] = $customGroup) : ($params['name'] = $customGroup);
      }
    }
    else {
      is_numeric($customGroup) ? ($params['id'] = $customGroup) : ($params['name'] = $customGroup);
    }
    if (empty($params)) {
      return [];
    }

    $result = civicrm_api3('CustomGroup', 'get', $params);

    if (!empty($result['values'])) {
      return $result['values'];
    }

    return [];
  }

}
