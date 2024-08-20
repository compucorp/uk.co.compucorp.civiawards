<?php

/**
 * @file
 * Declares an Angular module which can be autoloaded in CiviCRM.
 *
 * See also:
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules.
 */

use CRM_Civicase_Helper_GlobRecursive as GlobRecursive;

/**
 * Get a list of JS files.
 */
function get_awards_js_files() {
  return array_merge(
    ['ang/civiawards.js'],
    GlobRecursive::getRelativeToExtension(
      'uk.co.compucorp.civiawards',
      'ang/civiawards/*.js'
    )
  );
}

return [
  'js' => get_awards_js_files(),
  'css' => [
    0 => 'css/civiawards.min.css',
  ],
  'requires' => [
    'api4',
    'crmUi',
    'crmUtil',
    'ngRoute',
    'dialogService',
    'civiawards-base',
  ],
  'partials' => [
    0 => 'ang/civiawards',
  ],
  'permissions' => [
    'create/edit awards',
    'administer CiviCase',
  ],
];
