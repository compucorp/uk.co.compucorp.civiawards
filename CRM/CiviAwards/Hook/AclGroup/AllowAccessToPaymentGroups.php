<?php

use CRM_CiviAwards_Hook_AclGroup_ActivityTypeCustomGroupAccess as ActivityTypeCustomGroupAccess;
use CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes as CreateAwardPaymentActivityTypes;
use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;

/**
 * Class CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroups.
 */
class CRM_CiviAwards_Hook_AclGroup_AllowAccessToPaymentGroups extends ActivityTypeCustomGroupAccess {

  /**
   * Modifies ACL groups for user with access payment set permission.
   *
   * Allows a user with access payment set permission to have access to the
   * payment information custom group and custom fields belonging to this group.
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
    $this->setAccessibleCustomGroups($currentGroups);
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
    return $tableName == CRM_Core_BAO_CustomGroup::getTableName() && CRM_Core_Permission::check(AwardPermission::PAYMENT_FIELD_SET_PERM);
  }

  /**
   * Returns the activity type Id.
   *
   * @return int
   *   Activity Type ID.
   */
  protected function getActivityTypeId() {
    return $this->getAwardPaymentActivityTypeId();
  }

  /**
   * Returns the payment activity type Id.
   *
   * @return int|null
   *   Activity type Id.
   */
  private function getAwardPaymentActivityTypeId() {
    try {
      $result = civicrm_api3('OptionValue', 'getsingle', [
        'option_group_id' => 'activity_type',
        'name' => CreateAwardPaymentActivityTypes::AWARD_PAYMENT_ACTIVITY_TYPE,
      ]);

      return !empty($result['value']) ? $result['value'] : NULL;
    }
    catch (Exception $e) {
      return NULL;
    }
  }

}
