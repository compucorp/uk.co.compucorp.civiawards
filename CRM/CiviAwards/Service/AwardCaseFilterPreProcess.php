<?php

/**
 * Award case filter pre-process class.
 */
class CRM_CiviAwards_Service_AwardCaseFilterPreProcess {

  /**
   * Function that handles pre-processing for a case details relating to awards.
   *
   * @param array $requestParams
   *   API request parameters.
   */
  public function onCreate(array &$requestParams) {
    $caseTypeParams = [];
    $managedByParam = $requestParams['case_type_id.managed_by'];
    $awardDetailParams = $requestParams['case_type_id.award_detail_params'];

    foreach ($requestParams as $param => $value) {
      if ($param === 'case_type_id.managed_by' || $param === 'case_type_id.award_detail_params') {
        unset($requestParams[$param]);
      }
      elseif (strpos($param, 'case_type_id.') === 0) {
        unset($requestParams[$param]);
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

    $requestParams['case_type_id'] = ['IN' => array_column($filteredAwardTypes, 'id')];
  }

}
