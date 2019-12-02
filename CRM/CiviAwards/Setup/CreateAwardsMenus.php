<?php

/**
 * Creates the menu items for Civi Awards.
 */
class CRM_CiviAwards_Setup_CreateAwardsMenus {

  /**
   * Creates the Awards menu items.
   *
   * @return bool
   *   returns true when the menu items have been created successfully.
   */
  public function apply() {
    $this->createMenuItems();

    return TRUE;
  }

  /**
   * Creates Awards Main menu.
   */
  private function createMenuItems() {
    $result = civicrm_api3('Navigation', 'get', ['name' => 'awards']);

    if ($result['count'] > 0) {
      return;
    }

    $casesWeight = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Navigation',
      'Cases',
      'weight',
      'name'
    );

    $params = [
      'label' => ts('Awards'),
      'name' => 'awards',
      'url' => NULL,
      'permission_operator' => 'OR',
      'is_active' => 1,
      'permission' => 'access my awards and activities,access all awards and activities',
      'icon' => 'crm-i fa-folder-open-o',
    ];
    $menu = civicrm_api3('Navigation', 'create', $params);

    civicrm_api3('Navigation', 'create', [
      'id' => $menu['id'],
      'weight' => $casesWeight + 1,
    ]);
    $this->createSubmenus($menu['id']);
  }

  /**
   * Creates sub menu items.
   *
   * @param int $parentMenuId
   *   The id for the parent menu containing the sub menu items.
   */
  private function createSubmenus($parentMenuId) {
    $submenus = [
      [
        'label' => ts('Dashboard'),
        'name' => 'awards_dashboard',
        'url' => '/civicrm/case/a/?case_type_category=awards#/case?case_type_category=awards',
        'permission' => 'access my awards and activities,access all awards and activities',
        'permission_operator' => 'OR',
      ],
      [
        'label' => ts('New Award'),
        'name' => 'new_award',
        'url' => 'civicrm/a/#/awards/new',
        'permission' => 'add awards,access all awards and activities',
        'permission_operator' => 'OR',
      ],
      [
        'label' => ts('Manage Applications'),
        'name' => 'manage_awards_applications',
        'url' => 'civicrm/case/a/?case_type_category=awards#/case/list?cf=%7B%22case_type_category%22:%22awards%22%7D',
        'permission' => 'access my awards and activities,access all awards and activities',
        'permission_operator' => 'OR',
      ],
    ];

    foreach ($submenus as $i => $item) {
      $item['weight'] = $i;
      $item['parent_id'] = $parentMenuId;
      $item['is_active'] = 1;

      civicrm_api3('Navigation', 'create', $item);
    }
  }

}
