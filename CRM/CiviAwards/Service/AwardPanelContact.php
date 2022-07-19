<?php

use CRM_CiviAwards_BAO_AwardReviewPanel as AwardReviewPanel;

/**
 * Class CRM_CiviAwards_Service_AwardReviewPanelContact.
 */
class CRM_CiviAwards_Service_AwardPanelContact {

  /**
   * Gets the all panel contacts for an Award.
   *
   * @param int $awardPanelId
   *   Award Id.
   * @param array $filterContacts
   *   If present, filters the results to return only for
   *   contacts present in filter contacts.
   *
   * @return array
   *   The panel contacts data.
   */
  public function get($awardPanelId, array $filterContacts = []) {
    $awardContactSettings = $this->getAwardContactSettings($awardPanelId);

    if (empty($awardContactSettings)) {
      return [];
    }

    $includeGroups = isset($awardContactSettings['include_groups']) ? $awardContactSettings['include_groups'] : [];
    $excludeGroups = isset($awardContactSettings['exclude_groups']) ? $awardContactSettings['exclude_groups'] : [];
    $includeGroupContacts = [];
    $excludeGroupContacts = [];

    if (!empty($includeGroups)) {
      $includeGroupContacts = $this->getContactsForGroup($includeGroups, $filterContacts);
    }
    if (!empty($excludeGroups)) {
      $excludeGroupContacts = $this->getContactsForGroup($excludeGroups, $filterContacts);
    }

    $relationshipContacts = [];
    if (!empty($awardContactSettings['relationship'])) {
      foreach ($awardContactSettings['relationship'] as $relationshipSettings) {
        $relationshipContacts[] = $this->getRelationshipContacts(
          $relationshipSettings['relationship_type_id'],
          $relationshipSettings['is_a_to_b'],
          $relationshipSettings['contact_id'],
          $filterContacts
        );
      }
    }

    $panelContacts = $this->mergeAllRelatedContacts($includeGroupContacts, $relationshipContacts);

    return array_diff_key($panelContacts, $excludeGroupContacts);
  }

  /**
   * Merges all the contacts gotten from panel contact settings.
   *
   * @param array $groupContacts
   *   Group contacts.
   * @param array $relationshipContacts
   *   Relationship contacts.
   */
  private function mergeAllRelatedContacts(array $groupContacts, array $relationshipContacts) {
    $allContacts = [];
    foreach ($relationshipContacts as $contacts) {
      $allContacts = $allContacts + $contacts;
    }

    $allContacts = $allContacts + $groupContacts;

    return $allContacts;
  }

  /**
   * Returns the panel contacts based on the group settings.
   *
   * Returns results based on the Award panel group settings.
   *
   * @param array $includeGroups
   *   Include contacts belonging to this group.
   * @param array $excludeGroups
   *   Exclude contacts belonging to ths group.
   * @param array $filterContacts
   *   If present, filters the results to return only for
   *   contacts present in filter contacts.
   *
   * @return array
   *   Group contacts.
   */
  private function getGroupContacts(array $includeGroups, array $excludeGroups = [], array $filterContacts = []) {
    $includeGroupContacts = $this->getContactsForGroup($includeGroups, $filterContacts);
    $excludeGroupContacts = $this->getContactsForGroup($excludeGroups, $filterContacts);

    return array_diff_key($includeGroupContacts, $excludeGroupContacts);
  }

  /**
   * Returns the panel contacts based on the relationship settings.
   *
   *  Returns results based on the Award panel relationship settings.
   *
   * @param int $relationshipTypeId
   *   Relationship type Id.
   * @param bool $isAToB
   *   If relationship is a to b or not.
   * @param array $contactId
   *   Contact Ids linked to the relationship.
   * @param array $filterContacts
   *   If present, filters the results to return only for
   *   contacts present in filter contacts.
   *
   * @return array
   *   Relationship contacts.
   */
  private function getRelationshipContacts($relationshipTypeId, $isAToB, array $contactId, array $filterContacts = []) {
    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();
    $contactTable = CRM_Contact_BAO_Contact::getTableName();
    $contactEmailTable = CRM_Core_BAO_Email::getTableName();
    $relationshipJoinCondition = $isAToB ? 'ON r.contact_id_a = c.id' : 'ON r.contact_id_b = c.id';
    $contactCondition = $isAToB ? 'AND r.contact_id_b IN (%1)' : 'AND r.contact_id_a IN (%1)';
    $filterContactCondition = $filterContacts ? 'AND c.id IN(' . implode(', ', $filterContacts) . ')' : '';

    $query = "
      SELECT c.id, c.display_name, ce.email
      FROM {$contactTable } c
      INNER JOIN {$relationshipTable} r
       {$relationshipJoinCondition}
      INNER JOIN {$relationshipTypeTable} rt
        ON rt.id = r.relationship_type_id
      LEFT JOIN {$contactEmailTable} ce
        ON (c.id = ce.contact_id AND ce.is_primary = 1)
      WHERE r.is_active = 1 AND rt.is_active = 1
      AND rt.id = %2
      {$contactCondition}
      {$filterContactCondition}
      AND (r.start_date IS NULL OR r.start_date <= %3)
      AND (r.end_date IS NULL OR r.end_date >= %3)
      ORDER by c.id ASC
    ";

    $params = [
      1 => [implode(',', $contactId), 'CommaSeparatedIntegers'],
      2 => [$relationshipTypeId, 'Integer'],
      3 => [date('Y-m-d'), 'String'],
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params);
    $contacts = [];

    while ($result->fetch()) {
      $contacts[$result->id] = [
        'id' => $result->id,
        'email' => $result->email,
        'display_name' => $result->display_name,
      ];
    }

    return $contacts;
  }

