<?php

use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;

/**
 * Test Bao class for Award review panel.
 *
 * @group headless
 */
class CRM_CiviAwards_BAO_AwardReviewPanelTest extends BaseHeadlessTest {

  /**
   * Test create function fails if exclude group has non integer values.
   */
  public function testCreateThrowsExceptionWhenExcludeGroupContactSettingContainsNonInteger() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1, 3, 'Sample'],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings: One of the values of exclude_groups is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if exclude group is not an array.
   */
  public function testCreateThrowsExceptionWhenExcludeGroupContactSettingIsNotAnArray() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => 'Sample',
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings: exclude_groups should be an array'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if include group has non integer values.
   */
  public function testCreateThrowsExceptionWhenIncludeGroupContactSettingContainsNonInteger() {
    $params = [
      'contact_settings' => [
        'include_groups' => [1, 3, 'Sample'],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings: One of the values of include_groups is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if include group is not an array.
   */
  public function testCreateThrowsExceptionWhenIncludeGroupContactSettingIsNotAnArray() {
    $params = [
      'contact_settings' => [
        'include_groups' => 'Sample',
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Contact Settings: include_groups should be an array'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if relationship contact has non integer values.
   */
  public function testCreateThrowsExceptionWhenRelationshipContactArrayContainsNonInteger() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'relationship' => [
          [
            'contact_id' => [1, 'Contact'],
            'relationship_type_id' => 1,
          ],
        ],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'relationship: One of the values of contact_id is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if relationship contact is not an array.
   */
  public function testCreateThrowsExceptionWhenRelationshipContactIsNotAnArray() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'relationship' => [
          [
            'contact_id' => 2,
            'relationship_type_id' => 1,
          ],
        ],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'relationship: contact_id should be an array'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if relationship type id is not an integer.
   */
  public function testCreateThrowsExceptionWhenRelationshipTypeIdIsNotAnInteger() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'relationship' => [
          [
            'contact_id' => [1, 3],
            'relationship_type_id' => 'Sample',
          ],
        ],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'relationship: relationship_type_id is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if relationship is not formatted correctly.
   */
  public function testCreateThrowsExceptionWhenRelationshipIsNotInExpectedFormat() {
    $params = [
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'relationship' => [
          [
            'contact_id' => [1, 3],
            'relationship_type_id' => 1,
            'is_a_to_b' => 'Sample',
          ],
        ],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'relationship: is_a_to_b is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if application tag has non integer values.
   */
  public function testCreateThrowsExceptionWhenApplicationTagVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_tags' => [1, 3, 'Sample'],
        'anonymize_application' => 0,
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: One of the values of application_tags is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if application status has non integer values.
   */
  public function testCreateThrowsExceptionWhenApplicationStatusVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3, 'Sample'],
        'anonymize_application' => 0,
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: One of the values of application_status is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if anonymize application param is missing.
   */
  public function testCreateThrowsExceptionWhenAnonymizeApplicationParamForVisibilitySettingIsAbsent() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: anonymize_application is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if restricted status is not set.
   */
  public function testCreateThrowsExceptionWhenRestrictedStatusIsNotSetAndApplicationStatusIsRestricted() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: restricted_application_status is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if restricted status is empty.
   */
  public function testCreateThrowsExceptionWhenRestrictedStatusIsEmptyAndApplicationStatusIsRestricted() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
        'restricted_application_status' => [],
      ],
    ];

    $this->expectException(Exception::class);
    $this->expectExceptionCode(0);
    $this->expectExceptionMessage(
      'Visibility Settings: restricted_application_status is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if restricted application status is not present.
   */
  public function testCreateThrowsExceptionWhenIsApplicationStatusRestrictedNotPresent() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
        'anonymize_application' => 0,
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: is_application_status_restricted is required'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function fails if restricted status has non integer values.
   */
  public function testCreateThrowsExceptionWhenRestrictedStatusVisibilitySettingContainsNonInteger() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
        'restricted_application_status' => [1, 3, 'Sample'],
      ],
    ];

    $this->setExpectedException(
      'Exception',
      'Visibility Settings: One of the values of restricted_application_status is not in a valid format'
    );

    AwardReviewPanelFabricator::fabricate($params);
  }

  /**
   * Test create function is successful with all correct params.
   */
  public function testCreateIsSuccessFulWhenAllParametersAreInExpectedFormat() {
    $params = [
      'visibility_settings' => [
        'application_status' => [1, 3],
        'anonymize_application' => 0,
        'is_application_status_restricted' => 1,
        'restricted_application_status' => [1, 3],
      ],
      'contact_settings' => [
        'exclude_groups' => [1, 3],
        'include_groups' => [5],
        'relationship' => [
          [
            'contact_id' => [1, 3],
            'relationship_type_id' => 1,
            'is_a_to_b' => 1,
          ],
          [
            'contact_id' => [1, 3],
            'relationship_type_id' => 4,
            'is_a_to_b' => 0,
          ],
        ],
      ],
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $this->assertNotEmpty($awardPanel->id);
  }

}
