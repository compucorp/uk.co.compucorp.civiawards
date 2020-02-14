<?php

use CRM_CiviAwards_Helper_ApplicantReview as ApplicantReviewHelper;

/**
 * Class CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroups.
 */
class CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroups {

  /**
   * Determines if the hook should run or not.
   *
   * @param string $type
   *   The type of permission needed.
   * @param int $contactID
   *   The contactID for whom the check is made.
   * @param string $tableName
   *   The tableName which is being permissioned.
   * @param array $allGroups
   *   The set of all the objects for the above table.
   * @param array $currentGroups
   *   The set of objects that are currently permissioned for this contact.
   */
  public function run($type, $contactID, $tableName, array &$allGroups, array &$currentGroups) {
    if (!$this->shouldRun($tableName)) {
      return;
    }
    $applicantReviewCustomGroups = $this->getApplicantReviewCustomGroups();
    if (!$applicantReviewCustomGroups) {
      return;
    }

    foreach ($applicantReviewCustomGroups as $applicantReviewCustomGroup) {
      $currentGroups[] = $applicantReviewCustomGroup['id'];
    }

    $currentGroups = array_unique($currentGroups);
  }

  /**
   * Returns the Custom groups attached to the Applicant review activity type.
   */
  private function getApplicantReviewCustomGroups() {
    $applicantReviewCustomGroups = [];
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'extends' => 'Activity',
      'extends_entity_column_value' => ApplicantReviewHelper::getActivityTypeId(),
    ]);

    if (!empty($result['values'])) {
      $applicantReviewCustomGroups = $result['values'];
    }

    return $applicantReviewCustomGroups;
  }

  /**
   * Determines if the hook should run or not.
   *
   * @param string $tableName
   *   The tableName which is being permissioned.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($tableName) {
    return $tableName == CRM_Core_BAO_CustomGroup::getTableName() && CRM_Core_Permission::check('access review custom field set');
  }

}
