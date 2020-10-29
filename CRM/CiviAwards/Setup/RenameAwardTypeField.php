<?php

/**
 * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
 */
class CRM_CiviAwards_Setup_RenameAwardTypeField {

  /**
   * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
   */
  public function apply() {
    $this->renameDbColumn();
    $this->renameOptionGroup();
  }

  /**
   * Renames `civiawards_award_type` option group to `civiawards_award_subtype`.
   */
  private function renameOptionGroup() {
    $awardTypeOptionGroup = civicrm_api3('OptionGroup', 'get', [
      'sequential' => 1,
      'name' => "civiawards_award_type",
    ])['values'];

    if (count($awardTypeOptionGroup) > 0) {
      civicrm_api3('OptionGroup', 'create', [
        'id' => $awardTypeOptionGroup[0]['id'],
        'name' => "civiawards_award_subtype",
        'title' => "Award Sub Type",
      ]);
    }
  }

  /**
   * Renames `award_type` column to `award_subtype`, .
   */
  private function renameDbColumn() {
    $renameDBColumnSql = 'ALTER TABLE civicrm_civiawards_award_detail RENAME COLUMN award_type TO award_subtype;';

    CRM_Core_DAO::executeQuery($renameDBColumnSql);
  }

}
