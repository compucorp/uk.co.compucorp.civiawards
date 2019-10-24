<?php

use CRM_CiviAwards_BAO_AwardManager as AwardManager;

/**
 * Class CRM_CiviAwards_Api_Wrapper_AwardDetailPseudoFields.
 */
class CRM_CiviAwards_Api_Wrapper_AwardDetailExtraFields implements API_Wrapper {

  /**
   * {@inheritdoc}
   */
  public function fromApiInput($apiRequest) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $this->setRequiredReturnParameters($apiRequest);
    }

    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $this->addAwardManagerDetails($apiRequest, $result['values']);
    }

    return $result;
  }

  /**
   * Checks whether the request can be handled or not.
   *
   * @param array $apiRequest
   *   API Request.
   *
   * @return bool
   *   If request can be handled.
   */
  private function canHandleTheRequest(array $apiRequest) {
    return $apiRequest['entity'] === 'AwardDetail' && in_array($apiRequest['action'], [
        'get',
        'create',
      ]);
  }

  /**
   * Add award manager details to the results if required criteria is met.
   *
   * @param array $apiRequest
   *   API request details.
   * @param array $result
   *   API Result details.
   */
  private function addAwardManagerDetails(array $apiRequest, array &$result) {
    if (!in_array('award_manager', $apiRequest['params']['pseudo_fields'])) {
      return;
    }

    foreach ($result as $key => $value) {
      $awardManagers = $this->getAwardManagersForCaseType($value['case_type_id']);
      $result[$key]['award_manager'] = $awardManagers;
    }
  }

  /**
   * Returns Award managers for a case type.
   *
   * @param int $caseTypeId
   *   Case Type ID.
   *
   * @return array
   *   Award Managers for the case type.
   */
  private function getAwardManagersForCaseType($caseTypeId) {
    $awardManagers = [];
    $awardManagerObject = new AwardManager();
    $awardManagerObject->case_type_id = $caseTypeId;
    $awardManagerObject->find();

    while ($awardManagerObject->fetch()) {
      $awardManagers[] = $awardManagerObject->contact_id;
    }

    return $awardManagers;
  }

  /**
   * Set required return parameters.
   *
   * This function basically sets the case type id as a return parameter
   * if award detail extra fields are already part of the return parameters
   * or if all fields are to be returned (i.e return parameters is empty).
   *
   * The Case type Id is needed to add the extra field details when the
   * API results are returned.
   *
   * @param array $apiRequest
   *   API request.
   */
  private function setRequiredReturnParameters(array &$apiRequest) {
    $options = _civicrm_api3_get_options_from_params($apiRequest['params']);
    $returnParams = array_keys($options['return']);
    $reverseReturnParams = array_flip($returnParams);
    $pseudoFields = ['award_manager'];

    if (!empty($returnParams) && empty(array_intersect($reverseReturnParams, $pseudoFields))) {
      return;
    }

    foreach ($pseudoFields as $pseudoField) {
      $apiRequest['params']['pseudo_fields'][] = $pseudoField;
    }

    if (!empty($returnParams)) {
      $returnParams[] = 'case_type_id';
      $apiRequest['params']['return'] = $returnParams;
    }
  }

}
