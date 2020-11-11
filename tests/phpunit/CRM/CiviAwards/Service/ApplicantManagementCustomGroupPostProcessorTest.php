<?php

use CRM_CiviAwards_Helper_CustomGroupPostProcess as ApplicantManagementPostProcessHelper;
use CRM_Core_BAO_CustomGroup as CustomGroup;
use CRM_CiviAwards_Service_ApplicantManagementCustomGroupPostProcessor as ApplicantManagementCustomGroupPostProcessor;

/**
 * Runs tests on ApplicantManagementCustomGroupPostProcessor methods.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_ApplicantManagementCustomGroupPostProcessorTest extends BaseHeadlessTest {

  /**
   * Test for the SaveCustomGroupForCaseCategory method.
   *
   * @param int $expectedExtendsId
   *   Expected Extends Id.
   * @param string $expectedExtendsValue
   *   Expected extends value.
   * @param mixed $caseTypes
   *   Case types for the cases category.
   *
   * @dataProvider getDataForApplicantManagementCustomGroup
   */
  public function testSaveCustomGroupForCaseCategory(
    $expectedExtendsId,
    $expectedExtendsValue,
    $caseTypes
  ) {
    $customGroup = $this->getCustomGroupObject();
    $applicantManagementHelper = $this->getApplicantManagementHelperMock($caseTypes);
    $applicantManagementProcessor = new ApplicantManagementCustomGroupPostProcessor($applicantManagementHelper);
    $applicantManagementProcessor->saveCustomGroupForCaseCategory($customGroup);
    $this->assertEquals($expectedExtendsId, $customGroup->extends_entity_column_id);
    $this->assertEquals($expectedExtendsValue, $customGroup->extends_entity_column_value);
    $this->assertEquals('Case', $customGroup->extends);
  }

  /**
   * Returns a mock object for ApplicantManagementHelper.
   *
   * @param mixed $toReturn
   *   What to return for the getCaseTypesForSubType method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   ApplicantManagementHelper mock object.
   */
  private function getApplicantManagementHelperMock($toReturn) {
    $applicantManagementHelper = $this->getMockBuilder(ApplicantManagementPostProcessHelper::class)
      ->setMethods(['getCaseTypesForSubType'])
      ->getMock();
    $applicantManagementHelper->method('getCaseTypesForSubType')->willReturn($toReturn);

    return $applicantManagementHelper;
  }

  /**
   * Returns a custom group object.
   *
   * @return CRM_Core_BAO_CustomGroup
   *   Custom group object.
   */
  private function getCustomGroupObject() {
    $customGroup = new CustomGroup();
    $customGroup->extends = 'awards';
    $customGroup->title = 'Group' . uniqid();

    return $customGroup;
  }

  /**
   * Provides sample data for the SaveCustomGroupForCaseCategory test.
   *
   * @return array
   *   An array of sample data.
   */
  public function getDataForApplicantManagementCustomGroup() {
    return [
      [
        2,
        CRM_Core_DAO::VALUE_SEPARATOR .
        implode(CRM_Core_DAO::VALUE_SEPARATOR, [1, 2, 3]) .
        CRM_Core_DAO::VALUE_SEPARATOR,
        [1, 2, 3],
      ],
      [
        2,
        'null',
        NULL,
      ],
      [
        2,
        CRM_Core_DAO::VALUE_SEPARATOR . implode(CRM_Core_DAO::VALUE_SEPARATOR, [4]) . CRM_Core_DAO::VALUE_SEPARATOR,
        [4],
      ],
    ];
  }

}
