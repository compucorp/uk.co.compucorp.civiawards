<?php

/**
 * @file
 * Extension file.
 */

use CRM_Civicase_Service_CaseCategoryPermission as CaseCategoryPermission;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryhelper;

require_once 'civiawards.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function civiawards_civicrm_config(&$config) {
  _civiawards_civix_civicrm_config($config);
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
 * Implements hook_civicrm_thems().
 */
function civiawards_civicrm_themes(&$themes) {
  _civiawards_civix_civicrm_themes($themes);
}

/**
 * Implements hook_civicrm_permission().
 */
function civiawards_civicrm_permission(&$permissions) {
  $permissionService = new CaseCategoryPermission();
  $caseCategoryPermissions = $permissionService->get(CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME);

  // Add basic permissions set from civicase extension.
  foreach ($caseCategoryPermissions as $caseCategoryPermission) {
    $permissions[$caseCategoryPermission['name']] = [
      $caseCategoryPermission['label'],
      ts($caseCategoryPermission['description']),
    ];
  }

  // Add permission defined by this extension.
  $permissions['access awards panel portal'] = [
    'CiviAwards: Access awards panel portal',
    ts('Allows a user to access the awards panel portal'),
  ];

  $permissions['access applicant portal'] = [
    'CiviAwards: Access applicant portal',
    ts('Allows a user to access the awards applicant portal'),
  ];
}
