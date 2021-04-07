<?php

use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;

/**
 * Applicant Management Menu class.
 */
class CRM_CiviAwards_Service_ApplicantManagementMenu extends CRM_Civicase_Service_CaseCategoryMenu {

  /**
   * {@inheritDoc}
   */
  public function getSubmenus($caseTypeCategoryName, array $permissions = NULL) {
    $labelForMenu = ucfirst(strtolower($caseTypeCategoryName));
    $categoryId = civicrm_api3('OptionValue', 'getsingle', [
      'option_group_id' => 'case_type_categories',
      'name' => $caseTypeCategoryName,
      'return' => ['value'],
    ])['value'];
    if (!$permissions) {
      $permissions = (new CaseCategoryPermission())->get($caseTypeCategoryName);
    }

    return [
      [
        'label' => ts('Dashboard'),
        'name' => "{$caseTypeCategoryName}_dashboard",
        'url' => "/civicrm/case/a/?case_type_category={$categoryId}#/case?case_type_category={$categoryId}",
        'permission' => "{$permissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name']},{$permissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name']}",
        'permission_operator' => 'OR',
      ],
      [
        'label' => ts('Manage Applications'),
        'name' => "manage_{$caseTypeCategoryName}_applications",
        'url' => 'civicrm/case/a/?case_type_category=' . $categoryId . '#/case/list?cf={"case_type_category":"' . $categoryId . '"}',
        'permission' => "{$permissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name']},{$permissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name']}",
        'permission_operator' => 'OR',
        'has_separator' => 1,
      ],
      [
        'label' => ts("Manage " . $labelForMenu),
        'name' => "manage_{$caseTypeCategoryName}_workflows",
        'url' => 'civicrm/workflow/a?case_type_category=' . $categoryId . '#/list',
        'permission' => "{$permissions['ADMINISTER_CASE_CATEGORY']['name']}, administer CiviCRM",
        'permission_operator' => 'OR',
      ],
    ];
  }

}
