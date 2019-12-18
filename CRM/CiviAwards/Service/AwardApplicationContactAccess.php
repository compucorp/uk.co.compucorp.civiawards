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
   *
   * @return array|void
   *   The contact access to the Award applications.
   */
  public function get($contactId, $awardId) {
    $awardPanels = $this->getAwardPanels($awardId);

    if (empty($awardPanels)) {
      throw new Exception("No award panels available for this award type");
    }

    $visibilitySettings = [];
    $awardPanelContact = new AwardReviewPanelContact();
    foreach ($awardPanels as $awardPanelId) {
      if (!empty($awardPanelContact->get($awardPanelId, [$contactId]))) {
        $visibilitySettings[] = $this->getAwardVisibilitySettings($awardPanelId);
      }
    }

    return $this->processVisibilitySettings($visibilitySettings);
  }

  /**
   * Processes the contact visibility results.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   *
   * @return array
   *   The contact access to the Award applications.
   */
  private function processVisibilitySettings(array $visibilitySettings) {
    $applicationTags = [];
    $applicationStatus = [];
    $anonymizeApplication = TRUE;

    foreach ($visibilitySettings as $visibilitySetting) {
      if (isset($visibilitySetting['application_tags'])) {
        $applicationTags = array_merge($applicationTags, $visibilitySetting['application_tags']);
      }
      if (isset($visibilitySetting['application_status'])) {
        $applicationStatus = array_merge($applicationStatus, $visibilitySetting['application_status']);
      }

      if (isset($visibilitySetting['anonymize_application']) && $visibilitySetting['anonymize_application'] == 0) {
        $anonymizeApplication = FALSE;
      }
    }
    $applicationTags = array_unique($applicationTags);
    $applicationStatus = array_unique($applicationStatus);
    sort($applicationStatus);
    sort($applicationTags);

    return [
      'application_tags' => $applicationTags,
      'application_status' => $applicationStatus,
      'anonymize_application' => $anonymizeApplication,
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
