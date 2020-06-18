<?php

use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Service_AwardApplicationContactAccess as ApplicationContactAccess;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;

/**
 * CRM_CiviAwards_Service_AwardApplicationContactAccessTest.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_AwardApplicationContactAccessTest extends BaseHeadlessTest {

  /**
   * Test Get Throws Exception When No AwardPanels Exist For Award.
   */
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

  /**
   * Test Get Throws Exception When No Contact Does Belong To Any Award Panels.
   */
  public function testGetThrowsExceptionWhenNoContactDoesBelongToAnyAwardPanels() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;
    $awardPanelContact = new AwardPanelContact();

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $this->setExpectedException(
      'Exception',
      'This contact does not have any access to the panels on this Award'
    );
    $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact);
  }

  /**
   * Test Get Returns The Contact Access For A Contact.
   */
  public function testGetReturnsTheContactAccessForContact() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [1, 2],
          'application_status' => [5, 6],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 0,
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [6, 8],
          'application_status' => [9, 3],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);
    // Anonymize application will be false overral because more permission takes
    // precedence over less less permissions.
    $expectedResult = [
      'application_tags' => [1, 2, 6, 8],
      'application_status' => [3, 5, 6, 9],
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  /**
   * Test Anonymize Application Returns True For Contact When True.
   */
  public function testAnonymizeApplicationReturnsTrueForContactWhenItIsSetToBeTrue() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);
    $expectedResult = [
      'application_tags' => [],
      'application_status' => [],
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  /**
   * Test Visibility Settings Returns Items With Higher Values.
   */
  public function testVisibilitySettingsReturnsItemsWithHigherValuesWhenMoreThanOneAwardPanel() {
    $caseType = CaseTypeFabricator::fabricate();
    $applicationContactAccess = new ApplicationContactAccess();
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [],
          'application_status' => [],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_tags' => [6, 8],
          'application_status' => [9, 3],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 0,
        ],
      ],
    ];
    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);
    // Anonymize application will be false overral because more permission takes
    // precedence over less less permissions.
    // Application status will return empty because empty means contact can
    // filter all statuses and this has higher value.
    // Application tags will return empty because empty means contact can filter
    // all tags and this has higher value.
    // Status to move to is a bit different because by default contact is not
    // able to move to any So will be 2.
    $expectedResult = [
      'application_tags' => [],
      'application_status' => [],
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  /**
   * Returns Award Panel Object for Contact.
   *
   * @param object $awardPanel
   *   Award Panel.
   * @param int $contactId
   *   Contact Id.
   *
   * @return object
   *   Award Panel Contact.
   */
  private function getAwardPanelContactObject($awardPanel, $contactId) {
    $awardPanelContact = $this->prophesize(AwardPanelContact::class);
    foreach ($awardPanel as $awardPanel) {
      $awardPanelContact->get($awardPanel->id, [$contactId])->willReturn(TRUE);
    }

    return $awardPanelContact->reveal();
  }

}
