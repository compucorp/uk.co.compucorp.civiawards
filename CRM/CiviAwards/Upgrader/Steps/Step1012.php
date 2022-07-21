<?php

use CRM_CiviAwards_Setup_CreateApplicationReviewerRelationship as CreateApplicationReviewerRelationship;

/**
 * Creates the application reviewer relationship.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1012 {

  /**
   * Creates the application reviewer relationship.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    (new CreateApplicationReviewerRelationship())->apply();
    $this->configDraftActivityStatus();

    return TRUE;
  }

  /**
   * Adds Applicant Review category to activity draft status.
   */
  private function configDraftActivityStatus() {
    $value = civicrm_api3('OptionValue', 'getsingle', [
      'option_group_id' => "activity_status",
      'name' => "Draft",
    ]);

    $applicantReview = 'Applicant Review';
    $grouping = explode(", ", $value['grouping']);
    if (in_array($applicantReview, $grouping)) {
      return;
    }

    $grouping[count($grouping) + 1] = $applicantReview;

    civicrm_api3('OptionValue', 'create', [
      'id' => $value['id'],
      'grouping' => implode(", ", $grouping),
      'is_reserved' => TRUE,
    ]);
  }

}
