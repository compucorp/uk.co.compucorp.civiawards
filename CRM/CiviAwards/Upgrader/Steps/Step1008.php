<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Adds new db column to Award Details.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1008 {

  /**
   * Adds the is_template column.
   *
   * @return bool
   *   returns value.
   */
  public function apply() {
    $this->addIsTemplateColumn();
    $this->syncLogTables();

    return TRUE;
  }

  /**
   * Add Is Template Column to the Award Details table.
   */
  private function addIsTemplateColumn() {
    $awardDetailTable = AwardDetail::getTableName();
    $isTemplateColumn = 'is_template';

    if (!SchemaHandler::checkIfFieldExists($awardDetailTable, $isTemplateColumn)) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$awardDetailTable}
        ADD COLUMN {$isTemplateColumn} tinyint DEFAULT 0 COMMENT 'Whether the award detail is for a template or not'");
    }
  }

  /**
   * Sync log tables.
   */
  private function syncLogTables() {
    $logging = new CRM_Logging_Schema();
    $logging->fixSchemaDifferencesFor(AwardDetail::getTableName());
  }

}
