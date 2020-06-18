<?php

/**
 * @file
 * Award Review Panel API file.
 */

/**
 * AwardReviewPanel.getContactAccess API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_review_panel_getcontactaccess_spec(array &$spec) {
  $spec['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'Award Panel Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName'  => 'CRM_Contact_DAO_Contact',
    'FKApiName'    => 'Contact',
  ];

  $spec['award_id'] = [
    'title' => 'Award ID',
    'description' => 'Award ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName'  => 'CRM_Case_BAO_CaseType',
    'FKApiName'    => 'CaseType',
  ];
}

/**
 * AwardReviewPanel.getContactAccess API.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_award_review_panel_getcontactaccess(array $params) {
  $contactAccessService = new CRM_CiviAwards_Service_AwardApplicationContactAccess();
  $awardPanelContact = new CRM_CiviAwards_Service_AwardPanelContact();
  $contactAccess = $contactAccessService->get($params['contact_id'], $params['award_id'], $awardPanelContact);

  return civicrm_api3_create_success($contactAccess);
}

/**
 * AwardReviewPanel.getReviewAccess API specification.
 *
 * @param array $spec
 *   Description of fields supported by this API call.
 */
function _civicrm_api3_award_review_panel_getreviewaccess_spec(array &$spec) {
  $spec['contact_id'] = [
    'title' => 'Contact ID',
    'description' => 'Award Panel Contact ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName' => 'CRM_Contact_DAO_Contact',
    'FKApiName' => 'Contact',
  ];

  $spec['application_id'] = [
    'title' => 'Application ID',
    'description' => 'Application ID',
    'type' => CRM_Utils_Type::T_INT,
    'api.required' => 1,
    'FKClassName' => 'CRM_Case_BAO_Case',
    'FKApiName' => 'Case',
  ];
}

/**
 * AwardReviewPanel.getReviewAccess API.
 *
 * @param array $params
 *   Parameters.
 *
 * @return array
 *   API result descriptor.
 */
function civicrm_api3_award_review_panel_getreviewaccess(array $params) {
  $contactAccessService = new CRM_CiviAwards_Service_AwardApplicationContactAccess();
  $awardPanelContact = new CRM_CiviAwards_Service_AwardPanelContact();
  $contactAccess = $contactAccessService->getReviewAccess($params['contact_id'], $params['application_id'], $awardPanelContact);

  return civicrm_api3_create_success($contactAccess);
}

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
