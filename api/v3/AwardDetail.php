<?php

/**
 * @file
 * Award Detail API file.
 */

/**
 * AwardDetail.create API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_detail_create_spec(array &$spec) {
  $spec['award_manager'] = [
    'title' => 'Award manager',
    'description' => 'An array of Contact IDs',
    'type' => CRM_Utils_Type::T_STRING,
  ];
}

/**
 * AwardDetail.create API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_detail_create(array $params) {
  $params['award_manager'] = getParameterValue($params, 'award_manager');
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AwardDetail.delete API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_detail_delete(array $params) {
  $awardDetailService = new CRM_CiviAwards_Service_AwardDetail();
  $awardDetailService->deleteDependencies($params['id']);

  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AwardDetail.get API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_detail_get(array $params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Gets the parameter value from $params array.
 *
 * This function is useful when we want the parameter to not support
 * any SQL operator, i.e we expect a single value or an array of values to
 * be passed in for the parameter.
 *
 * @param array $params
 *   API parameters.
 * @param string $parameterName
 *   Parameter name.
 *
 * @return array
 *   The parameter value.
 */
function getParameterValue(array $params, $parameterName) {
  if (empty($params[$parameterName])) {
    return [];
  }

  if (!is_array($params[$parameterName])) {
    return [$params[$parameterName]];
  }

  $acceptedSQLOperators = CRM_Core_DAO::acceptedSQLOperators();
  if (array_intersect($acceptedSQLOperators, array_keys($params[$parameterName]))) {
    throw new InvalidArgumentException("No SQL operators allowed for {$parameterName}");
  }

  return $params[$parameterName];
}
