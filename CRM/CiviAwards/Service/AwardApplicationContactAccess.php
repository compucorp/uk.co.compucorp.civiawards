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
    $tags = array_keys($visibilitySettings['tags']);
    $tags = in_array('all', $tags) ? [] : $tags;
    $status = array_keys($visibilitySettings['status']);
    $status = in_array('all', $status) ? [] : $status;
    sort($status);
    sort($tags);
    return [
      'application_tags' => $tags,
      'application_status' => $status,
    ];
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
    $caseStatus = $applicationDetails['status_id'];

    $caseStatusAccess = !empty($visibilitySettings['status'][$caseStatus]['status_to_move_to']) ?
      $visibilitySettings['status'][$caseStatus]['status_to_move_to'] : [];
    $allCaseStatusAccess = !empty($visibilitySettings['status']['all']['status_to_move_to']) ?
      $visibilitySettings['status']['all']['status_to_move_to'] : [];
    $allCaseTagAccess = !empty($visibilitySettings['tags']['all']['status_to_move_to']) ?
      $visibilitySettings['tags']['all']['status_to_move_to'] : [];
    $caseTagAccess = [];
    $caseTagAnonymization = TRUE;

    foreach ($caseTags as $tag) {
      $statusToMoveTo = !empty($visibilitySettings['tags'][$tag]['status_to_move_to']) ? $visibilitySettings['tags'][$tag]['status_to_move_to'] : [];
      $caseTagAccess = array_merge($caseTagAccess, $statusToMoveTo);
    }

    $statusToMoveApplicationTo = array_merge($caseStatusAccess, $allCaseStatusAccess, $caseTagAccess);
    if (!empty($caseTags)) {
      $statusToMoveApplicationTo = array_merge($statusToMoveApplicationTo, $allCaseTagAccess);
      $caseTagAnonymization = $this->getCaseTagsAnonymization($visibilitySettings, $caseTags);
    }
    $caseStatusAnonymization = $this->getCaseStatusAnonymization($visibilitySettings, [$caseStatus]);
    $statusToMoveApplicationTo = array_unique($statusToMoveApplicationTo);
    sort($statusToMoveApplicationTo);

    return [
      'anonymize_application' => $caseTagAnonymization && $caseStatusAnonymization ? TRUE : FALSE,
      'status_to_move_to' => $statusToMoveApplicationTo,
    ];
  }

  /**
   * Gets anonymization value for case status or case tags data.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   * @param array $data
   *   Data array to get anonymization data for.
   * @param string $key
   *   Visibility key: tags or status.
   *
   * @return bool
   *   Anonymization value.
   */
  private function getAnonymizationForReview(array $visibilitySettings, array $data, $key) {
    $anonymizeApplication = TRUE;
    foreach ($data as $value) {
      if ($anonymizeApplication === TRUE) {
        $anonymizeApplication = $this->getReviewAnonymizationValue($visibilitySettings, $value, $key);
      }
    }
    $allDataAnonymization = $this->getReviewAnonymizationValue($visibilitySettings, 'all', $key);

    return $anonymizeApplication && $allDataAnonymization ? TRUE : FALSE;
  }

  /**
   * Gets anonymization value for case tags data.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   * @param array $caseTags
   *   Data array to get anonymization data for.
   *
   * @return bool
   *   Anonymization value.
   */
  private function getCaseTagsAnonymization(array $visibilitySettings, array $caseTags) {
    return $this->getAnonymizationForReview($visibilitySettings, $caseTags, 'tags');
  }

  /**
   * Gets anonymization value for case status data.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   * @param array $caseStatus
   *   Data array to get anonymization data for.
   *
   * @return bool
   *   Anonymization value.
   */
  private function getCaseStatusAnonymization(array $visibilitySettings, array $caseStatus) {
    return $this->getAnonymizationForReview($visibilitySettings, $caseStatus, 'status');
  }

  /**
   * Gets review anonymization value.
   *
   * @param array $visibilitySettings
   *   Visibility settings.
   * @param mixed $value
   *   Case status or tag value.
   * @param string $key
   *   Visibility key: tags or status.
   *
   * @return bool|mixed
   *   Anonymization value.
   */
  private function getReviewAnonymizationValue(array $visibilitySettings, $value, $key) {
    if (isset($visibilitySettings[$key][$value]['anonymize_application'])) {
      return $visibilitySettings[$key][$value]['anonymize_application'];
    }

    return TRUE;
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
    $tags = [];
    $status = [];
    foreach ($visibilitySettings as $visibilitySetting) {
      if (isset($visibilitySetting['application_tags']) && empty($visibilitySetting['application_tags'])) {
        $tagKey = 'all';
        $this->setVisibilityData($tags, $tagKey, $visibilitySetting);
      }
      elseif (!empty($visibilitySetting['application_tags'])) {
        foreach ($visibilitySetting['application_tags'] as $tagKey) {
          $this->setVisibilityData($tags, $tagKey, $visibilitySetting);
        }
      }

      if (isset($visibilitySetting['application_status']) && empty($visibilitySetting['application_status'])) {
        $statusKey = 'all';
        $this->setVisibilityData($status, $statusKey, $visibilitySetting);
      }
      elseif (!empty($visibilitySetting['application_status'])) {
        foreach ($visibilitySetting['application_status'] as $statusKey) {
          $this->setVisibilityData($status, $statusKey, $visibilitySetting);
        }
      }
    }

    return [
      'tags' => $tags,
      'status' => $status,
    ];
  }

  /**
   * Sets visibility data for status or tag.
   *
   * @param array $data
   *   Data array to set visivility data to.
   * @param mixed $key
   *   Array key.
   * @param array $visibilitySetting
   *   Visibility setting for award panel.
   */
  private function setVisibilityData(array &$data, $key, array $visibilitySetting) {
    $this->initializeArray($data, $key);
    $data[$key]['status_to_move_to'] = $this->getRestrictedStatus($data[$key]['status_to_move_to'], $visibilitySetting);
    $data[$key]['anonymize_application'] = $data[$key]['anonymize_application'] === FALSE ? FALSE : $this->getAnonymization($visibilitySetting);
  }

  /**
   * Create an entry for the key in the array if not present.
   *
   * @param array $data
   *   Array to initialize data in.
   * @param mixed $key
   *   Key to initialize data for.
   */
  private function initializeArray(array &$data, $key) {
    if (!isset($data[$key]['status_to_move_to'])) {
      $data[$key]['status_to_move_to'] = [];
    }

    if (!isset($data[$key]['anonymize_application'])) {
      $data[$key]['anonymize_application'] = TRUE;
    }
  }

  /**
   * Gets anonymization from visibility setting.
   *
   * @param array $visibilitySetting
   *   Visibility setting for award panel.
   *
   * @return bool
   *   Return anonymization.
   */
  private function getAnonymization(array $visibilitySetting) {
    if (isset($visibilitySetting['anonymize_application']) && $visibilitySetting['anonymize_application'] == 0) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Returns restricted status data.
   *
   * @param mixed $oldValue
   *   Old value.
   * @param array $visibilitySetting
   *   Visibility setting for award panel.
   *
   * @return array
   *   New value for restricted status.
   */
  private function getRestrictedStatus($oldValue, array $visibilitySetting) {
    return array_merge($oldValue, !empty($visibilitySetting['is_application_status_restricted']) ?
      $visibilitySetting['restricted_application_status'] : []);
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