  /**
   * Returns Award contact settings.
   *
   * @param int $awardPanelId
   *   Award ID.
   *
   * @return array
   *   Award contact settings.
   */
  private function getAwardContactSettings($awardPanelId) {
    $awardReviewPanelObject = new AwardReviewPanel();
    $awardReviewPanelObject->id = $awardPanelId;
    $awardReviewPanelObject->find(TRUE);

    if (!empty($awardReviewPanelObject->contact_settings)) {
      return unserialize($awardReviewPanelObject->contact_settings);
    }

    return NULL;
  }

  /**
   * Returns contacts for a given group.
   *
   * @param array $groupIds
   *   Group Ids.
   * @param array $filterContacts
   *   If present, filters the results to return only for
   *   contacts present in filter contacts.
   *
   * @return array
   *   Group contacts.
   */
  private function getContactsForGroup(array $groupIds, array $filterContacts = []) {
    if (empty($groupIds)) {
      return [];
    }

    $params = [
      'group' => ['IN' => $groupIds],
      'return' => ['email', 'display_name'],
    ];
    if (!empty($filterContacts)) {
      $params['id'] = ['IN' => $filterContacts];
    }

    $result = civicrm_api3('Contact', 'get', $params);

    return $result['values'];
  }

  /**
   * Returns the contacts that has a role.
   *
   * This will also return the ID of the case where the role is assigned
   * to the contact.
   *
   * @param string $role
   *   The role to return contacts for.
   * @param int $awardId
   *   The Award ID.
   * @param int $contactId
   *   The contact ID.
   *
   * @return array
   *   A list of case_id and contact_id.
   */
  private function getRolesContacts($role, $awardId, $contactId): array {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_b_a' => $role,
      'api.Relationship.get' => [
        'relationship_type_id' => "\$value.id",
        'options' =>
        ['limit' => 0, 'is_active' => 1],
      ],
      'options' => ['limit' => 0],
      'is_active' => 1,
    ]);

    if ($result['is_error'] || $result['count'] < 1) {
      return [];
    }

    $caseRoles = [];

    foreach ($result['values'] as $relationshipType) {
      foreach ($relationshipType['api.Relationship.get']['values'] as $relationship) {
        if ($this->caseRoleRelationshipIsValid($relationship)) {
          $contactRole = [
            'case_id' => $relationship['case_id'],
            'contact_id' => $relationship['contact_id_b'],
          ];
          array_push($caseRoles, $contactRole);
        }
      }
    }

    return $caseRoles;
  }

  /**
   * Returns true if a case_role relationship is still valid.
   *
   * I.e.
   * The status "is_active": "1"
   * AND The start date is null or =< todays date
   * AND The end date is null or > todays date.
   *
   * @param array $caseRoleRelationship
   *   The case relationship array.
   *
   * @return bool
   *   true if relationship is valid, otherwise false.
   */
  private function caseRoleRelationshipIsValid(array $caseRoleRelationship): bool {
    $isActive = $caseRoleRelationship['is_active'] === "1";
    $todaysDate = (new Date())->getTime();
    $relationshipStartDate = (new Date($caseRoleRelationship['start_date'] ?? NULL))->getTime();
    $relationshipEndDate = (new Date($caseRoleRelationship['end_date'] ?? NULL))->getTime();
    $relationshipHasStarted = $relationshipStartDate <= $todaysDate;
    $relationshipHasEnded = $relationshipEndDate >= $todaysDate;

    return $isActive && $relationshipHasStarted && !$relationshipHasEnded;
  }

}
