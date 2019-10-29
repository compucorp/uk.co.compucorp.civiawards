<?php

/**
 * Class CRM_CiviAwards_Service_AwardProfile.
 */
class CRM_CiviAwards_Service_AwardProfile {

  /**
   * Create a Profile for a Case Type/Award.
   *
   * @param int $caseTypeId
   *   Case Type Id.
   *
   * @return int
   *   Profile id.
   */
  public function createProfile($caseTypeId) {
    $profileName = 'Award Profile_' . $caseTypeId;
    $result = civicrm_api3('UFGroup', 'create', [
      'title' => $profileName,
      'name' => $profileName,
      'is_reserved' => 1,
    ]);

    return $result['id'];
  }

  /**
   * Create fields for a profile.
   *
   * @param array $customFieldIds
   *   Custom fields.
   * @param int $profileId
   *   Profile Id.
   */
  public function createProfileFields(array $customFieldIds, $profileId) {
    foreach ($customFieldIds as $customFieldId) {
      civicrm_api3('UFField', 'create', [
        'uf_group_id' => $profileId,
        'field_name' => 'custom_' . $customFieldId,
        'field_type' => 'Activity',
        'label' => 'Activity Field' . $customFieldId,
      ]);
    }
  }

  /**
   * Update fields for a profile.
   *
   * @param array $customFields
   *   Custom fields.
   * @param int $profileId
   *   Profile id.
   */
  public function updateProfileFields(array $customFields, $profileId) {
    $result = civicrm_api3('UFField', 'get', [
      'uf_group_id' => $profileId,
    ]);

    if ($result['count'] > 0) {
      foreach ($result['values'] as $profileField) {
        civicrm_api3('UFField', 'delete', [
          'id' => $profileField['id'],
        ]);
      }
    }

    $this->createProfileFields($customFields, $profileId);
  }

  /**
   * Delete a profile, fields will be deleted automatically.
   *
   * @param int $profileId
   *   Profile ID.
   */
  public function deleteProfile($profileId) {
    civicrm_api3('UFGroup', 'delete', [
      'id' => $profileId,
    ]);
  }

  /**
   * Returns the fields for a profile.
   *
   * @param int $profileID
   *   Profile Id.
   *
   * @return array
   *   Profile fields.
   */
  public function getProfileFields($profileID) {
    if (empty($profileID)) {
      return [];
    }
    $result = civicrm_api3('UFField', 'get', [
      'uf_group_id' => $profileID,
    ]);

    if ($result['count'] == 0) {
      return [];
    }

    $customFields = [];
    foreach ($result['values'] as $profileField) {
      $customFields[] = substr($profileField['field_name'], 7);
    }

    return $customFields;
  }

}
