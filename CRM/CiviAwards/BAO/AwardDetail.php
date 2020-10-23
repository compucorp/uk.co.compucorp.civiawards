<?php

use CRM_CiviAwards_Service_AwardDetail as AwardDetailService;

/**
 * AwardDetail Business logic class.
 */
class CRM_CiviAwards_BAO_AwardDetail extends CRM_CiviAwards_DAO_AwardDetail {

  /**
   * The award detail object before it gets updated.
   *
   * @var CRM_CiviAwards_BAO_AwardDetail
   */
  public $oldAwardDetail;

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
    // We don't want this to be updated via API.
    unset($params['profile_id']);
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    if ($hook == 'edit') {
      $instance->loadOldAwardDetail($params['id']);
    }
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
  private static function validateParams(array &$params) {
    self::validateDates($params);
    self::validateReviewFields($params);
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

  /**
   * Load an Award detail object.
   *
   * @param int $id
   *   The Id of the award detail.
   */
  private function loadOldAwardDetail($id) {
    $this->oldAwardDetail = self::findById($id);
  }

  /**
   * Validates review_field options to ensure legal options are passed.
   *
   * @param array $params
   *   Request Parameters.
   *
   * @throws \Exception
   */
  private static function validateReviewFields(array $params) {
    $reviewFields = CRM_Utils_Array::value('review_fields', $params);
    $reviewFieldsValidator = new CRM_CiviAwards_Helper_CreateReviewFieldsValidator();
    $reviewFieldsValidator->validate($reviewFields);
  }

}
