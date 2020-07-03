<?php

use CRM_CiviAwards_BAO_AwardReviewPanel as AwardReviewPanel;
use CRM_CiviAwards_Service_AwardPanelContact as AwardReviewPanelContact;

/**
 * Class CRM_CiviAwards_Service_AwardApplicationContactAccess.
 */
class CRM_CiviAwards_Service_AwardApplicationContactAccess {

  /**
   * Returns the contact access to the Award Applications.
   *
   * @param int $contactId
   *   Contact Id.
   * @param int $awardId
   *   Award Id.
   * @param CRM_CiviAwards_Service_AwardPanelContact $awardPanelContact
   *   Award Panel contact service.
   *
   * @return array|void
   *   The contact access to the Award applications.
   */
  public function get($contactId, $awardId, AwardReviewPanelContact $awardPanelContact) {
    $visibilitySettings = $this->processVisibilitySettings($this->getContactVisibilitySettings($contactId, $awardId, $awardPanelContact));
    foreach ($visibilitySettings as &$visibilitySetting) {
      unset($visibilitySetting['status_to_move_to'], $visibilitySetting['anonymize_application']);
    }

    return $visibilitySettings;
  }

  /**
   * Returns the visibility settings the contact has for the Award.
   *
   * @param int $contactId
   *   Contact Id.
   * @param int $awardId
   *   Award Id.
   * @param CRM_CiviAwards_Service_AwardPanelContact $awardPanelContact
   *   Award Panel contact service.
   *
   * @return array
   *   Visibility settings.
   */
  private function getContactVisibilitySettings($contactId, $awardId, AwardReviewPanelContact $awardPanelContact) {
    $awardPanels = $this->getAwardPanels($awardId);

    if (empty($awardPanels)) {
      throw new Exception("No award panels available for this award type");
    }

    $visibilitySettings = [];
    foreach ($awardPanels as $awardPanelId) {
      if (!empty($awardPanelContact->get($awardPanelId, [$contactId]))) {
        $visibilitySettings[] = $this->getAwardVisibilitySettings($awardPanelId);
      }
    }

    if (empty($visibilitySettings)) {
      throw new Exception("This contact does not have any access to the panels on this Award");
    }

    return $visibilitySettings;
  }

  /**
   * Returns the Award ID given an application ID.
   *
   * @param int $applicationId
   *   Application ID.
   *
   * @return mixed|null
   *   Award ID.
   */
  private function getApplicationDetails($applicationId) {
    try {
      $result = civicrm_api3('Case', 'getsingle', [
        'return' => ['tag_id', 'case_type_id', 'status_id'],
        'id' => $applicationId,
      ]);

      return $result;
    }
    catch (Exception $e) {
      return NULL;
    }
  }

  /**
   * Processes the contact visibility settings results.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   *
   * @return array
   *   The contact access to the Award applications.
   */
  private function processVisibilitySettings(array $visibilitySettings) {
    $response = [];
    foreach ($visibilitySettings as $visibilitySetting) {
      $response[] = [
        'application_tags' => isset($visibilitySetting['application_tags']) ? $visibilitySetting['application_tags'] : [],
        'application_status' => isset($visibilitySetting['application_status']) ? $visibilitySetting['application_status'] : [],
        'status_to_move_to' => !empty($visibilitySetting['is_application_status_restricted']) ? $visibilitySetting['restricted_application_status'] : [],
        'anonymize_application' => isset($visibilitySetting['anonymize_application']) && $visibilitySetting['anonymize_application'] == 0 ? FALSE : TRUE,
      ];
    }

    return $response;
  }

  /**
   * Returns the review access a contact has to the Award Application.
   *
   * Basically returns the statuses that a contact can move an application
   * to and if the data for the application should be anonymized or not.
   *
   * @param int $contactId
   *   Contact Id.
   * @param int $applicationId
   *   Award Id.
   * @param CRM_CiviAwards_Service_AwardPanelContact $awardPanelContact
   *   Award Panel contact service.
   *
   * @return array
   *   Review access array.
   */
  public function getReviewAccess($contactId, $applicationId, AwardReviewPanelContact $awardPanelContact) {
    $applicationDetails = $this->getApplicationDetails($applicationId);
    $awardId = $applicationDetails['case_type_id'];
    $visibilitySettings = $this->processVisibilitySettings($this->getContactVisibilitySettings($contactId, $awardId, $awardPanelContact));

    $caseTags = !empty($applicationDetails['tag_id']) ? array_keys($applicationDetails['tag_id']) : [];
    $caseStatus = [$applicationDetails['status_id']];
    $statusToMoveApplicationTo = [];
    $anonymization = TRUE;

    foreach ($visibilitySettings as $visibilitySetting) {
      $caseStatusMatch = array_intersect($visibilitySetting['application_status'], $caseStatus) || empty($visibilitySetting['application_status']);
      $caseTagMatch = !empty($caseTags) && (array_intersect($visibilitySetting['application_tags'], $caseTags) || empty($visibilitySetting['application_tags']));

      if ($caseStatusMatch && $caseTagMatch) {
        $statusToMoveApplicationTo = array_merge($statusToMoveApplicationTo, $visibilitySetting['status_to_move_to']);
        $anonymization = $visibilitySetting['anonymize_application'] === FALSE ? FALSE : $anonymization;
      }
    }

    $statusToMoveApplicationTo = array_unique($statusToMoveApplicationTo);
    sort($statusToMoveApplicationTo);

    return [
      'anonymize_application' => $anonymization,
      'status_to_move_to' => $statusToMoveApplicationTo,
    ];
  }

  /**
   * Returns the Award Panels for an Award.
   *
   * @param int $awardId
   *   Award Id.
   *
   * @return array
   *   Award panels.
   */
  private function getAwardPanels($awardId) {
    $awardReviewPanelObject = new AwardReviewPanel();
    $awardReviewPanelObject->case_type_id = $awardId;
    $awardReviewPanelObject->is_active = 1;
    $awardReviewPanelObject->find();

    $panelIds = [];
    while ($awardReviewPanelObject->fetch()) {
      $panelIds[] = $awardReviewPanelObject->id;
    }

    return $panelIds;
  }

  /**
   * Returns Award Visibility settings.
   *
   * @param int $awardPanelId
   *   Award ID.
   *
   * @return array
   *   Award contact settings.
   */
  private function getAwardVisibilitySettings($awardPanelId) {
    $awardReviewPanelObject = new AwardReviewPanel();
    $awardReviewPanelObject->id = $awardPanelId;
    $awardReviewPanelObject->find(TRUE);

    if (!empty($awardReviewPanelObject->visibility_settings)) {
      return unserialize($awardReviewPanelObject->visibility_settings);
    }

    return NULL;
  }

}
