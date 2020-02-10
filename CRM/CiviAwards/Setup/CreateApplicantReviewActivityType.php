<?php

/**
 * Class CreateApplicantReviewActivityType.
 */
class CRM_CiviAwards_Setup_CreateApplicantReviewActivityType {

  const APPLICANT_REVIEW = 'Applicant Review';

  /**
   * Adds the Applicant review activity type and category.
   */
  public function apply() {
    $this->addApplicantReviewCategory();
    $this->addApplicantReviewActivityType();
    $this->setApplicantReviewActivityTypeStatuses();
  }

  /**
   * Adds the applicant review activity category.
   */
  private function addApplicantReviewCategory() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'activity_category',
      'name' => self::APPLICANT_REVIEW,
      'label' => self::APPLICANT_REVIEW,
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

  /**
   * Adds the applicant review activity type.
   */
  private function addApplicantReviewActivityType() {
    $civicaseComponent = CRM_Core_Component::get('CiviCase');
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'activity_type',
      'name' => self::APPLICANT_REVIEW,
      'label' => self::APPLICANT_REVIEW,
      'grouping' => self::APPLICANT_REVIEW,
      'icon' => 'fa-user',
      'component_id' => !empty($civicaseComponent->componentID) ? $civicaseComponent->componentID : '',
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

  /**
   * Sets activity status 'Scheduled' and 'Completed'.
   *
   * Sets for applicant review activity type if not already set.
   */
  private function setApplicantReviewActivityTypeStatuses() {
    $applicantReviewActivityName = self::APPLICANT_REVIEW;
    $statuses = civicrm_api3('OptionValue', 'get', [
      'name' => ['IN' => ['Scheduled', 'Completed']],
      'option_group_id' => 'activity_status',
    ])['values'];

    foreach ($statuses as $status) {
      $groupings = explode(',', $status['grouping']);
      $index = array_search($applicantReviewActivityName, $groupings);
      if ($index !== FALSE) {
        continue;
      }

      array_push($groupings, $applicantReviewActivityName);
      civicrm_api3('OptionValue', 'create', [
        'id' => $status['id'],
        'grouping' => implode(',', $groupings),
      ]);
    }
  }

}
