<?php

use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Service_AwardApplicationContactAccess as ApplicationContactAccess;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;

/**
 * @group headless
 */
class CRM_CiviAwards_Service_AwardApplicationContactAccessTest extends BaseHeadlessTest {

  public function testGetThrowsExceptionWhenNoAwardPanelsExistForAward() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;
    $awardPanelContact = new AwardPanelContact();

    $this->setExpectedException(
      'Exception',
      'No award panels available for this award type'
    );
    $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact);
  }

  public function testGetReturnsTheContactAccessForAContact() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [1,2],
          'application_status' => [5,6],
          'anonymize_application' => 0
        ]
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [6,8],
          'application_status' => [9,3],
          'anonymize_application' => 1
        ]
      ]
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);
    // anonymize application will be false overral because more permission takes precedence over less
    // less permissions.

    $expectedResult = [
      'application_tags' => [1,2,6,8],
      'application_status' => [3,5,6,9],
      'anonymize_application' => FALSE
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  public function testAnonymizeApplicationReturnsTrueForContactWhenItIsSetToBeTrue() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1
        ]
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1
        ]
      ]
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);
    $expectedResult = [
      'application_tags' => [],
      'application_status' => [],
      'anonymize_application' => TRUE
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  private function getAwardPanelContactObject($awardPanel, $contactId) {
    $awardPanelContact = $this->prophesize(AwardPanelContact::class);
    foreach ($awardPanel as $awardPanel) {
      $awardPanelContact->get($awardPanel->id, [$contactId])->willReturn(TRUE);
    }

    return $awardPanelContact->reveal();
  }
}
