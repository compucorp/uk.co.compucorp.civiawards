<?php

use CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes as CreateAwardPaymentActivityTypes;

/**
 * Removes the status filter from payment status labels.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1010 {

  /**
   * Runs the update.
   *
   * @return bool
   *   TRUE when successfull.
   */
  public function apply() {
    $this->removeStatusFilterFromPaymentStatusLabels();

    return TRUE;
  }

  /**
   * Loops through all payment statuses and sets their intended label.
   */
  private function removeStatusFilterFromPaymentStatusLabels() {
    $paymentStatuses = $this->getPaymentStatusesToUpdate();

    foreach ($paymentStatuses as $paymentStatus) {
      civicrm_api3('OptionValue', 'getsingle', [
        'name' => $paymentStatus['name'],
        'option_group_id' => 'activity_status',
        'grouping' => CreateAwardPaymentActivityTypes::AWARD_PAYMENTS_ACTIVITY_CATEGORY,
        'api.OptionValue.create' => [
          'id' => '$value.id',
          'label' => $paymentStatus['new_label'],
        ],
      ]);
    }
  }

  /**
   * Returns a list of payments statuses with the intended label for each one.
   *
   * @return array
   *   A list of payment status names and the labels they should have.
   */
  private function getPaymentStatusesToUpdate() {
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

}
