<?php

/**
 * Base class for activity type custom group access.
 */
abstract class CRM_CiviAwards_Hook_AclGroup_ActivityTypeCustomGroupAccess {

  /**
   * Activity Type Id.
   *
   * @return int
   *   Activity type Id.
   */
  abstract protected function getActivityTypeId();

  /**
   * Returns the Custom groups attached to the activity type.
   */
  protected function getActivityTypeCustomGroups() {
    $activityTypeCustomGroups = [];
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'extends' => 'Activity',
      'extends_entity_column_value' => ['LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $this->getActivityTypeId() . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
    ]);

    if (!empty($result['values'])) {
      $activityTypeCustomGroups = $result['values'];
    }

    return $activityTypeCustomGroups;
  }

  /**
   * Modifies/sets the ACL custom groups that the current user has access to.
   *
   * @param array $currentGroups
   *   Current ACL Groups user has access to.
   */
  protected function setAccessibleCustomGroups(array &$currentGroups) {
    $activityTypeCustomGroups = $this->getActivityTypeCustomGroups();
    if (!$activityTypeCustomGroups) {
      return;
    }

    foreach ($activityTypeCustomGroups as $activityTypeCustomGroup) {
      $currentGroups[] = $activityTypeCustomGroup['id'];
    }

    $currentGroups = array_unique($currentGroups);
  }

}
