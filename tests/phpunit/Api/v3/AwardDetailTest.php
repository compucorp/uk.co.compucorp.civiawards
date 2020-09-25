<?php

use CRM_CiviAwards_Test_Fabricator_CaseCategory as CaseCategoryFabricator;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;
use CRM_Case_BAO_CaseType as CaseType;

/**
 * Class api_v3_AwardDetailTest.
 *
 * @group headless
 */
class Api_v3_AwardDetailTest extends BaseHeadlessTest {

  /**
   * Test AwardDetail.getInstanceCategories API.
   */
  public function testGetInstanceCategoriesReturnsCorrectResults() {
    $caseTypeCategory1 = CaseCategoryFabricator::fabricate(['name' => 'category1']);
    $caseTypeCategory2 = CaseCategoryFabricator::fabricate(['name' => 'category2']);
    CaseCategoryFabricator::fabricate(['name' => 'category3']);

    $this->addCategoryToApplicantManagement($caseTypeCategory1['name']);
    $this->addCategoryToApplicantManagement($caseTypeCategory2['name']);

    $result = civicrm_api3('AwardDetail', 'getinstancecategories');

    // There are three case categories belonging to the Award Instance
    // The Awards category added on setup, CaseTypeCategory1 and
    // CaseTypeCategory2 all belongs to the Applicant Management
    // Instance.
    $caseTypeCategories = array_flip(CaseType::buildOptions('case_type_category', 'validate'));
    $expectedValue = [
      $caseTypeCategories[CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME] => CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME,
      $caseTypeCategory1['value'] => $caseTypeCategory1['name'],
      $caseTypeCategory2['value'] => $caseTypeCategory2['name'],
    ];

    $this->assertEquals($expectedValue, $result['values']);
  }

  /**
   * Adds the case category to applicant management Instance.
   *
   * @param string $caseCategoryName
   *   Case category name.
   */
  private function addCategoryToApplicantManagement($caseCategoryName) {
    $params = [
      'category_id' => $caseCategoryName,
      'instance_id' => CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME,
    ];
    civicrm_api3('CaseCategoryInstance', 'create', $params);
  }

}
