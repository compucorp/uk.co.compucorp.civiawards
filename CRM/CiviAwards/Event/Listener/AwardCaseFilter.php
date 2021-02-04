<?php

use Civi\API\Event\PrepareEvent;

/**
 * Award filter while fetching cases.
 */
class CRM_CiviAwards_Event_Listener_AwardCaseFilter {

  /**
   * Fetches case by the given award filters.
   *
   * @param \Civi\API\Event\PrepareEvent $event
   *   API Prepare Event Object.
   */
  public static function onPrepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] != 3) {
      return;
    }

    if (!self::shouldRun($apiRequest)) {
      return;
    }

    $caseTypeParams = [];
    $managedByParam = $apiRequest['params']['case_type_id.managed_by'];
    $awardDetailParams = $apiRequest['params']['case_type_id.award_detail_params'];

    foreach ($apiRequest['params'] as $param => $value) {
      if ($param === 'case_type_id.managed_by' || $param === 'case_type_id.award_detail_params') {
        unset($apiRequest['params'][$param]);
      }
      elseif (strpos($param, 'case_type_id.') === 0) {
        unset($apiRequest['params'][$param]);
        $caseTypeParams[substr($param, strlen('case_type_id.'))] = $value;
      }
    }

    $filteredAwardTypes = civicrm_api3('Award', 'get', [
      'managed_by' => $managedByParam,
      'award_detail_params' => $awardDetailParams,
      'case_type_params' => array_merge($caseTypeParams, [
        'sequential' => 1,
        'options' => ['limit' => 0],
        'return' => ['id'],
      ]),
    ])['values'];

    $apiRequest['params']['case_type_id'] = ['IN' => array_column($filteredAwardTypes, 'id')];

    $event->setApiRequest($apiRequest);
  }

  /**
   * Determines if the processing will run.
   *
   * @param array $apiRequest
   *   Api request data.
   *
   * @return bool
   *   TRUE if processing should run, FALSE otherwise.
   */
  protected static function shouldRun(array $apiRequest) {
    return $apiRequest['entity'] == 'Case' &&
      $apiRequest['action'] == 'getdetails' &&
      ($apiRequest['params']['case_type_id.managed_by'] || $apiRequest['params']['case_type_id.award_detail_params']);
  }

}
