<?php

/**
 * @file
 * Declares an Angular module which can be autoloaded in CiviCRM.
 *
 * See also:
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules.
 */

use CRM_Civicase_Helper_GlobRecursive as GlobRecursive;
use CRM_Civicase_Helper_OptionValues as OptionValuesHelper;

$options = [
  'awardSubtypes' => 'civiawards_award_subtype',
];

OptionValuesHelper::setToJsVariables($options);
expose_permissions();

/**
 * Exposes the awards permissions values on the `CRM.permissions` object.
 */
function expose_permissions() {
  Civi::resources()
    ->addPermissions([
      'create/edit awards',
    ]);
}

/**
 * Get a list of JS files.
 */
function get_awards_js_files() {
  return array_merge(
    ['ang/civiawards.js'], GlobRecursive::get(dirname(__FILE__) . '/civiawards/*.js')
  );
}

return [
  'js' => get_awards_js_files(),
  'css' => [
    0 => 'css/civiawards.min.css',
  ],
  'settings' => $options,
  'requires' => [
    'crmUi',
    'crmUtil',
    'ngRoute',
    'dialogService',
    'civicase-base',
  ],
  'partials' => [
    0 => 'ang/civiawards',
  ],
];
