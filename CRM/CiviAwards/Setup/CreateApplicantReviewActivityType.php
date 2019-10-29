<?php

/**
 * Class CreateApplicantReviewActivityType.
 */
class CRM_CiviAwards_Setup_CreateApplicantReviewActivityType {

  /**
   * Adds the Applicant review activity type and category.
   */
  public function apply() {
    $this->addApplicantReviewCategory();
    $this->addApplicantReviewActivityType();
  }

  /**
   * Adds the applicant review activity category.
   */
  private function addApplicantReviewCategory() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'activity_category',
      'name' => 'Applicant Review',
      'label' => 'Applicant Review',
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
      'name' => 'Applicant Review',
      'label' => 'Applicant Review',
      'grouping' => 'Applicant Review',
      'icon' => 'fa-user',
      'component_id' => !empty($civicaseComponent->componentID) ? $civicaseComponent->componentID : '',
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

}
