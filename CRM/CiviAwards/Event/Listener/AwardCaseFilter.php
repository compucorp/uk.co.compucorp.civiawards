<?php

use Civi\API\Event\PrepareEvent;
use CRM_CiviAwards_Service_AwardCaseFilterPreProcess as AwardCaseFilterPreProcess;

/**
 * Award filter while fetching cases.
 */
class CRM_CiviAwards_Event_Listener_AwardCaseFilter {

  /**
   * Fetches cases by the given award filters.
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

    $awardCaseFilterPreProcess = new AwardCaseFilterPreProcess();
    $awardCaseFilterPreProcess->onCreate($apiRequest['params']);

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
      (!empty($apiRequest['params']['case_type_id.managed_by']) ||
        !empty($apiRequest['params']['case_type_id.award_detail_params']));
  }

}
