<?php

use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;
use CRM_CiviAwards_Setup_CreateAwardPaymentActivityTypes as CreateAwardPaymentActivityTypes;
use CRM_CiviAwards_Hook_AclGroup_AllowAccessToPaymentGroups as AllowAccessToPaymentGroups;

/**
 * Test class for the AllowAccessToPaymentGroups ACL Hook.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_AclGroup_AllowAccessToPaymentGroupsTest extends BaseHeadlessTest {

  use CRM_CiviAwards_Helper_SessionTrait;

  /**
   * Test Payment custom group is set.
   */
  public function testPaymentCustomGroupIsSetAsPartOfAccessibleCustomGroups() {
    $this->setPermissions([AwardPermission::PAYMENT_FIELD_SET_PERM]);
    $paymentGroupHook = new AllowAccessToPaymentGroups();
    $allGroups = [];
    $currentGroups = [];
    $paymentGroupHook->run('', '', CRM_Core_BAO_CustomGroup::getTableName(), $allGroups, $currentGroups);
    $this->assertCount(1, $currentGroups);
    $this->assertContains($this->getPaymentInformationCustomGroupId(), $currentGroups);
  }

  /**
   * Test Payment custom group not set.
   */
  public function testPaymentCustomGroupIsNotSetWhenUserDoesNotHavePermission() {
    $this->setPermissions();
    $paymentGroupHook = new AllowAccessToPaymentGroups();
    $allGroups = [];
    $currentGroups = [];
    $paymentGroupHook->run('', '', CRM_Core_BAO_CustomGroup::getTableName(), $allGroups, $currentGroups);
    $this->assertEmpty($currentGroups);
  }

  /**
   * Get the applicant custom group Id.
   */
  private function getPaymentInformationCustomGroupId() {
    $result = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => CreateAwardPaymentActivityTypes::CUSTOM_GROUP_NAME,
    ]);

    return $result['id'];
  }

}
