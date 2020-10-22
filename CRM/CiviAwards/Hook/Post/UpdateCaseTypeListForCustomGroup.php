<?php

use CRM_Civicase_Helper_CaseCategory as CaseCategoryHelper;
use CRM_CiviAwards_Service_ApplicantManagementUtils as ApplicantManagementUtils;
use CRM_CiviAwards_Service_ApplicantManagementAwardDetailPostProcessor as ApplicantManagementAwardDetailPostProcessor;
use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;

/**
 * Updates custom group case type list for applicant management.
 *
 * When a case type is created/updated, custom groups related to
 * the case types need to be updated.
 */
class CRM_CiviAwards_Hook_Post_UpdateCaseTypeListForCustomGroup {

  /**
   * Case Category Custom Group Saver.
   *
   * @param string $op
   *   The operation being performed.
   * @param string $objectName
   *   Object name.
   * @param mixed $objectId
   *   Object ID.
   * @param object $objectRef
   *   Object reference.
   */
  public function run($op, $objectName, $objectId, &$objectRef) {
    if (!$this->shouldRun($op, $objectName)) {
      return;
    }

    $caseTypeDetails = $this->getCaseTypeDetails($objectRef->case_type_id);
    $caseCategoryInstance = CaseCategoryHelper::getInstanceObject($caseTypeDetails['case_type_category']);
    if (empty($caseCategoryInstance || !$caseCategoryInstance instanceof ApplicantManagementUtils)) {
      return;
    }
    $caseTypePostProcessor = new ApplicantManagementAwardDetailPostProcessor(new CustomGroupPostProcessHelper());

    if ($op === 'create') {
      $caseTypePostProcessor->processCaseTypeCustomGroupsOnCreate($objectRef);
    }

    if ($op === 'edit') {
      $caseTypePostProcessor->processCaseTypeCustomGroupsOnUpdate($objectRef);
    }
  }

  /**
   * Gets the case type details.
   *
   * We need to use this function because core does not pass
   * the Case Type object in the ObjectRef.
   *
   * @param int $caseTypeId
   *   Case type ID.
   */
  private function getCaseTypeDetails($caseTypeId) {
    $result = civicrm_api3('CaseType', 'getsingle', [
      'id' => $caseTypeId,
    ]);

    return $result;
  }

  /**
   * Determines if the hook should run or not.
   *
   * @param string $op
   *   The operation being performed.
   * @param string $objectName
   *   Object name.
   *
   * @return bool
   *   returns a boolean to determine if hook will run or not.
   */
  private function shouldRun($op, $objectName) {
    return $objectName == 'AwardDetail' && in_array($op, ['create', 'edit']);
  }

}
