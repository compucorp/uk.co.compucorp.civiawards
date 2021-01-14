<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;

/**
 * Utility class for importing Awards using CSV importer extension.
 */
class CRM_CiviAwards_Service_AwardImport {

  /**
   * Create an Award and the related entities.
   *
   * @param array $params
   *   Information for creating the award and related entities.
   */
  public function create(array $params) {
    $tx = new CRM_Core_Transaction();

    $params['case_type_category'] = 'awards';
    $params['definition'] = $this->createDefinition($params);
    try {
      $caseType = civicrm_api3('CaseType', 'create', $params);
    }
    catch (Exception $exception) {
      $tx->rollback();

      throw new API_Exception('Exception while saving the Case Type for Award: ' . $exception->getMessage());
    }

    $params['case_type_id'] = $caseType['id'];
    $params['award_manager'] = !empty($params['award_manager']) ? explode(',', $params['award_manager']) : [];
    $params['review_fields'] = [];
    try {
      civicrm_api3('AwardDetail', 'create', $params);
    }
    catch (Exception $exception) {
      $tx->rollback();

      throw new API_Exception('Exception while saving the AwardDetail: ' . $exception->getMessage());
    }

    return TRUE;
  }

  /**
   * Get the case category value for Awards.
   *
   * @return int
   *   The case category id for Awards.
   */
  public function getCaseCategoryValueForAwards() {
    $caseCategories = CRM_Core_OptionGroup::values('case_type_categories', TRUE, FALSE, TRUE, NULL, 'name');
    return $caseCategories[CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME];
  }

  /**
   * Create the definition for the case type.
   *
   * Makes an array with all the information required for the definition field
   * on the case type.
   *
   * @param array $params
   *   Information for creating the Award.
   *
   * @return array
   *   Array with details for the definition field.
   */
  private function createDefinition(array $params) {
    if (!isset($params['activity_types'])) {
      $params['activity_types'] = [
        ['name' => 'Applicant Review'],
        ['name' => 'Email'],
        ['name' => 'Follow up'],
        ['name' => 'Meeting'],
        ['name' => 'Phone Call'],
      ];
    }
    else {
      $params['activity_types'] = json_decode($params['activity_types'], TRUE);
    }

    if (!isset($params['statuses'])) {
      $params['statuses'] = [];
    }
    else {
      $params['statuses'] = json_decode($params['statuses'], TRUE);
    }

    $params['caseRoles'] = [
      ['name' => 'Application Manager'],
    ];

    return [
      'activityTypes' => $params['activity_types'],
      'caseRoles' => $params['caseRoles'],
      'statuses' => $params['statuses'],
    ];
  }

}
