<?php

/**
 * Class DeleteCustomGroups.
 */
class CRM_CiviAwards_Setup_DeleteCustomGroups {

  /**
   * Deletes custom group defined in the extension.
   */
  public function apply() {
    $customGroupsNames = ['Applicant_Review', 'Payment_Approval', 'Awards_Payment_Bank_Details', 'Awards_Payment_Information'];

    foreach ($customGroupsNames as $customGroupsName) {
      $result = civicrm_api3('CustomGroup', 'get', [
        'name' => $customGroupsName,
      ]);

      if (empty($result['id'])) {
        return;
      }

      civicrm_api3('CustomGroup', 'delete', [
        'id' => $result['id'],
      ]);
    }
  }

}
