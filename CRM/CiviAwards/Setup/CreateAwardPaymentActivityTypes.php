<?php

/**
 * Class for setting up Award payment activity types.
 *
 * This class helps to create the Awards Payments activity category
 * and also create the Award Payment and Award payment activity types.
 * The default set of statuses to work with the Activity category are also
 * added.
 */
class CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes {

  const AWARD_PAYMENTS_ACTIVITY_CATEGORY = 'Awards Payments';
  const AWARD_PAYMENT_ACTIVITY_TYPE = 'Awards Payment';
  const AWARD_PAYMENT_REQUEST_ACTIVITY_TYPE = 'Awards Payment Request';
  const CUSTOM_GROUP_NAME = 'Awards_Payment_Information';

  /**
   * Adds the Applicant review activity type and category.
   */
  public function apply() {
    $this->addAwardPaymentsActivityCategory();
    $this->createActivityStatusesForAwardPaymentsCategory();
    $this->addAwardPaymentActivityTypes();
  }

  /**
   * Adds the award payments activity category.
   */
  private function addAwardPaymentsActivityCategory() {
    CRM_Core_BAO_OptionValue::ensureOptionValueExists([
      'option_group_id' => 'activity_category',
      'name' => self::AWARD_PAYMENTS_ACTIVITY_CATEGORY,
      'label' => ts(self::AWARD_PAYMENTS_ACTIVITY_CATEGORY),
      'is_default' => 1,
      'is_active' => TRUE,
      'is_reserved' => TRUE,
    ]);
  }

  /**
   * Adds the `award payment` and `award payment request` types.
   */
  private function addAwardPaymentActivityTypes() {
    $civiCaseComponent = CRM_Core_Component::get('CiviCase');
    $activityTypes = [
      [
        'name' => self::AWARD_PAYMENT_ACTIVITY_TYPE,
        'icon' => 'fa-money',
      ],
      [
        'name' => self::AWARD_PAYMENT_REQUEST_ACTIVITY_TYPE,
        'icon' => 'fa-files-o',
      ],
    ];

    foreach ($activityTypes as $activityType) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists(
        [
          'option_group_id' => 'activity_type',
          'name' => $activityType['name'],
          'label' => ts($activityType['name']),
          'grouping' => self::AWARD_PAYMENTS_ACTIVITY_CATEGORY,
          'icon' => $activityType['icon'],
          'component_id' => !empty($civiCaseComponent->componentID) ? $civiCaseComponent->componentID : '',
          'is_default' => 1,
          'is_reserved' => TRUE,
        ]
      );
    }
  }

  /**
   * Returns the award payment category statuses.
   *
   * @return array
   *   Activity statuses.
   */
  private function getActivityStatuses() {
    return [
      [
        'name' => 'applied_for_incomplete',
        'label' => 'Applied for (incomplete)',
        'filter' => CRM_Activity_BAO_Activity::INCOMPLETE,
      ],
      [
        'name' => 'approved_complete',
        'label' => 'Approved (complete)',
        'filter' => CRM_Activity_BAO_Activity::COMPLETED,
      ],
      [
        'name' => 'exported_complete',
        'label' => 'Exported (complete)',
        'filter' => CRM_Activity_BAO_Activity::COMPLETED,
      ],
      [
        'name' => 'paid_complete',
        'label' => 'Paid (complete)',
        'filter' => CRM_Activity_BAO_Activity::COMPLETED,
      ],
      [
        'name' => 'cancelled_cancelled',
        'label' => 'Cancelled (cancelled)',
        'filter' => CRM_Activity_BAO_Activity::CANCELLED,
      ],
      [
        'name' => 'failed_incomplete',
        'label' => 'Failed (incomplete)',
        'filter' => CRM_Activity_BAO_Activity::INCOMPLETE,
      ],
    ];
  }

  /**
   * Creates activity statuses for the Award Payment Category.
   */
  private function createActivityStatusesForAwardPaymentsCategory() {
    $activityStatuses = $this->getActivityStatuses();
    foreach ($activityStatuses as $activityStatus) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists(
        [
          'option_group_id' => 'activity_status',
          'name' => $activityStatus['name'],
          'label' => $activityStatus['label'],
          'filter' => $activityStatus['filter'],
          'grouping' => self::AWARD_PAYMENTS_ACTIVITY_CATEGORY,
          'is_reserved' => TRUE,
        ]
      );
    }
  }

}
