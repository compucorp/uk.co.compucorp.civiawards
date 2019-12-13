<?php

/**
 * @file
 * Award Review Panel API file.
 */

/**
 * AwardReviewPanel.create API.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_award_review_panel_create(array $params) {
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AwardReviewPanel.delete API.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_award_review_panel_delete(array $params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * AwardReviewPanel.get API.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_award_review_panel_get(array $params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}
