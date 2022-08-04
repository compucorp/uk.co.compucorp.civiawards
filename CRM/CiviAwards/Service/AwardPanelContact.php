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
    $awardReviewPanel = $this->getAwardReviewPanel($awardPanelId);
    $awardContactSettings = $this->getAwardContactSettings($awardReviewPanel);

    if (empty($awardContactSettings)) {
      return [];
    }

    $roleContacts = [];
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

    if (!empty($awardContactSettings['case_roles'])) {
      $roleContacts = $this->getRolesContacts($awardContactSettings['case_roles'], $awardReviewPanel->case_type_id, $filterContacts);
    }

    $panelContacts = $this->mergeAllRelatedContacts($includeGroupContacts, $relationshipContacts);
    $panelContacts = array_replace($roleContacts, $panelContacts);

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
      WHERE rt.is_active = 1 AND 
      (
        (r.start_date IS NULL AND r.end_date IS NULL AND r.is_active = 1) OR
        (r.start_date <= %3 AND r.end_date IS NULL) OR 
        (r.start_date IS NULL AND r.end_date > %3) OR 
        (r.start_date <= %3 AND r.end_date > %3)
      )
      AND rt.id = %2
      {$contactCondition}
      {$filterContactCondition}
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
   * Returns Award Review Panel Object.
   *
   * @param int $awardPanelId
   *   Award ID.
   *
   * @return \CRM_CiviAwards_BAO_AwardReviewPanel
   *   Award Review Panel.
   */
  private function getAwardReviewPanel($awardPanelId) {
    $awardReviewPanelObject = new AwardReviewPanel();
    $awardReviewPanelObject->id = $awardPanelId;
    $awardReviewPanelObject->find(TRUE);

    return $awardReviewPanelObject;
  }

  /**
   * Returns Award contact settings.
   *
   * @param \CRM_CiviAwards_BAO_AwardReviewPanel $awardReviewPanel
   *   Award ID.
   *
   * @return array
   *   Award contact settings.
   */
  private function getAwardContactSettings(AwardReviewPanel $awardReviewPanel) {
    if (!empty($awardReviewPanel->contact_settings)) {
      return unserialize((string) $awardReviewPanel->contact_settings);
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
   * @param array $roles
   *   The award roles to return assigned contacts for.
   * @param int $awardId
   *   The Award ID.
   * @param array $contactId
   *   The list of contact IDs.
   *
   * @return array
   *   A list of case_id and contact_id.
   */
  private function getRolesContacts(array $roles, $awardId, array $contactId): array {
    if (empty($contactId)) {
      return [];
    }

    $caseRoles = [];
    $caseTable = CRM_Case_BAO_Case::getTableName();
    $contactTable = CRM_Contact_BAO_Contact::getTableName();
    $contactEmailTable = CRM_Core_BAO_Email::getTableName();
    $caseRolesCondition = '(\'' . implode("','", $roles) . '\')';
    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();

    $query = "
      SELECT r.case_id, r.contact_id_b as contact_id,
      c.display_name, ce.email
      FROM {$relationshipTypeTable} rt
      INNER JOIN {$relationshipTable} r
        ON (r.relationship_type_id = rt.id)
      INNER JOIN {$caseTable} cs
        ON (cs.id = r.case_id AND cs.case_type_id = %1)
      INNER JOIN {$contactTable} c
        ON (c.id = r.contact_id_b)
      LEFT JOIN {$contactEmailTable} ce
        ON (c.id = ce.contact_id AND ce.is_primary = 1)
      WHERE rt.is_active = 1 AND 
      (
        (r.start_date IS NULL AND r.end_date IS NULL AND r.is_active = 1) OR
        (r.start_date <= %4 AND r.end_date IS NULL) OR 
        (r.start_date IS NULL AND r.end_date > %4) OR 
        (r.start_date <= %4 AND r.end_date > %4)
      )
      AND rt.name_b_a IN {$caseRolesCondition} AND r.contact_id_b IN (%3)
    ";

    $params = [
      1 => [$awardId, 'Integer'],
      4 => [date('Y-m-d'), 'String'],
      3 => [implode(',', $contactId), 'CommaSeparatedIntegers'],
    ];
    $result = CRM_Core_DAO::executeQuery($query, $params);

    while ($result->fetch()) {
      $caseRoles[$result->contact_id] = [
        'id' => $result->contact_id,
        'email' => $result->email,
        'display_name' => $result->display_name,
        'case_ids' => array_unique(array_merge(
          ($caseRoles[$result->contact_id]['case_ids'] ?? []), [$result->case_id]
        )),
      ];
    }

    return $caseRoles;
  }

}
