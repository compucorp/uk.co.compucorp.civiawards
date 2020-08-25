<?php

use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Test_Fabricator_Case as CaseFabricator;
use CRM_CiviAwards_Service_AwardApplicationContactAccess as ApplicationContactAccess;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;

/**
 * Class to test handling access of a contact to an application.
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
      [
        'application_tags' => [1, 2],
        'application_status' => [5, 6],
      ],
      [
        'application_tags' => [6, 8],
        'application_status' => [9, 3],
      ],
      [
        'application_tags' => [],
        'application_status' => [],
      ],
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->get($contactId, $caseType['id'], $awardPanelContact));
  }

  /**
   * Test Get Review Access When Panel Setting Is For All Case tags.
   */
  public function testGetReviewAccessForContactAndPanelSettingIsForAllCaseTags() {
    $caseType = CaseTypeFabricator::fabricate();
    $caseStatus = 1;
    $applicationContactAccess = new ApplicationContactAccess();
    $caseData = CaseFabricator::fabricateWithTags(
      ['Tag 1', 'Tag 2'],
      ['status_id' => $caseStatus, 'case_type_id' => $caseType['id']]
    );
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [1],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [3, 4],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [6, 7],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus, 2],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2, 5],
        ],
      ],
    ];

    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);

    // Panel 1 and Panel 3 meets the case status and tags criteria.
    // So Contact will view combined status to move to for both panels
    // Since anonymization is not restricted in Panel 3, Contact will be able
    // to view unanonymized data.
    $expectedResult = [
      'status_to_move_to' => [1, 2, 5],
      'anonymize_application' => FALSE,
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->getReviewAccess($contactId, $caseData['case']['id'], $awardPanelContact));
  }

  /**
   * Test Get Review Access When Panel Setting Is For Specific Case tags.
   */
  public function testGetReviewAccessForContactAndAndPanelSettingIsForSpecificCaseTags() {
    $caseType = CaseTypeFabricator::fabricate();
    $caseStatus = 1;
    $applicationContactAccess = new ApplicationContactAccess();
    $caseData = CaseFabricator::fabricateWithTags(
      ['Tag 1', 'Tag 2'],
      ['status_id' => $caseStatus, 'case_type_id' => $caseType['id']]
    );

    $tag1Id = $caseData['tags']['0']['id'];
    $tag2Id = $caseData['tags']['1']['id'];

    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'application_tags' => [$tag1Id],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [1],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [3, 4],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [6, 7],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'application_tags' => [$tag1Id, $tag2Id],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [8, 9],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus, 2],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2, 5],
        ],
      ],
    ];

    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);

    // Panel 1, Panel 3, Panel 4 meets the case status and case tags criteria.
    // So Contact will view combined status to move to for the panels
    // Since anonymization is not restricted in Panel 3 & 4, Contact will
    // be able to view unanonymized data.
    $expectedResult = [
      'status_to_move_to' => [1, 2, 5, 8, 9],
      'anonymize_application' => FALSE,
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->getReviewAccess($contactId, $caseData['case']['id'], $awardPanelContact));
  }

  /**
   * Test When Panel Setting Is For All Tags And Contact Cannot View Anonymised.
   */
  public function testGetReviewAccessForContactAndPanelSettingIsForAllTagsAndContactCanNotViewAnonymisedData() {
    $caseType = CaseTypeFabricator::fabricate();
    $caseStatus = 1;
    $applicationContactAccess = new ApplicationContactAccess();
    $caseData = CaseFabricator::fabricateWithTags(
      ['Tag 1', 'Tag 2'],
      ['status_id' => $caseStatus, 'case_type_id' => $caseType['id']]
    );
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [1],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus, 2],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2, 5],
        ],
      ],
    ];

    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);

    // Panel 1 and Panel 2 meets the case status and tag criteria.
    // So Contact will view combined status to move to for both panels
    // Since anonymization is restricted in all panels, Contact will not be able
    // to view unanonymized data.
    $expectedResult = [
      'status_to_move_to' => [1, 2, 5],
      'anonymize_application' => TRUE,
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->getReviewAccess($contactId, $caseData['case']['id'], $awardPanelContact));
  }

  /**
   * Test Get Review Access When Panel Has All Tags And Status Enabled.
   */
  public function testGetReviewAccessForContactWhenContactBelongsToPanelWhereAllTagsAndStatusIsAllowed() {
    $caseType = CaseTypeFabricator::fabricate();
    $caseStatus = 1;
    $applicationContactAccess = new ApplicationContactAccess();
    $caseData = CaseFabricator::fabricateWithTags(
      ['Tag 1', 'Tag 2'],
      ['status_id' => $caseStatus, 'case_type_id' => $caseType['id']]
    );
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [],
          'application_tags' => [],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [1, 3, 6],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [3, 4],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [6, 7],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2, 5],
        ],
      ],
    ];

    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);

    // Panel 1 and Panel 3 meets the case status criteria.
    // So Contact will view combined status to move to for both panels
    // Since anonymization is not restricted in Panel 3, Contact will be able
    // to view unanonymized data.
    $expectedResult = [
      'status_to_move_to' => [1, 2, 3, 5, 6],
      'anonymize_application' => FALSE,
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->getReviewAccess($contactId, $caseData['case']['id'], $awardPanelContact));
  }

  /**
   * Test Contact Can Move to Status When Case And Panel Both Has No Tags.
   */
  public function testContactIsAbleToMoveToRelevantStatusWhenCaseHasNoTagsAndPanelTagsIsEmpty() {
    $caseType = CaseTypeFabricator::fabricate();
    $caseStatus = 1;
    $applicationContactAccess = new ApplicationContactAccess();
    $caseData = CaseFabricator::fabricate(
      ['status_id' => $caseStatus, 'case_type_id' => $caseType['id']]
    );
    $contactId = 1;

    $params = [
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [3],
          'application_tags' => [],
          'anonymize_application' => 1,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [1, 3, 6],
        ],
      ],
      [
        'case_type_id' => $caseType['id'],
        'visibility_settings' => [
          'application_status' => [$caseStatus],
          'anonymize_application' => 0,
          'is_application_status_restricted' => 1,
          'restricted_application_status' => [2, 5],
        ],
      ],
    ];

    $awardPanel = [];
    foreach ($params as $param) {
      $awardPanel[] = AwardReviewPanelFabricator::fabricate($param);
    }

    $awardPanelContact = $this->getAwardPanelContactObject($awardPanel, $contactId);

    // If a case has no tags it is accessible by panel user
    // when panel also does not contain any tags.
    $expectedResult = [
      'status_to_move_to' => [2, 5],
      'anonymize_application' => FALSE,
    ];

    $this->assertEquals($expectedResult, $applicationContactAccess->getReviewAccess($contactId, $caseData['id'], $awardPanelContact));
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
