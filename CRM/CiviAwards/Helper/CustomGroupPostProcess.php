<?php

use CRM_CiviAwards_Service_ApplicantManagementCustomGroupPostProcessor as ApplicantManagementCustomGroupPostProcessor;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseCategoryHelper;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Custom Group Post-Process Applicant management helper.
 */
class CRM_CiviAwards_Helper_CustomGroupPostProcess extends CRM_Civicase_Helper_InstanceCustomGroupPostProcess {

  /**
   * Returns the case types for the award sub-types.
   *
   * The function returns case types belonging to the
   * case category passed. If sub-types is empty, case
   * types are returned for all award subtypes for the
   * given case category.
   *
   * @param int $caseCategoryValue
   *   Case category value.
   * @param array $subTypes
   *   Award Subtypes.
   */
  public function getCaseTypesForSubType($caseCategoryValue, array $subTypes = []) {
    $params = [
      'return' => ['case_type_id'],
      'case_type_id.case_type_category' => $caseCategoryValue,
    ];

    $subTypes = array_filter($subTypes);
    if (!empty($subTypes)) {
      $params['award_subtype'] = ['IN' => $subTypes];
    }

    $result = civicrm_api3('AwardDetail', 'get', $params);

    if (empty($result['values'])) {
      return [];
    }

    return array_column($result['values'], 'case_type_id');
  }

  /**
   * Returns the sub types for the given case types.
   *
   * @param array $caseTypes
   *   Case Types.
   */
  public function getSubTypesForCaseType(array $caseTypes) {
    $result = civicrm_api3('AwardDetail', 'get', [
      'return' => ['award_subtype'],
      'case_type_id' => ['IN' => $caseTypes],
    ]);

    if (empty($result['values'])) {
      return [];
    }

    return array_column($result['values'], 'award_subtype');
  }

  /**
   * Gets the custom groups that the case type is associated with.
   *
   * @param int $caseTypeId
   *   Case type Id.
   *
   * @return array
   *   Mismatched case type custom groups
   */
  public function getCaseTypeCustomGroupsWithCategoryMatch($caseTypeId) {
    $caseCategory = $this->getCaseCategoryForCaseType($caseTypeId);
    $caseCategoryId = $caseCategory['case_type_category'];

    $result = civicrm_api3('CustomGroup', 'get', [
      'extends' => 'Case',
      'extends_entity_column_id' => ['IN' => [$caseCategoryId]],
      'extends_entity_column_value' => ['LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $caseTypeId . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
      'options' => ['limit' => 0],
    ]);

    return $result['values'];
  }

  /**
   * Saves the original subtypes for the custom group.
   *
   * Whe the subtypes is saved for a custom group, on post process
   * these subtypes are converted to equivalent case types but these
   * subtypes list need to be preserved so it can be used for other
   * necessary business logic.
   *
   * @param int $customGroupId
   *   Custom group Id.
   * @param array $subtypesList
   *   Subtypes for the custom group.
   */
  public function updateCustomGroupSubTypesList($customGroupId, array $subtypesList) {
    $subTypeSettingName = ApplicantManagementCustomGroupPostProcessor::CUSTOM_GROUP_SUBTYPES;
    $customGroupSubTypes = Civi::settings()->get($subTypeSettingName);
    if (empty($customGroupSubTypes)) {
      $customGroupSubTypes = [];
    }

    $subtypesList = array_filter($subtypesList);
    if (!empty($subtypesList) && $subtypesList[0] == 'null') {
      $subtypesList = array_keys(CaseCategoryHelper::getSubTypes());
    }

    $customGroupSubTypes[$customGroupId] = $subtypesList;

    Civi::settings()->set($subTypeSettingName, $customGroupSubTypes);
  }

  /**
   * Returns the custom group subtype list.
   *
   * @param int|null $customGroupId
   *   Custom group ID.
   *
   * @return array
   *   Subtypes for custom group.
   */
  public function getCustomGroupSubTypesList($customGroupId = NULL) {
    $subTypeSettingName = ApplicantManagementCustomGroupPostProcessor::CUSTOM_GROUP_SUBTYPES;
    $customGroupSubTypes = Civi::settings()->get($subTypeSettingName);
    if (empty($customGroupId)) {
      return $customGroupSubTypes;
    }

    return !empty($customGroupSubTypes[$customGroupId]) ? $customGroupSubTypes[$customGroupId] : [];
  }

  /**
   * Provide the list of sub types.
   *
   * @return array
   *   List of sub types.
   */
  public function getAwardSubTypes() {
    return AwardDetail::buildOptions('award_subtype');
  }

}
