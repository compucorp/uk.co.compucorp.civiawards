<?php

use CRM_CiviAwards_Service_AwardImport as AwardImportService;
use CRM_CiviAwards_Helper_ApplicantReview as ApplicantReviewHelper;

/**
 * Class for test CRM_CiviAwards_Service_AwardImport.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_AwardImportTest extends BaseHeadlessTest {

  /**
   * Test the creation of the Case Type and the AwardDetail.
   */
  public function testImportAwardWithBaseInformation() {
    $params = $this->getBaseParamsForAward();

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);

    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);
    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
  }

  /**
   * Test the Award creation with manager information.
   *
   * In this test only one manager is specified.
   */
  public function testImportAwardWithSingleManagerInformation() {
    $contactId = CRM_CiviAwards_Test_Fabricator_Contact::fabricate()['id'];
    $params = array_merge(
      $this->getBaseParamsForAward(),
      [
        'award_manager' => $contactId,
      ]
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);

    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertEquals($contactId, $awardDetail['award_manager'][0]);
  }

  /**
   * Test the Award creation with manager information.
   *
   * In this test two managers are specified.
   */
  public function testImportAwardWithMultipleManagerInformation() {
    $contactIdOne = CRM_CiviAwards_Test_Fabricator_Contact::fabricate()['id'];
    $contactIdTwo = CRM_CiviAwards_Test_Fabricator_Contact::fabricate()['id'];
    $params = array_merge(
      $this->getBaseParamsForAward(),
      [
        'award_manager' => "$contactIdOne,$contactIdTwo",
      ]
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);

    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertCount(2, $awardDetail['award_manager']);
    $this->assertContains($contactIdOne, $awardDetail['award_manager']);
    $this->assertContains($contactIdTwo, $awardDetail['award_manager']);
  }

  /**
   * Test the creation of review fields.
   */
  public function testImportAwardWithReviewFieldsInformation() {
    $customFields = [$this->createReviewField()];

    $params = array_merge(
      $this->getBaseParamsForAward(),
      [
        'review_fields' => json_encode($customFields),
      ]
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);
    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertEquals($customFields, $awardDetail['review_fields']);
  }

  /**
   * Test the creation of ReviewPanel.
   */
  public function testImportAwardWithReviewPanelInformation() {
    $paramsForReviewPanel = [
      'review_panel_title' => 'Review Panel Title',
      'review_panel_is_active' => 1,
    ];
    $params = array_merge(
      $this->getBaseParamsForAward(),
      $paramsForReviewPanel
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);
    $awardReviewPanel = civicrm_api3('AwardReviewPanel', 'getsingle', [
      'title' => $paramsForReviewPanel['review_panel_title'],
    ]);

    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertEquals($paramsForReviewPanel['review_panel_title'], $awardReviewPanel['title']);
    $this->assertEquals($paramsForReviewPanel['review_panel_is_active'], $awardReviewPanel['is_active']);
  }

  /**
   * Test the creation of case type with activity information.
   */
  public function testImportAwardWithActivityInformation() {
    $activityTypes = [
      ['name' => 'Email'],
      ['name' => 'Follow up'],
    ];

    $params = array_merge(
      $this->getBaseParamsForAward(),
      [
        'activity_types' => json_encode($activityTypes),
      ]
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);
    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertEquals($activityTypes, $awardCaseType['definition']['activityTypes']);
  }

  /**
   * Test the creation of case type with statuses information.
   */
  public function testImportAwardWithStatusesInformation() {
    $statuses = ['won', 'lost', 'ongoing'];

    $params = array_merge(
      $this->getBaseParamsForAward(),
      [
        'statuses' => json_encode($statuses),
      ]
    );

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'getsingle', [
      'title' => $params['title'],
    ]);
    $awardDetail = civicrm_api3('AwardDetail', 'getsingle', [
      'case_type_id' => $awardCaseType['name'],
    ]);
    $this->assertCaseTypeInformation($params, $awardCaseType);
    $this->assertDetailsInformation($params, $awardDetail);
    $this->assertEquals($statuses, $awardCaseType['definition']['statuses']);
  }

  /**
   * Test that in case of error, all the operation is cancelled.
   */
  public function testErrorImportingAwardWithDetailInformation() {
    $params = $this->getBaseParamsForAward();
    $params['start_date'] = 'invalid date';
    $this->expectException(API_Exception::class);
    $initialAwardCount = civicrm_api3('AwardDetail', 'getcount');

    (new AwardImportService())->create($params);

    $awardCaseType = civicrm_api3('CaseType', 'get', [
      'title' => $params['title'],
    ]);
    // No case type created.
    $this->assertEquals(0, $awardCaseType['count']);
    // No new award details found.
    $this->assertEquals($initialAwardCount, civicrm_api3('AwardDetail', 'getcount'));
  }

  /**
   * Base information for creating the Case Type and Details.
   *
   * @return array
   *   Base information.
   */
  private function getBaseParamsForAward() {
    $suffix = rand();
    return [
      'title' => 'Test title asdf ' . $suffix,
      'description' => 'test desc',
      'is_active' => 1,
      'award_subtype' => 2,
      'start_date' => date('Y-m-d', strtotime('+1 day')),
      'end_date' => date('Y-m-d', strtotime("+14 days")),
      'is_template' => '',
      'award_manager' => '',
      'review_fields' => '',
    ];
  }

  /**
   * Helper for asserting Case Type information.
   *
   * @param array $expected
   *   Expected information.
   * @param array $actual
   *   Information received.
   */
  private function assertCaseTypeInformation(array $expected, array $actual) {
    $this->assertEquals($expected['title'], $actual['title']);
    $this->assertEquals($expected['description'], $actual['description']);
    $this->assertEquals($expected['is_active'], $actual['is_active']);

    $caseTypeCategoryForAwards = (new AwardImportService())->getCaseCategoryValueForAwards();
    $this->assertEquals($caseTypeCategoryForAwards, $actual['case_type_category']);

    // We only check the existence of the keys in definition field here,
    // content is checked on specific tests.
    $this->assertArrayHasKey('activityTypes', $actual['definition']);
    $this->assertArrayHasKey('caseRoles', $actual['definition']);
  }

  /**
   * Helper for asserting Award Detail information.
   *
   * @param array $expected
   *   Expected information.
   * @param array $actual
   *   Information received.
   */
  private function assertDetailsInformation(array $expected, array $actual) {
    $this->assertEquals($expected['start_date'], $actual['start_date']);
    $this->assertEquals($expected['end_date'], $actual['end_date']);
    $this->assertEquals($expected['award_subtype'], $actual['award_subtype']);
  }

  /**
   * Creates a review field.
   *
   * @return array
   *   Review field details.
   */
  private function createReviewField() {
    $suffix = rand();
    $customField = civicrm_api3('CustomField', 'create', [
      'custom_group_id' => ApplicantReviewHelper::getApplicantReviewCustomGroupId(),
      'name' => 'test_review_field_' . $suffix,
      'label' => 'Test Review Field ' . $suffix,
      'data_type' => 'Boolean',
      'default_value' => 1,
      'html_type' => 'Radio',
      'required' => 1,
      'weight' => 2,
    ]);

    $customField = array_shift($customField['values']);

    return [
      'id' => $customField['id'],
      'required' => $customField['is_required'],
      'weight' => $customField['weight'],
    ];
  }

}
