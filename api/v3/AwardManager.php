<?php

/**
 * @file
 * Award Manager API file.
 */

/**
 * AwardManager.create API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_manager_create(array $params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AwardManager.delete API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_manager_delete(array $params) {
  $result = _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);

  return $result;
}

/**
 * AwardManager.get API.
 *
 * @param array $params
 *   API parameters.
 *
 * @return array
 *   API result descriptor
 */
function civicrm_api3_award_manager_get(array $params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
