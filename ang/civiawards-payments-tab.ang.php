<?php

/**
 * @file
 * Declares an Angular module which can be autoloaded in CiviCRM.
 *
 * See also:
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules.
 */

use CRM_Civicase_Helper_GlobRecursive as GlobRecursive;
use CRM_CiviAwards_Service_FinanceManagementSetting as FinanceManagementSettingService;

$financeManagementSettingService = new FinanceManagementSettingService();

/**
 * Get a list of JS files.
 */
function get_awards_payments_tab_js_files() {
  return array_merge(
    ['ang/civiawards-payments-tab.js'],
    GlobRecursive::get(dirname(__FILE__) . '/civiawards-payments-tab/*.js')
  );
}

return [
  'js' => get_awards_payments_tab_js_files(),
  'requires' => [
    'civiawards-base',
  ],
  'partials' => [
    0 => 'ang/civiawards-payments-tab',
  ],
  'settings' => [
    'instances_finance_support' => $financeManagementSettingService->get(),
  ],
];
