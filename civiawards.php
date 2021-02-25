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
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function civiawards_civicrm_xmlMenu(&$files) {
  _civiawards_civix_civicrm_xmlMenu($files);
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
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function civiawards_civicrm_postInstall() {
  _civiawards_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function civiawards_civicrm_uninstall() {
  _civiawards_civix_civicrm_uninstall();
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
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function civiawards_civicrm_disable() {
  _civiawards_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function civiawards_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civiawards_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function civiawards_civicrm_managed(&$entities) {
  _civiawards_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function civiawards_civicrm_caseTypes(&$caseTypes) {
  _civiawards_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function civiawards_civicrm_angularModules(&$angularModules) {
  _civiawards_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function civiawards_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civiawards_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function civiawards_civicrm_entityTypes(&$entityTypes) {
  _civiawards_civix_civicrm_entityTypes($entityTypes);
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
    ts('CiviAwards: Access awards panel portal'),
    ts('Allows a user to access the awards panel portal'),
  ];

  $permissions['access applicant portal'] = [
    ts('CiviAwards: Access applicant portal'),
    ts('Allows a user to access the awards applicant portal'),
  ];

  $permissions['create/edit awards'] = [
    ts('CiviAwards: Create/Edit awards'),
    ts('Allows a user to create or edit awards'),
  ];

  $permissions[CRM_CiviAwards_Hook_AlterAPIPermissions_Award::REVIEW_FIELD_SET_PERM] = [
    ts('CiviAwards: Access review fields '),
    ts(
      "This allows the user to view any review field sets on the reserved review activity type.
       Note that this can also be done through ACLs or allocating the user 'Access all custom data' permission"
    ),
  ];

  $permissions[CRM_CiviAwards_Hook_AlterAPIPermissions_Award::PAYMENT_FIELD_SET_PERM] = [
    ts('CiviAwards: Access Payment custom fields '),
    ts(
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
