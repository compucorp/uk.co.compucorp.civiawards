<?php

/**
 * @file
 * Award Detail API file.
 */

/**
 * Award.get API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_get_spec(array &$spec) {
  $spec['managed_by'] = [
    'title' => 'Award Manager',
    'description' => 'Award Manager Contact ID',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['award_detail_params'] = [
    'title' => 'Params for AwardDetail.Get API',
    'description' => 'Array of parameters for AwardDetail.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['case_type_params'] = [
    'title' => 'Params for CaseType.Get API',
    'description' => 'Array of parameters for CaseType.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Award.get API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_get(array $params) {
  $awardsFilteredByManager = [];
  if ($params['managed_by']) {
    $awardsFilteredByManager = civicrm_api3('AwardManager', 'get', [
      'sequential' => 1,
      'contact_id' => $params['managed_by'],
      'options' => ['limit' => 0],
    ]);

    $awardsFilteredByManager = array_column($awardsFilteredByManager['values'], 'case_type_id');

    if (count($awardsFilteredByManager) === 0) {
      return civicrm_api3_create_success([]);
    }
  }

  if ($params['award_detail_params']) {
    $detailsParams = array_merge($params['award_detail_params'], [
      'sequential' => 1,
      'options' => ['limit' => 0],
    ]);

    if (count($awardsFilteredByManager) > 0) {
      $detailsParams['case_type_id'] = ['IN' => $awardsFilteredByManager];
    }

    $awardsFilteredByDetails = civicrm_api3('AwardDetail', 'get', $detailsParams);

    $awardsFilteredByDetails = array_column($awardsFilteredByDetails['values'], 'case_type_id');

    if (count($awardsFilteredByDetails) === 0) {
      return civicrm_api3_create_success([]);
    }
  }
  else {
    $awardsFilteredByDetails = $awardsFilteredByManager;
  }

  $params['case_type_params'] = empty($params['case_type_params']) ? [] : $params['case_type_params'];
  if (count($awardsFilteredByDetails) > 0) {
    $params['case_type_params']['id'] = ['IN' => $awardsFilteredByDetails];
  }

  $awardsFilteredByCaseType = civicrm_api3('CaseType', 'get', $params['case_type_params']);

  return $awardsFilteredByCaseType;
}

/**
 * Award.getcount API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_getcount_spec(array &$spec) {
  $spec['managed_by'] = [
    'title' => 'Award Manager',
    'description' => 'Award Manager Contact ID',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['award_detail_params'] = [
    'title' => 'Params for AwardDetail.Get API',
    'description' => 'Array of parameters for AwardDetail.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];

  $spec['case_type_params'] = [
    'title' => 'Params for CaseType.Get API',
    'description' => 'Array of parameters for CaseType.Get API',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * Award.getcount API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_getcount(array $params) {
  $params['case_type_params']['options']['limit'] = 0;
  $params['case_type_params']['options']['offset'] = 0;

  return count(civicrm_api3_award_get($params)['values']);
}
