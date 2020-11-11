<?php

use CRM_CiviAwards_Helper_CustomGroupPostProcess as ApplicantManagementPostProcessHelper;
use CRM_CiviAwards_Service_ApplicantManagementAwardDetailPostProcessor as ApplicantManagementCaseTypePostProcessor;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Runs tests on ApplicantManagementAwardDetailPostProcessor methods.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_ApplicantManagementAwardDetailPostProcessorTest extends BaseHeadlessTest {

  /**
   * Test process case type custom group on case type create.
   *
   * @param int $caseTypeId
   *   Case type Id.
   * @param mixed $entityColumnValues
   *   Custom group entity column values.
   * @param array $subTypes
   *   Sub types.
   * @param mixed $expectedEntityColumnValues
   *   Expected entity column values for matched custom groups.
   *
   * @dataProvider getDataForTestProcessCaseTypeCustomGroupsOnCreate
   */
  public function testProcessCaseTypeCustomGroupsOnCreate(
    $caseTypeId,
    $entityColumnValues,
    array $subTypes,
    $expectedEntityColumnValues
  ) {
    $customGroup = $this->createCustomGroup($entityColumnValues);
    $awardDetail = $this->createAwardDetail($caseTypeId);
    $customGroupId = $customGroup[0]['id'];
    $applicantManagementHelper = $this->getApplicantManagementHelperMock($customGroup, [], $subTypes);
    $applicantManagementProcessor = new ApplicantManagementCaseTypePostProcessor($applicantManagementHelper);
    $applicantManagementProcessor->processCaseTypeCustomGroupsOnCreate($awardDetail);
    $expectedCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $customGroupId]);

    $this->assertEquals($expectedCustomGroup['extends_entity_column_value'], $expectedEntityColumnValues);
  }

  /**
   * Test process case type custom group on case type update.
   *
   * @param int $caseTypeId
   *   Case type Id.
   * @param mixed $entityColumnValues
   *   Custom group entity column values.
   * @param mixed $mismatchEntityColumnValues
   *   Entity column values for mismatched custom groups.
   * @param mixed $subTypes
   *   Sub types.
   * @param mixed $expectedEntityColumnValues
   *   Expected entity column values for matched custom groups.
   * @param mixed $correctedMismatchValues
   *   Expected/corrected entity column values for mismatched custom groups.
   *
   * @dataProvider getDataForTestProcessCaseTypeCustomGroupsOnUpdate
   */
  public function testProcessCaseTypeCustomGroupsOnUpdate(
    $caseTypeId,
    $entityColumnValues,
    $mismatchEntityColumnValues,
    $subTypes,
    $expectedEntityColumnValues,
    $correctedMismatchValues
  ) {
    $customGroup = $this->createCustomGroup($entityColumnValues);
    $awardDetail = $this->createAwardDetail($caseTypeId);
    $mismatchCustomGroup = $this->createCustomGroup($mismatchEntityColumnValues);
    $customGroupId = $customGroup[0]['id'];
    $mismatchCustomGroupId = $mismatchCustomGroup[0]['id'];
    $applicantManagementHelper = $this->getApplicantManagementHelperMock($customGroup, $mismatchCustomGroup, $subTypes);
    $applicantManagementProcessor = new ApplicantManagementCaseTypePostProcessor($applicantManagementHelper);
    $applicantManagementProcessor->processCaseTypeCustomGroupsOnUpdate($awardDetail);
    $expectedCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $customGroupId]);
    $expectedMismatchCustomGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $mismatchCustomGroupId]);

    $this->assertEquals($expectedCustomGroup['extends_entity_column_value'], $expectedEntityColumnValues);
    $this->assertEquals($expectedMismatchCustomGroup['extends_entity_column_value'], $correctedMismatchValues);
  }

  /**
   * Data set for TestProcessCaseTypeCustomGroupsOnCreate.
   *
   * @return array
   *   Data set.
   */
  public function getDataForTestProcessCaseTypeCustomGroupsOnCreate() {
    return [
      [5, [1, 2, 3], [1, 2], [1, 2, 3, 5]],
      [4, [1], [1, 3], [1, 4]],
      [4, [1], [4, 2], [1, 4]],
      [5, NULL, [1, 5], [5]],
    ];
  }

  /**
   * Data set for TestProcessCaseTypeCustomGroupsOnUpdate.
   *
   * @return array
   *   Data set.
   */
  public function getDataForTestProcessCaseTypeCustomGroupsOnUpdate() {
    return [
      [7, [1, 2, 3], [4, 7, 6], [1, 3], [1, 2, 3, 7], [4, 6]],
      [8, [1, 2, 3], [4, 5, 8], [4, 2], [1, 2, 3, 8], [4, 5]],
      [2, NULL, [4, 5, 2], [5, 2], [2], [4, 5]],
    ];
  }

  /**
   * Creates a custom group object and returns value as array.
   *
   * @param array|null $entityColumnValues
   *   Entity custom values for custom group.
   */
  private function createCustomGroup($entityColumnValues) {
    $cusGroup = new CRM_Core_BAO_CustomGroup();
    $cusGroup->title = 'Group' . uniqid();
    $cusGroup->extends = 'awards';
    $entityColValue = is_null($entityColumnValues) ? 'null' : CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, $entityColumnValues) . CRM_Core_DAO::VALUE_SEPARATOR;
    $cusGroup->extends_entity_column_value = $entityColValue;
    $cusGroup->save();

    return [
      [
        'id' => $cusGroup->id,
        'extends_entity_column_value' => $entityColumnValues,
      ],
    ];
  }

  /**
   * Creates an award detail bao.
   *
   * @param int $caseTypeId
   *   Case type id.
   */
  private function createAwardDetail($caseTypeId) {
    $awardDetail = new AwardDetail();
    $awardDetail->case_type_id = $caseTypeId;

    return $awardDetail;
  }

  /**
   * Returns a mock object for ApplicantManagementHelper.
   *
   * @param mixed $customGroupReturn
   *   What to return for the getCaseTypeCustomGroups' method.
   * @param mixed $customGroupMismatchReturn
   *   What to return for the
   *   getCaseTypeCustomGroupsWithCategoryMismatch method.
   * @param array $subTypes
   *   Sub type list.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   CaseManagementHelper mock object.
   */
  private function getApplicantManagementHelperMock($customGroupReturn, $customGroupMismatchReturn, array $subTypes) {
    $applicantManagementHelper = $this->getMockBuilder(ApplicantManagementPostProcessHelper::class)
      ->setMethods(
        [
          'getCaseTypeCustomGroups',
          'getCaseTypeCustomGroupsWithCategoryMismatch',
          'getCaseCategoryForCaseType',
          'getSubTypesForCaseType',
          'getCustomGroupSubTypesList',
        ]
      )
      ->getMock();
    $applicantManagementHelper->method('getCaseTypeCustomGroups')->willReturn($customGroupReturn);
    $applicantManagementHelper->method('getCaseCategoryForCaseType')->willReturn(1);
    $applicantManagementHelper->method('getCaseTypeCustomGroupsWithCategoryMismatch')->willReturn($customGroupMismatchReturn);
    $applicantManagementHelper->method('getSubTypesForCaseType')->willReturn($subTypes);
    $applicantManagementHelper->method('getCustomGroupSubTypesList')->willReturn([
      $customGroupReturn[0]['id'] => $subTypes,
    ]);

    return $applicantManagementHelper;
  }

}
