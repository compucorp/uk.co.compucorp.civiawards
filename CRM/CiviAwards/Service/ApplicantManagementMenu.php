<?php

/**
 * Class CRM_CiviAwards_Service_ApplicantManagementMenu.
 */
class CRM_CiviAwards_Service_ApplicantManagementMenu extends CRM_Civicase_Service_CaseCategoryMenu {

  /**
   * Creates the Sub Menus for the Applicant Management Instance.
   *
   * @param string $caseTypeCategoryName
   *   Case category name.
   * @param array $permissions
   *   Permissions.
   * @param int $caseCategoryMenuId
   *   Menu ID.
   */
  protected function createCaseCategorySubmenus($caseTypeCategoryName, array $permissions, $caseCategoryMenuId) {
    $submenus = [
      [
        'label' => ts('Dashboard'),
        'name' => "{$caseTypeCategoryName}_dashboard",
        'url' => "/civicrm/case/a/?case_type_category={$caseTypeCategoryName}#/case?case_type_category={$caseTypeCategoryName}",
        'permission' => "{$permissions['ACCESS_MY_CASE_CATEGORY_AND_ACTIVITIES']['name']},{$permissions['ACCESS_CASE_CATEGORY_AND_ACTIVITIES']['name']}",
        'permission_operator' => 'OR',
      ],
      [
        'label' => ts('Manage Applications'),
        'name' => "manage_{$caseTypeCategoryName}_applications",
        'url' => 'civicrm/case/a/?case_type_category=' . $caseTypeCategoryName . '#/case/list?cf={"case_type_category":"' . $caseTypeCategoryName . '"}',
        'permission' => 'access my awards and activities,access all awards and activities',
        'permission_operator' => 'OR',
      ],
    ];

    foreach ($submenus as $i => $item) {
      $item['weight'] = $i;
      $item['parent_id'] = $caseCategoryMenuId;
      $item['is_active'] = 1;
      civicrm_api3('Navigation', 'create', $item);
    }
  }

}
