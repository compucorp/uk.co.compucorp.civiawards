<?php

use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;

/**
 * @group headless
 */
class CRM_CiviAwards_BAO_AwardReviewPanelTest extends BaseHeadlessTest {

  public function testCreateThrowsExceptionWhenExcludeGroupContactSettingContainsNonInteger() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1,3,'Sample'],
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings :  One of the values of exclude_groups is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenExcludeGroupContactSettingIsNotAnArray() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => 'Sample',
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings :  exclude_groups should be an array'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenIncludeGroupContactSettingContainsNonInteger() {
    $params = [
      'contact_settings' => [
        'include_groups' => [1,3,'Sample'],
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings :  One of the values of include_groups is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenIncludeGroupContactSettingIsNotAnArray() {
    $params = [
      'contact_settings' => [
        'include_groups' => 'Sample',
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings :  include_groups should be an array'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenApplicationTagVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_tags' => [1,3,'Sample'],
        'anonymize_application' => 0
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings :  One of the values of application_tags is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenApplicationStatusVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3,'Sample'],
        'anonymize_application' => 0
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings :  One of the values of application_status is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenAnonymizeApplicationParamForVisibilitySettingIsAbsent() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3],
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings :  anonymize_application is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenRestrictedStatusIsEmptyAndApplicationStatusIsRestricted() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: restricted_application_status is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenIsApplicationStatusRestrictedNotPresent() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3],
        'anonymize_application' => 0,
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: is_application_status_restricted is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateThrowsExceptionWhenRestrictedStatusVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
        'restricted_application_status' => [1,3,'Sample'],
      ]
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: One of the values of restricted_application_status is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  public function testCreateIsSuccessFulWhenAllPArametersAreInExpectedFormat() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1,3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
        'restricted_application_status' => [1,3],
      ],
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'include_groups' => [5],
        'relationship' => [
          'contact_id' => [1, 3],
          'relationship_type_id' => 1,
          'is_a_to_b' => 1
        ]
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $this->assertNotEmpty($awardPanel->id);
  }
}
