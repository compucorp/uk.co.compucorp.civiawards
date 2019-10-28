<?php

/**
 * @file
 * Declares an Angular module which can be autoloaded in CiviCRM.
 *
 * See also:
 * http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules.
 */

$options = [
  'awardTypes' => 'civiawards_award_type',
];

set_option_values_to_js_vars($options);

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
  'settings' => $options,
  'requires' => [
    'ngRoute',
    'civicase-base',
  ],
  'partials' => [
    0 => 'ang/civiawards',
  ],
];
