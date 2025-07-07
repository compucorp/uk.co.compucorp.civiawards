<?php

namespace Civi\Api4\Action\ApplicantReviewField;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\Generic\BasicGetAction;
use Civi\Api4\Generic\Result;

/**
 * Get Application Review Fields.
 */
class GetAction extends BasicGetAction {

  /**
   * Performs the getter action.
   *
   * @param \Civi\Api4\Generic\Result $result
   *   Result object.
   *
   * @return \Civi\Api4\Generic\Result
   *   Result object
   */
  public function _run(Result $result) { // phpcs:ignore
    foreach ($this->getFields() as $field) {
      $result[] = $field;
    }

    return $result;
  }

  /**
   * Gets Application review fields.
   *
   * @return array
   *   Application review fields
   */
  private function getFields() {
    if ($this->checkPermissions && \CRM_Core_Permission::check(\CRM_CiviAwards_Hook_AlterAPIPermissions_Award::REVIEW_FIELD_SET_PERM)) {
      // User is permitted to take action.
      $this->checkPermissions = FALSE;
    }

    $customGroups = CustomGroup::get($this->checkPermissions)
      ->addSelect('name')
      ->addJoin('OptionValue AS option_value', 'INNER',
        ['extends_entity_column_value', '=', 'option_value.value']
      )
      ->addWhere('extends', '=', 'Activity')
      ->addWhere('option_value.option_group_id:name', '=', 'activity_type')
      ->addWhere('option_value.name', '=', 'Applicant Review')
      ->execute();

    if ($customGroups->count() === 0) {
      return [];
    }

    $params = $this->getParams();

    // Extract names using array_column.
    $applicantReviewCustomGroups = array_column($customGroups->getArrayCopy(), 'name');

    $query = CustomField::get($this->checkPermissions)
      ->addWhere('custom_group_id:name', 'IN', $applicantReviewCustomGroups)
      ->setLimit(0)
      ->addChain('custom_group', CustomGroup::get($this->checkPermissions)
        ->addWhere('id', '=', '$custom_group_id')
        ->addSelect('title', 'name')
    );

    foreach ($params["select"] ?? [] as $select) {
      $query->addSelect($select);
    }

    return $query->execute();
  }

}
