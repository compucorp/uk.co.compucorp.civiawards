<?php

use CRM_CiviAwards_Service_AwardImport as AwardImportService;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;

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
   * Test that in case of error, all the operation is cancelled.
   */
  public function testErrorImportingAwardWithInvalidDate() {
    $params = $this->getBaseParamsForAward();
    $params['start_date'] = 'invalid date';
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage(
      "Exception while saving the AwardDetail: start_date is not a valid date: {$params['start_date']}"
    );
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
   * Test that the operation fails when no award title is received.
   */
  public function testErrorImportingAwardWithEmptyTitle() {
    $params = $this->getBaseParamsForAward();
    $params['title'] = '';
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage('Invalid param received: Award Title should not be empty');
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
   * Test that the operation fails when no award subtype is received.
   */
  public function testErrorImportingAwardWithoutSubtype() {
    $params = $this->getBaseParamsForAward();
    $params['award_subtype'] = '';
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage(
      'Exception while saving the AwardDetail: Award Subtype should not be empty'
    );
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
   * Test that the operation fails when no award start date is received.
   */
  public function testErrorImportingAwardWithoutStartDate() {
    $params = $this->getBaseParamsForAward();
    $params['start_date'] = '';
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage(
      'Exception while saving the AwardDetail: Award Start Date should not be empty'
    );
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
   * Test that the operation fails when no award manager is received.
   */
  public function testErrorImportingAwardWithoutManager() {
    $params = $this->getBaseParamsForAward();
    $params['award_manager'] = '';
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage(
      'Exception while saving the AwardDetail: Award Manager should not be empty'
    );
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
   * Test that the operation fails when an invalid manager is received.
   */
  public function testErrorImportingAwardWithInvalidManager() {
    $params = $this->getBaseParamsForAward();
    $params['award_manager'] = rand(1000000, 10000000);
    $this->expectException(API_Exception::class);
    $this->expectExceptionMessage(
      "Exception while saving the AwardDetail: Invalid Contact Received: {$params['award_manager']}"
    );
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
      'award_manager' => '1',
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

    $caseTypeCategoryForAwards = $this->getCaseCategoryValueForAwards();
    $this->assertEquals($caseTypeCategoryForAwards, $actual['case_type_category']);
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
   * Get the case category value for Awards.
   *
   * @return int
   *   The case category id for Awards.
   */
  private function getCaseCategoryValueForAwards() {
    $caseCategories = CRM_Core_OptionGroup::values('case_type_categories', TRUE, FALSE, TRUE, NULL, 'name');
    return $caseCategories[CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME];
  }

}
