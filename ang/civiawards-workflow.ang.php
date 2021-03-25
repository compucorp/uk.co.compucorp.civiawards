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
function get_awards_workflow_js_files() {
  return array_merge(
    ['ang/civiawards-workflow.js'],
    GlobRecursive::getRelativeToExtension(
      'uk.co.compucorp.civiawards',
      'ang/civiawards-workflow/*.js'
    )
  );
}

return [
  'js' => get_awards_workflow_js_files(),
  'requires' => [
    'civiawards-base',
  ],
  'partials' => [
    0 => 'ang/civiawards-workflow',
  ],
];
