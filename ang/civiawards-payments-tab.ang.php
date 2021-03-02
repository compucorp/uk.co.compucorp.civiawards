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
use CRM_Civicase_Helper_OptionValues as OptionValuesHelper;

$financeManagementSettingService = new FinanceManagementSettingService();
$options = [
  'payment_types' => 'budget_line_type',
];

OptionValuesHelper::setToJsVariables($options);

$options['instances_finance_support'] = $financeManagementSettingService->get();

/**
 * Get a list of JS files.
 */
function get_awards_payments_tab_js_files() {
  return array_merge(
    ['ang/civiawards-payments-tab.js'],
    GlobRecursive::getRelativeToExtension(
      'uk.co.compucorp.civiawards',
      'ang/civiawards-payments-tab/*.js'
    )
  );
}

return [
  'js' => get_awards_payments_tab_js_files(),
  'settings' => $options,
  'requires' => [
    'civiawards-base',
  ],
  'partials' => [
    0 => 'ang/civiawards-payments-tab',
  ],
];
