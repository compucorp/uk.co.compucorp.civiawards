<?php

/**
 * @file
 * Award Import API file.
 */

use CRM_CiviAwards_Service_AwardImport as AwardImportService;

/**
 * AwardImport.create API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_import_create_spec(array &$spec) {
  $caseTypeFields = civicrm_api3('CaseType', 'getfields', ['api_action' => 'get']);
  $awardDetailFields = civicrm_api3('AwardDetail', 'getfields', ['api_action' => 'get']);
  $spec = array_merge($caseTypeFields['values'], $awardDetailFields['values']);

  $spec['is_template'] = [
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('Is Template?'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('Whether the award detail is for a template or not'),
  ];

  $spec['award_manager'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Award Manager',
    'description' => 'A comma-separated list of Contact IDs',
  ];

  $spec['award_subtype'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('Award Subtype'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('One of the values of the award_subtype option group'),
  ];
}

/**
 * AwardImport.create API.
 *
 * @param array $params
 *   API parameters.
 */
function civicrm_api3_award_import_create(array $params) {
  (new AwardImportService())->create($params);
}
