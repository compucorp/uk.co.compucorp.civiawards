<?php

use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;
use CRM_CiviAwards_BAO_AwardManager as AwardManager;

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
    $caseTypeId = $this->getCaseType($awardDetail);
    if (!empty($params['award_manager'])) {
      $this->updateAwardManagers($params['award_manager'], $caseTypeId);
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
    if (!empty($awardDetail->case_type_id)) {
      return $awardDetail->case_type_id;
    }

    if (!empty($awardDetail->id)) {
      $storedAwardDetail = AwardDetail::findById($awardDetail->id);
      return $storedAwardDetail->case_type_id;
    }
  }

  /**
   * Delete an Award detail.
   *
   * Also deletes the Award managers alongside.
   *
   * @param int $id
   *   Award detail ID.
   */
  public function deleteDependencies($id) {
    $awardDetail = AwardDetail::findById($id);
    $caseType = $awardDetail->case_type_id;
    $this->deleteAwardManagersForCaseType($caseType);
  }

}
