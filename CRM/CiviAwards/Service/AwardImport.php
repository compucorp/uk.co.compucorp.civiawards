<?php

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
    $this->validateParams($params);

    $tx = new CRM_Core_Transaction();

    $params['case_type_category'] = 'awards';
    $params['definition'] = $this->getDefaultDefinitionParams();
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
   * Returns the default definition params for the award.
   *
   * Makes an array with all the minimal default information required for
   * the definition field on the award.
   *
   * @return array
   *   Details for the definition field.
   */
  private function getDefaultDefinitionParams() {
    return [
      'activityTypes' => [
        ['name' => 'Applicant Review'],
        ['name' => 'Email'],
        ['name' => 'Follow up'],
        ['name' => 'Meeting'],
        ['name' => 'Phone Call'],
      ],
      'statuses' => [],
      'caseRoles' => [
        ['name' => 'Application Manager'],
      ],
    ];
  }

  /**
   * Validate information received.
   *
   * @param array $params
   *   Information for creating the award and related entities.
   */
  private function validateParams(array $params) {
    if (empty($params['title'])) {
      throw new API_Exception('Invalid param received: Award Title should not be empty');
    }
  }

}
