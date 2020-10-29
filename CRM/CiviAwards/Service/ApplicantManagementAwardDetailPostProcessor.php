<?php

use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Handles the custom field related post processing for an Award Detail.
 *
 * This class is specific for the Applicant management instance and handles the
 * post processing related to custom field set associated with a case type
 * when the award detail for that case type is created/updated.
 */
class CRM_CiviAwards_Service_ApplicantManagementAwardDetailPostProcessor {

  /**
   * Stores the ApplicantManagement Post process helper.
   *
   * @var \CRM_CiviAwards_Helper_CustomGroupPostProcess
   */
  private $postProcessHelper;

  /**
   * Stores custom group subtypes.
   *
   * @var array
   */
  private $customGroupSubTypes;

  /**
   * Constructor function.
   *
   * @param \CRM_CiviAwards_Helper_CustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CustomGroupPostProcessHelper $postProcessHelper) {
    $this->postProcessHelper = $postProcessHelper;
    $this->customGroupSubTypes = $postProcessHelper->getCustomGroupSubTypesList();
  }

  /**
   * Handles case type post processing on create.
   *
   * Fetches all custom groups related to the case type category and
   * then compares the subtype of the custom group entity value case
   * types with that of the case type to be updated, if there's a match
   * then the case type is added for that custom group record.
   *
   * Also removes a case type from a custom group if it does not belong
   * to that group, this may be due to a sub type change for the case type.
   *
   * @param CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   */
  public function processCaseTypeCustomGroupsOnCreate(AwardDetail $awardDetail) {
    $caseTypeId = $awardDetail->case_type_id;
    $customGroups = $this->postProcessHelper->getCaseTypeCustomGroups($caseTypeId);
    if (empty($customGroups)) {
      return;
    }
    $caseTypeSubType = $this->postProcessHelper->getAwardSubtypesForCaseType([$caseTypeId]);
    foreach ($customGroups as $cusGroup) {
      $extendColValue = !empty($cusGroup['extends_entity_column_value']) ? $cusGroup['extends_entity_column_value'] : [];
      $customGroupSubTypeList = !empty($this->customGroupSubTypes[$cusGroup['id']]) ? $this->customGroupSubTypes[$cusGroup['id']] : [];
      if (array_intersect($customGroupSubTypeList, $caseTypeSubType)) {
        $entityColumnValues = array_merge($extendColValue, [$caseTypeId]);
        $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
      }
    }
  }

  /**
   * Handles case type post processing on update.
   *
   * Removes case type from custom groups that have a different case
   * category than the case type. This may be due to a change in case
   * category for the case type.
   *
   * Also removes the case type from a custom group in the event that
   * the subtype of the award has been updated and the subtype is not
   * part of the sub type list for the custom group.
   *
   * @param CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   */
  public function processCaseTypeCustomGroupsOnUpdate(AwardDetail $awardDetail) {
    $caseTypeId = $awardDetail->case_type_id;
    $mismatchCustomGroups = $this->postProcessHelper->getCaseTypeCustomGroupsWithCategoryMismatch($caseTypeId);
    $runCreateFunction = FALSE;
    if (!empty($mismatchCustomGroups)) {
      $runCreateFunction = TRUE;
      foreach ($mismatchCustomGroups as $cusGroup) {
        $entityColumnValues = array_diff($cusGroup['extends_entity_column_value'], [$caseTypeId]);
        $entityColumnValues = $entityColumnValues ? $entityColumnValues : NULL;
        $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
      }
    }

    if ($this->awardSubTypeChanged($awardDetail)) {
      $runCreateFunction = TRUE;
      $matchedCustomGroups = $this->postProcessHelper->getCaseTypeCustomGroupsWithCategoryMatch($caseTypeId);
      $caseTypeSubType = $this->postProcessHelper->getAwardSubtypesForCaseType([$caseTypeId]);
      foreach ($matchedCustomGroups as $cusGroup) {
        $customGroupSubTypeList = !empty($this->customGroupSubTypes[$cusGroup['id']]) ? $this->customGroupSubTypes[$cusGroup['id']] : [];
        $extendColValue = !empty($cusGroup['extends_entity_column_value']) ? $cusGroup['extends_entity_column_value'] : [];
        if (!array_intersect($customGroupSubTypeList, $caseTypeSubType)) {
          $entityColumnValues = array_diff($extendColValue, [$caseTypeId]);
          $entityColumnValues = $entityColumnValues ? $entityColumnValues : NULL;
          $this->updateCustomGroup($cusGroup['id'], $entityColumnValues);
        }
      }
    }

    if ($runCreateFunction) {
      $this->processCaseTypeCustomGroupsOnCreate($awardDetail);
    }
  }

  /**
   * Check if award subtype changed.
   *
   * @param \CRM_CiviAwards_BAO_AwardDetail $awardDetail
   *   Award detail object.
   *
   * @return bool
   *   Whether award subtype changed or not.
   */
  private function awardSubTypeChanged(AwardDetail $awardDetail) {
    return !empty($awardDetail->award_type) && $awardDetail->award_type != $awardDetail->oldAwardDetail->award_type;
  }

  /**
   * Updates a custom group.
   *
   * We are using the custom group object here rather than the API because if
   * this is updated via the API the `extends_entity_column_id` field will be
   * set to NULL and this is needed to keep track of custom groups extending
   * case categories.
   *
   * @param int $id
   *   Custom group Id.
   * @param array|null $entityColumnValues
   *   Entity custom values for custom group.
   */
  protected function updateCustomGroup($id, $entityColumnValues) {
    $cusGroup = new CRM_Core_BAO_CustomGroup();
    $cusGroup->id = $id;
    $entityColValue = is_null($entityColumnValues) ? 'null' : CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $entityColumnValues) . CRM_Core_DAO::VALUE_SEPARATOR;
    $cusGroup->extends_entity_column_value = $entityColValue;
    $cusGroup->save();
  }

}
