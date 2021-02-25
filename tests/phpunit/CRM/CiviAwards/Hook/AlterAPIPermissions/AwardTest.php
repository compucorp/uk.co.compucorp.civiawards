<?php

use CRM_CiviAwards_Setup_CreateApplicantReviewActivityType as CreateApplicantReviewActivityType;
use CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes as CreateAwardPaymentActivityTypes;
use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;

/**
 * Test class for the Award Alter Permissions Hook.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_AlterAPIPermissions_AwardTest extends BaseHeadlessTest {

  use CRM_CiviAwards_Helper_SessionTrait;

  /**
   * Test Exception is thrown when user does not have access review perm.
   */
  public function testExceptionIsThrownWhenAccessingCustomReviewSetFieldsWithoutAccessReviewSetPermission() {
    $this->setPermissions([]);
    $this->expectException(CiviCRM_API3_Exception::class);
    $this->expectExceptionMessage(
      'API permission check failed for CustomField/get call; insufficient permission: require ( ' . AwardPermission::REVIEW_FIELD_SET_PERM . ' or access all custom data )'
    );

    civicrm_api3('CustomField', 'get', [
      'custom_group_id' => CreateApplicantReviewActivityType::CUSTOM_GROUP_NAME,
      'check_permissions' => TRUE,
    ]);
  }

  /**
   * Test Exception is thrown when user does not have access payment set perm.
   */
  public function testExceptionIsThrownWhenAccessingPaymentReviewSetFieldsWithoutAccessPaymentSetPermission() {
    $this->setPermissions([]);
    $this->expectException(CiviCRM_API3_Exception::class);
    $this->expectExceptionMessage(
      'API permission check failed for CustomField/get call; insufficient permission: require ( ' . AwardPermission::PAYMENT_FIELD_SET_PERM . ' or access all custom data )'
    );

    civicrm_api3('CustomField', 'get', [
      'custom_group_id' => CreateAwardPaymentActivityTypes::CUSTOM_GROUP_NAME,
      'check_permissions' => TRUE,
    ]);
  }

  /**
   * Test no error is thrown when user has access review perm.
   */
  public function testReviewFieldSetCanBeAccessedWithAccessReviewSetPermission() {
    $this->setPermissions([AwardPermission::REVIEW_FIELD_SET_PERM]);

    $result = civicrm_api3('CustomField', 'get', [
      'custom_group_id' => CreateApplicantReviewActivityType::CUSTOM_GROUP_NAME,
      'check_permissions' => TRUE,
    ]);
    $this->assertEquals(0, $result['is_error']);
  }

  /**
   * Test no error is thrown when user has access payment set perm.
   */
  public function testPaymentFieldSetCanBeAccessedWithAccessPaymentSetPermission() {
    $this->setPermissions([AwardPermission::PAYMENT_FIELD_SET_PERM]);

    $result = civicrm_api3('CustomField', 'get', [
      'custom_group_id' => CreateAwardPaymentActivityTypes::CUSTOM_GROUP_NAME,
      'check_permissions' => TRUE,
    ]);

    $this->assertEquals(0, $result['is_error']);
  }

}
