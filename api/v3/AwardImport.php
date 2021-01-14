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

  $spec['title'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => ts('Award Title'),
    'description' => ts('Natural language name for the Award'),
  ];

  $spec['description'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => ts('Description'),
    'description' => ts('Description of the Award'),
  ];

  $spec['is_active'] = [
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'title' => ts('Enabled?'),
    'description' => ts('Is this Award enabled?'),
  ];

  $spec['award_subtype'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('Award Subtype'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('One of the values of the award_subtype option group'),
  ];

  $spec['start_date'] = [
    'type' => CRM_Utils_Type::T_DATE,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('Start Date'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('Award Start Date'),
  ];

  $spec['end_date'] = [
    'type' => CRM_Utils_Type::T_DATE,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('End Date'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('Award End Date'),
  ];

  $spec['is_template'] = [
    'type' => CRM_Utils_Type::T_BOOLEAN,
    'title' => CRM_CiviAwards_ExtensionUtil::ts('Is Template?'),
    'description' => CRM_CiviAwards_ExtensionUtil::ts('Whether the award detail is for a template or not'),
  ];

  $spec['award_manager'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Award manager',
    'description' => 'A comma-separated list of Contact IDs',
  ];

  $spec['activity_types'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Award activity types',
    'description' => 'A two-dimensional array of activity types. Example [{"name": "Applicant Review"}, {"name": "Email"}]',
  ];

  $spec['statuses'] = [
    'type' => CRM_Utils_Type::T_STRING,
    'title' => 'Award statuses',
    'description' => 'A two-dimensional array of Award statuses. Example ["won", "lost"]',
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
