<?php

/**
 * Class for updating the Award payment activity status labels.
 */
class CRM_CiviAwards_Setup_UpdateAwardPaymentActivityStatusLabel {

  /**
   * Updates the Applicant payment activity status label.
   */
  public function apply() {
    $this->updateActivityStatusLabels();
  }

  /**
   * Returns the award payment category statuses map.
   *
   * @return array
   *   Activity statuses map.
   */
  private function getActivityStatusesMap() {
    return [
      [
        'name' => 'applied_for_incomplete',
        'new_label' => 'Applied for',
      ],
      [
        'name' => 'approved_complete',
        'new_label' => 'Approved',
      ],
      [
        'name' => 'exported_complete',
        'new_label' => 'Exported',
      ],
      [
        'name' => 'paid_complete',
        'new_label' => 'Paid',
      ],
      [
        'name' => 'cancelled_cancelled',
        'new_label' => 'Cancelled',
      ],
      [
        'name' => 'failed_incomplete',
        'new_label' => 'Failed',
      ],
    ];
  }

  /**
   * Updates the Applicant payment activity status label.
   */
  private function updateActivityStatusLabels() {
    $activityStatusesMap = $this->getActivityStatusesMap();

    foreach ($activityStatusesMap as $activityStatus) {
      civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'option_group_id' => "activity_status",
        'name' => $activityStatus['name'],
        'api.OptionValue.create' => [
          'id' => '$value.id',
          'label' => $activityStatus['new_label'],
        ],
      ]);
    }
  }

}
