<?php

use CRM_CiviAwards_Helper_CustomGroupPostProcess as ApplicantManagementPostProcessHelper;
use CRM_CiviAwards_Service_ApplicantManagementCustomGroupDisplayFormatter as ApplicantManagementCustomGroupDisplayFormatter;
use CRM_CiviAwards_Setup_ProcessAwardsCategoryForCustomGroupSupport as CaseCategoryForCustomGroupSupport;

/**
 * Runs tests for ApplicantManagementCustomGroupDisplayFormatterTest.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_ApplicantManagementCustomGroupDisplayFormatterTest extends BaseHeadlessTest {

  /**
   * Test process display function.
   */
  public function testProcessDisplay() {
    $caseCategories = [3 => 'Awards'];
    $expectedDisplay = CaseCategoryForCustomGroupSupport::AWARDS_CATEGORY_CG_LABEL;
    $cgExtends = ['Awards' => $expectedDisplay];
    $subTypes = [3 => [1 => 1, 2 => 2]];
    $awardManagementHelper = $this->getApplicantManagementHelperMock($caseCategories, $cgExtends, $subTypes);
    $row = ['id' => 3, 'extends_entity_column_id' => 3];
    $displayFormatter = new ApplicantManagementCustomGroupDisplayFormatter($awardManagementHelper);
    $displayFormatter->processDisplay($row);
    $this->assertEquals($expectedDisplay, $row['extends_display']);
    $this->assertEquals(1, $row['extends_entity_column_value']);
  }

  /**
   * Returns a mock object for ApplicantManagementHelper.
   *
   * @param mixed $caseCategoryReturn
   *   What to return for the `getCaseTypeCategories` method.
   * @param mixed $cgExtendReturn
   *   What to return for the `getCgExtendValues` method.
   * @param mixed $subTypesListReturn
   *   What to return for the `getCgExtendValues` method.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   ApplicantManagementHelper mock object.
   */
  private function getApplicantManagementHelperMock($caseCategoryReturn, $cgExtendReturn, $subTypesListReturn) {
    $applicantManagementHelper = $this->getMockBuilder(ApplicantManagementPostProcessHelper::class)
      ->setMethods(
        [
          'getCaseTypeCategories',
          'getCgExtendValues',
          'getCustomGroupSubTypesList',
          'getAwardSubTypes',
        ]
      )
      ->getMock();
    $applicantManagementHelper->method('getCaseTypeCategories')->willReturn($caseCategoryReturn);
    $applicantManagementHelper->method('getCgExtendValues')->willReturn($cgExtendReturn);
    $applicantManagementHelper->method('getCustomGroupSubTypesList')->willReturn($subTypesListReturn);
    $applicantManagementHelper->method('getAwardSubTypes')->willReturn([1 => 1]);

    return $applicantManagementHelper;
  }

}
