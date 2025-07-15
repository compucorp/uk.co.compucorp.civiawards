<?php

/**
 * @file
 * CiviAwards Extension.
 */

require_once 'civiawards.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function civiawards_civicrm_config(&$config) {
  _civiawards_civix_civicrm_config($config);

  Civi::dispatcher()->addListener(
    'civi.api.prepare',
    ['CRM_CiviAwards_Event_Listener_AwardCaseFilter', 'onPrepare'],
    10
  );

  Civi::dispatcher()->addListener(
    'civi.api.authorize',
    ['CRM_CiviAwards_Event_Listener_AlterOptionValuePermission', 'authorize'],
    10
  );
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function civiawards_civicrm_install() {
  _civiawards_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function civiawards_civicrm_enable() {
  _civiawards_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_buildForm().
 */
function civiawards_civicrm_buildForm($formName, &$form) {
  $hooks = [
    new CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues(),
    new CRM_CiviAwards_Hook_BuildForm_AddFinanceManagementField(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($form, $formName);
  }
}

/**
 * Implements hook_civicrm_buildForm().
 */
function civiawards_civicrm_postProcess($formName, &$form) {
  $hooks = [
    new CRM_CiviAwards_Hook_PostProcess_SaveFinanceManagement(),
    new CRM_CiviAwards_Hook_PostProcess_EnableBankDetailsFieldSet(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($form, $formName);
  }
}

/**
 * Implements hook_civicrm_apiWrappers().
 */
function civiawards_civicrm_apiWrappers(&$wrappers, $apiRequest) {
  if ($apiRequest['entity'] == 'AwardDetail') {
    $wrappers[] = new CRM_CiviAwards_Api_Wrapper_AwardDetailExtraFields();
  }
  if ($apiRequest['entity'] == 'AwardReviewPanel') {
    $wrappers[] = new CRM_CiviAwards_Api_Wrapper_AwardReviewPanelFormatSettingFields();
  }
}

/**
 * Implements hook_civicrm_permission().
 */
function civiawards_civicrm_permission(&$permissions) {
  // Add permission defined by this extension.
  $permissions['access awards panel portal'] = [
    'label' => ts('CiviAwards: Access awards panel portal'),
    'description' => ts('Allows a user to access the awards panel portal'),
  ];

  $permissions['access applicant portal'] = [
    'label' => ts('CiviAwards: Access applicant portal'),
    'description' => ts('Allows a user to access the awards applicant portal'),
  ];

  $permissions['create/edit awards'] = [
    'label' => ts('CiviAwards: Create/Edit awards'),
    'description' => ts('Allows a user to create or edit awards'),
  ];

  $permissions[CRM_CiviAwards_Hook_AlterAPIPermissions_Award::REVIEW_FIELD_SET_PERM] = [
    'label' => ts('CiviAwards: Access review fields '),
    'description' => ts(
      "This allows the user to view any review field sets on the reserved review activity type.
       Note that this can also be done through ACLs or allocating the user 'Access all custom data' permission"
    ),
  ];

  $permissions[CRM_CiviAwards_Hook_AlterAPIPermissions_Award::PAYMENT_FIELD_SET_PERM] = [
    'label' => ts('CiviAwards: Access Payment custom fields '),
    'description' => ts(
      "This allows the user to view any payment field sets on the related payment activity types.
       Note that this can also be done through ACLs or allocating the user 'Access all custom data' permission"
    ),
  ];
}

/**
 * Implements hook_civicrm_alterAPIPermissions().
 *
 * @link https://docs.civicrm.org/dev/en/master/hooks/hook_civicrm_alterAPIPermissions/
 */
function civiawards_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $hooks = [
    new CRM_CiviAwards_Hook_AlterAPIPermissions_Award(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($entity, $action, $params, $permissions);
  }
}

/**
 * Implements hook_civicrm_aclGroup().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_aclGroup/
 */
function civiawards_civicrm_aclGroup($type, $contactID, $tableName, &$allGroups, &$currentGroups) {
  $hooks = [
    new CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroups(),
    new CRM_CiviAwards_Hook_AclGroup_AllowAccessToPaymentGroups(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($type, $contactID, $tableName, $allGroups, $currentGroups);
  }
}

/**
 * Implements hook_civicrm_post().
 */
function civiawards_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  $hooks = [
    new CRM_CiviAwards_Hook_Post_UpdateCaseTypeListForCustomGroup(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($op, $objectName, $objectId, $objectRef);
  }
}

/**
 * Implements addCiviCaseDependentAngularModules().
 */
function civiawards_addCiviCaseDependentAngularModules(&$dependentModules) {
  $dependentModules[] = "civiawards";

  $hooks = [
    new CRM_CiviAwardsPaymentsTab_Hook_AddCiviCaseDependentAngularModules_PaymentsTabModule(),
  ];

  foreach ($hooks as $hook) {
    $hook->run($dependentModules);
  }
}

/**
 * Implements addWorkflowDependentAngularModules().
 */
function civiawards_addWorkflowDependentAngularModules(&$dependentModules) {
  $dependentModules[] = "civiawards-workflow";
}

/**
 * Implements hook_civicrm_alterMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterMenu
 */
function civiawards_civicrm_alterMenu(&$items) {
  $items['civicrm/awardreview']['ids_arguments']['json'][] = 'civicase_reload';
}
