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

    $params['case_type_category'] = $this->getCaseCategoryValueForAwards();
    $params['definition'] = $this->createDefinition($params);
    try {
      $caseType = civicrm_api3('CaseType', 'create', $params);
    }
    catch (Exception $exception) {
      $tx->rollback();

      throw new API_Exception('Exception while saving the Case Type for Award: ' . $exception->getMessage());
    }
    $caseTypeId = array_shift($caseType['values'])['id'];

    $params['case_type_id'] = $caseTypeId;
    $params['award_manager'] = !empty($params['award_manager']) ? json_decode($params['award_manager'], TRUE) : [];
    $params['review_fields'] = !empty($params['review_fields']) ? json_decode($params['review_fields'], TRUE) : [];
    try {
      civicrm_api3('AwardDetail', 'create', $params);
    }
    catch (Exception $exception) {
      $tx->rollback();

      throw new API_Exception('Exception while saving the AwardDetail: ' . $exception->getMessage());
    }

    if (!empty($params['review_panel_title'])) {
      try {
        civicrm_api3('AwardReviewPanel', 'create', [
          'case_type_id' => $caseTypeId,
          'title' => $params['review_panel_title'],
          'is_active' => $params['review_panel_is_active'] ?? 1,
          'visibility_settings' => [
            'application_status' => [],
            'anonymize_application' => '1',
            'application_tags' => [],
            'is_application_status_restricted' => '0',
            'restricted_application_status' => [],
          ],
          'contact_settings' => [
            'exclude_groups' => [],
            'include_groups' => [],
            'relationship' => [],
          ],
        ]);
      }
      catch (Exception $exception) {
        $tx->rollback();

        throw new API_Exception('Exception while saving the AwardReviewPanel: ' . $exception->getMessage());
      }
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
