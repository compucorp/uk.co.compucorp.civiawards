<?php

/**
 * Service class CRM_CiviAwards_Service_AwardProfile.
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
   * @param array $customFields
   *   Custom fields.
   * @param int $profileId
   *   Profile Id.
   *
   * @throws \CiviCRM_API3_Exception
   *   API3 Exception.
   */
  public function createProfileFields(array $customFields, $profileId) {
    $this->addContactIdField($profileId);

    foreach ($customFields as $customField) {
      civicrm_api3('UFField', 'create', [
        'uf_group_id' => $profileId,
        'field_name' => 'custom_' . $customField['id'],
        'field_type' => 'Activity',
        'label' => 'Activity Field' . $customField['id'],
        'is_required' => $customField['required'],
        'weight' => $customField['weight'],
        'option.autoweight' => FALSE,
      ]);
    }
  }

  /**
   * Adds the contact ID field as part of a set of profile fields.
   *
   * This is necessary because for the Profile submit and get APIs
   * to work, the Profile must contain both contact and activity fields,
   * However, this field is created disabled, so it does not interfere
   * with the profile listing and view.
   *
   * @param int $profileId
   *   Profile ID.
   */
  private function addContactIdField($profileId) {
    civicrm_api3('UFField', 'create', [
      'uf_group_id' => $profileId,
      'field_name' => 'id',
      'field_type' => 'Contact',
      'label' => 'Contact ID',
      'is_active' => 0,
    ]);
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
      'options' => ['limit' => 0],
    ]);

    if ($result['count'] == 0) {
      return [];
    }

    $customFields = [];
    foreach ($result['values'] as $profileField) {
      if (strpos($profileField['field_name'], 'custom_') === 0) {
        $customFields[] = [
          'id' => substr($profileField['field_name'], 7),
          'required' => $profileField['is_required'],
          'weight' => $profileField['weight'],
        ];
      }
    }

    return $customFields;
  }

}
