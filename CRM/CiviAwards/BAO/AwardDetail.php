<?php

use CRM_CiviAwards_Service_AwardDetail as AwardDetailService;

/**
 * Class CRM_CiviAwards_BAO_AwardDetail.
 */
class CRM_CiviAwards_BAO_AwardDetail extends CRM_CiviAwards_DAO_AwardDetail {

  /**
   * Create a new AwardDetail based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return CRM_CiviAwards_DAO_AwardDetail
   *   AwardDetail instance.
   */
  public static function create(array $params) {
    $entityName = 'AwardDetail';
    $hook = empty($params['id']) ? 'create' : 'edit';
    self::validateParams($params);

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);
    $instance->runAfterSave($hook, $params);
    return $instance;
  }

  /**
   * Runs after save actions for the entity using the Award detail service.
   *
   * @param string $hook
   *   Hook Value.
   * @param array $params
   *   Parameters.
   */
  private function runAfterSave($hook, array $params) {
    $awardDetailService = new AwardDetailService();
    if ($hook == 'create') {
      $awardDetailService->postCreateActions($this, $params);
    }
    else {
      $awardDetailService->postUpdateActions($this, $params);
    }
  }

  /**
   * Validation function.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateParams(array $params) {
    self::validateDates($params);
  }

  /**
   * Ensures that the start and end dates are validated.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateDates(array $params) {
    if (!empty($params['start_date']) && !empty($params['end_date'])) {
      $startDate = new DateTime($params['start_date']);
      $endDate = new DateTime($params['end_date']);

      if ($startDate > $endDate) {
        throw new Exception("Award Start Date must not be greater than the End Date");
      }
    }
  }

}
