<?php

use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;
use CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroups as AllowAccessToApplicantReviewGroups;
use CRM_CiviAwards_Setup_CreateApplicantReviewActivityType as CreateApplicantReviewActivityType;

/**
 * Test class for the AllowAccessToApplicantReviewGroups ACL Hook.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_AclGroup_AllowAccessToApplicantReviewGroupsTest extends BaseHeadlessTest {

  use CRM_CiviAwards_Helper_SessionTrait;

  /**
   * Test Applicant review custom group is set.
   */
  public function testApplicantReviewCustomGroupIsSetAsPartOfAccessibleCustomGroups() {
    $this->setPermissions([AwardPermission::REVIEW_FIELD_SET_PERM]);
    $applicantReviewHook = new AllowAccessToApplicantReviewGroups();
    $allGroups = [];
    $currentGroups = [];
    $applicantReviewHook->run('', '', CRM_Core_BAO_CustomGroup::getTableName(), $allGroups, $currentGroups);
    $this->assertCount(1, $currentGroups);
    $this->assertContains($this->getApplicantReviewCustomGroupId(), $currentGroups);
  }

  /**
   * Test Applicant review custom group not set.
   */
  public function testApplicantReviewCustomGroupIsNotSetWhenUserDoesNotHavePermission() {
    $this->setPermissions();
    $applicantReviewHook = new AllowAccessToApplicantReviewGroups();
    $allGroups = [];
    $currentGroups = [];
    $applicantReviewHook->run('', '', CRM_Core_BAO_CustomGroup::getTableName(), $allGroups, $currentGroups);
    $this->assertEmpty($currentGroups);
  }

  /**
   * Get the applicant custom group Id.
   */
  private function getApplicantReviewCustomGroupId() {
    $result = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => CreateApplicantReviewActivityType::CUSTOM_GROUP_NAME,
    ]);

    return $result['id'];
  }

}
