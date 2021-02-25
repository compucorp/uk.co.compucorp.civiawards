<?php

use CRM_Core_DAO_CustomGroup as CustomGroup;
use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;

/**
 * Applicant management custom group post processor class.
 *
 * Handles events after a custom group extending a case category entity
 * is saved.
 */
class CRM_CiviAwards_Service_ApplicantManagementCustomGroupPostProcessor extends CRM_Civicase_Service_BaseCustomGroupPostProcessor {

  const CUSTOM_GROUP_SUBTYPES = 'ApplicantManagementCustomGroupSubTypes';

  /**
   * Stores the CaseManagement Post process helper.
   *
   * @var \CRM_CiviAwards_Helper_CustomGroupPostProcess
   *   Post process helper class.
   */
  private $postProcessHelper;

  /**
   * Constructor function.
   *
   * @param \CRM_CiviAwards_Helper_CustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CustomGroupPostProcessHelper $postProcessHelper) {
    parent::__construct();
    $this->postProcessHelper = $postProcessHelper;
  }

  /**
   * Saves applicant management case type related custom groups.
   *
   * This function allows saving the Custom groups that extends a Case category
   * (that belongs to the Applicant Management Instance) entity to extend
   * the Case entity and to save the ID of the case category in the
   * `extends_entity_column_id` of the `custom_group` table and also to store
   * all case types for the Case category in the `extends_entity_column_value`
   * column.
   *
   * The `extends_entity_column_value` will contain award subtypes and this
   * is converted to equivalent case types that has these subtypes attached and
   * saved.
   *
   * @param \CRM_Core_DAO_CustomGroup $customGroup
   *   Custom Group Object.
   */
  public function saveCustomGroupForCaseCategory(CustomGroup $customGroup) {
    if (empty($this->caseTypeCategories[$customGroup->extends])) {
      return;
    }

    $subTypes = empty($customGroup->extends_entity_column_value) || $customGroup->extends_entity_column_value === 'null'
      ? []
      : explode(CRM_Core_DAO::VALUE_SEPARATOR, $customGroup->extends_entity_column_value);
    $this->postProcessHelper->updateCustomGroupSubTypesList($customGroup->id, $subTypes);

    $caseTypeIds = $this->postProcessHelper->getCaseTypesForSubType($this->caseTypeCategories[$customGroup->extends], $subTypes);
    $ids = CRM_Core_DAO::VALUE_SEPARATOR . 0 . CRM_Core_DAO::VALUE_SEPARATOR;
    if (!empty($caseTypeIds)) {
      $ids = CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $caseTypeIds) . CRM_Core_DAO::VALUE_SEPARATOR;
    }

    $this->updateCustomGroup($customGroup, $ids);
  }

}
