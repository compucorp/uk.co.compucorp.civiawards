<?php

/**
 * Class DeleteApplicantReviewCustomField.
 */
class CRM_CiviAwards_Setup_DeleteApplicantReviewCustomField {

  /**
   * Deletes the Applicant review custom group set.
   */
  public function apply() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'name' => 'Applicant_Review',
    ]);

    if (empty($result['id'])) {
      return;
    }

    civicrm_api3('CustomGroup', 'delete', [
      'id' => $result['id'],
    ]);
  }

}
