<?php

use CRM_CiviAwards_Service_AwardProfile as AwardProfileService;
use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;
use CRM_CiviAwards_Helper_ApplicantReview as ApplicantReviewHelper;

/**
 * Class for testing CRM_CiviAwards_Service_AwardProfile.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_AwardProfileTest extends BaseHeadlessTest {

  /**
   * Test the creation of profile fields.
   */
  public function testCreateProfileFieldsWithValidParams() {
    $profileId = $this->createProfile();
    $customFields = $this->createCustomFields(5);

    (new AwardProfileService())->createProfileFields($customFields, $profileId);

    $uFFields = civicrm_api3('UFField', 'get', [
      'uf_group_id' => $profileId,
    ]);

    // Custom fields count + 1 for ID field.
    $this->assertEquals($uFFields['count'], count($customFields) + 1);
    foreach ($customFields as $customField) {
      $createdUFField = NULL;
      foreach ($uFFields['values'] as $uFField) {
        if ($uFField['field_name'] == 'custom_' . $customField['id']) {
          $createdUFField = $uFField;
          break;
        }
      }

      $this->assertNotNull($createdUFField);
      $this->assertEquals($customField['required'], $createdUFField['is_required']);
      $this->assertEquals($customField['weight'], $createdUFField['weight']);
    }
  }

  /**
   * Get the category type Id used for Awards cases.
   */
  private function getCaseTypeCategoryForAwards() {
    $caseCategories = CRM_Core_OptionGroup::values('case_type_categories', TRUE, FALSE, TRUE, NULL, 'name');

    return $caseCategories[CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME];
  }

  /**
   * Create a given number of custom fields.
   *
   * @param int $fieldsNumber
   *   Number of fields to be created.
   *
   * @return array
   *   The Custom Fields information.
   */
  private function createCustomFields(int $fieldsNumber) {
    $customFields = [];
    for ($i = 0; $i < $fieldsNumber; $i++) {
      $suffix = rand();
      $customField = civicrm_api3('CustomField', 'create', [
        'custom_group_id' => ApplicantReviewHelper::getApplicantReviewCustomGroupId(),
        'name' => 'test_custom_' . $suffix,
        'label' => 'test_custom_' . $suffix,
        'data_type' => 'Boolean',
        'default_value' => 1,
        'html_type' => 'Radio',
        'required' => rand(0, 1),
        'weight' => rand(),
      ]);
      $customField = array_shift($customField['values']);

      $customFields[] = [
        'id' => $customField['id'],
        'required' => $customField['is_required'],
        'weight' => $customField['weight'],
      ];
    }

    return $customFields;
  }

  /**
   * Get the ID of the group used for Review Fields.
   *
   * @return int
   *   Id of the profile created or retrieved.
   */
  private function createProfile() {
    $caseTypeId = $this->getCaseTypeCategoryForAwards();

    $result = civicrm_api3('UFGroup', 'get', [
      'title' => 'Award Profile_' . $caseTypeId,
      'name' => 'Award Profile_' . $caseTypeId,
      'is_reserved' => 1,
    ]);
    if ($result['count'] > 0) {
      return array_shift($result['values'])['id'];
    }

    return (new AwardProfileService())->createProfile($caseTypeId);
  }

}
