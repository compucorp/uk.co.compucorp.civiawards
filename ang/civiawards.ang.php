<?php

/**
 * @file
 * Declares an Angular module which can be autoloaded in CiviCRM.
 *
 * See also:
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules.
 */

/**
 * Get a list of JS files.
 */
function get_awards_js_files() {
  return array_merge(
    ['ang/civiawards.js'], glob_recursive(dirname(__FILE__) . '/civiawards/*.js')
  );
}

return [
  'js' => get_awards_js_files(),
  'css' => [
    0 => 'css/civiawards.min.css',
  ],
  'requires' => [
    'ngRoute',
  ],
  'partials' => [
    0 => 'ang/civiawards',
  ],
];
