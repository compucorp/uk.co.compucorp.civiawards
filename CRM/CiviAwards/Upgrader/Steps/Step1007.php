<?php

use CRM_CiviAwards_Setup_CreateAwardSubtypeOptionGroup as CreateAwardSubtypeOptionGroup;

/**
 * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1007 {

  /**
   * Renames the 'Award Type' field to 'Award Subtype'`.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $this->renameDbColumn();
    $this->renameOptionGroup();
    $this->syncLogTables();

    return TRUE;
  }

  /**
   * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
   */
  private function renameOptionGroup() {
    $awardTypeOptionGroup = civicrm_api3('OptionGroup', 'get', [
      'sequential' => 1,
      'name' => "civiawards_award_type",
    ])['values'];

    if (count($awardTypeOptionGroup) == 0) {
      return;
    }

    civicrm_api3('OptionGroup', 'create', [
      'id' => $awardTypeOptionGroup[0]['id'],
      'name' => CreateAwardSubtypeOptionGroup::AWARD_OPTION_GROUP_NAME,
      'title' => ts(CreateAwardSubtypeOptionGroup::AWARD_OPTION_GROUP_TITLE),
      'is_reserved' => 1,
    ]);
  }

  /**
   * Renames `award_type` column to `award_subtype`.
   */
  private function renameDbColumn() {
    $renameColumnSql = "ALTER TABLE
      civicrm_civiawards_award_detail
      CHANGE award_type award_subtype varchar(30) NOT NULL COMMENT 'One of the values of the award_subtype option group'";

    CRM_Core_DAO::executeQuery($renameColumnSql);
  }

  /**
   * Sync log tables.
   */
  private function syncLogTables() {
    $logging = new CRM_Logging_Schema();
    $logging->fixSchemaDifferencesFor(CRM_CiviAwards_BAO_AwardDetail::getTableName());
  }

}
