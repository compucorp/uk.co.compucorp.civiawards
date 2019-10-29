<?php

use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;
use CRM_CiviAwards_BAO_AwardManager as AwardManager;
use CRM_CiviAwards_Service_AwardProfile as AwardProfileService;

/**
 * Class CRM_CiviAwards_Service_AwardDetail.
 */
class CRM_CiviAwards_Service_AwardDetail {

  /**
   * Post Create actions for an award detail.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award Detail instance.
   * @param array $params
   *   Parameters used to update the Award detail instance.
   */
  public function postCreateActions(AwardDetail $awardDetail, array $params) {
    $caseTypeId = $this->getCaseType($awardDetail);
    if (!empty($params['award_manager'])) {
      $this->createAwardManagers($params['award_manager'], $caseTypeId);
    }

    $this->addAwardProfile($awardDetail, $params, $caseTypeId);
  }

  /**
   * Post Update actions for an award detail.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award Detail instance.
   * @param array $params
   *   Parameters used to create the Award detail instance.
   */
  public function postUpdateActions(AwardDetail $awardDetail, array $params) {
    if (!empty($params['award_manager'])) {
      $caseTypeId = $this->getCaseType($awardDetail);
      $this->updateAwardManagers($params['award_manager'], $caseTypeId);
    }

    if (isset($params['review_fields'])) {
      $profileId = $this->getProfileId($awardDetail);
      $this->updateAwardProfile($params['review_fields'], $profileId);
    }
  }

  /**
   * Create Award managers for a case type.
   *
   * @param array $awardManagers
   *   Award Manager.
   * @param int $caseTypeId
   *   Case Type ID.
   */
  private function createAwardManagers(array $awardManagers, $caseTypeId) {
    foreach ($awardManagers as $awardManager) {
      $params = ['contact_id' => $awardManager, 'case_type_id' => $caseTypeId];
      AwardManager::create($params);
    }
  }

  /**
   * Update Award managers for a case type.
   *
   * We need to delete previous award managers so we can assign the new
   * ones.
   *
   * @param array $awardManagers
   *   Award Manager.
   * @param int $caseTypeId
   *   Case Type ID.
   */
  private function updateAwardManagers(array $awardManagers, $caseTypeId) {
    $this->deleteAwardManagersForCaseType($caseTypeId);
    $this->createAwardManagers($awardManagers, $caseTypeId);
  }

  /**
   * Delete award managers for a case type.
   *
   * @param int $caseTypeId
   *   Case Type ID.
   */
  private function deleteAwardManagersForCaseType($caseTypeId) {
    $awardManagerObject = new AwardManager();
    $awardManagerObject->case_type_id = $caseTypeId;
    $awardManagerObject->find();

    while ($awardManagerObject->fetch()) {
      $awardManagers[] = clone $awardManagerObject;
    }

    foreach ($awardManagers as $awardManager) {
      $awardManager->delete();
    }
  }

  /**
   * Gets the Case Type.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award Detail object.
   *
   * @return int
   *   Returns the Case Type ID.
   */
  private function getCaseType(AwardDetail $awardDetail) {
    return $this->getAwardDetailField($awardDetail, 'case_type_id');
  }

  /**
   * Returns the profile Id.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   *
   * @return mixed
   *   Profile Id.
   */
  private function getProfileId(AwardDetail $awardDetail) {
    return $this->getAwardDetailField($awardDetail, 'profile_id');
  }

  /**
   * Returns the field value for an Award detail.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   * @param string $fieldName
   *   Field name.
   *
   * @return mixed
   *   Field value.
   */
  private function getAwardDetailField(AwardDetail $awardDetail, $fieldName) {
    if (!empty($awardDetail->$fieldName)) {
      return $awardDetail->$fieldName;
    }

    if (!empty($awardDetail->id)) {
      $storedAwardDetail = AwardDetail::findById($awardDetail->id);
      return $storedAwardDetail->$fieldName;
    }
  }

  /**
   * Adds an award profile for an Award detail.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   * @param array $params
   *   Params.
   * @param int $caseTypeId
   *   Case Type ID.
   */
  private function addAwardProfile(AwardDetail $awardDetail, array $params, $caseTypeId) {
    $awardProfileService = new AwardProfileService();
    $profileId = $awardProfileService->createProfile($caseTypeId);
    $awardDetail->profile_id = $profileId;
    $awardDetail->save();

    if (!empty($params['review_fields'])) {
      $awardProfileService->createProfileFields($params['review_fields'], $profileId);
    }
  }

  /**
   * Updates the Award profile for an Award detail.
   *
   * @param array $customFields
   *   Custom fields for the Award.
   * @param int $profileId
   *   Profile Id.
   */
  private function updateAwardProfile(array $customFields, $profileId) {
    $awardProfileService = new AwardProfileService();
    $awardProfileService->updateProfileFields($customFields, $profileId);
  }

  /**
   * Delete an Award detail.
   *
   * Also deletes the Award managers alongside.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   */
  public function deleteDependencies(AwardDetail $awardDetail) {
    $caseType = $awardDetail->case_type_id;
    $this->deleteAwardManagersForCaseType($caseType);
    $awardProfileService = new AwardProfileService();
    $awardProfileService->deleteProfile($awardDetail->profile_id);
  }

}
