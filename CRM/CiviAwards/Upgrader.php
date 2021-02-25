<?php

use CRM_CiviAwards_Setup_CreateAwardsCaseCategoryOption as CreateAwardsCaseCategoryOption;
use CRM_CiviAwards_Setup_CreateApplicantManagementOption as CreateApplicantManagementOption;
use CRM_CiviAwards_Setup_ProcessAwardsCategoryForCustomGroupSupport as ProcessAwardsCategoryForCustomGroupSupport;
use CRM_CiviAwards_Setup_AddApplicantManagementCaseTypeCustomGroupSupport as AddApplicantManagementCaseTypeCustomGroupSupport;
use CRM_CiviAwards_Setup_DeleteAwardsCaseCategoryOption as DeleteAwardsCaseCategoryOption;
use CRM_CiviAwards_Setup_CreateAwardSubtypeOptionGroup as CreateAwardSubtypeOptionGroup;
use CRM_CiviAwards_Setup_CreateApplicantReviewActivityType as CreateApplicantReviewActivityType;
use CRM_CiviAwards_Setup_DeleteApplicantReviewCustomField as DeleteApplicantReviewCustomField;
use CRM_CiviAwards_Setup_AddApplicationManagementWordReplacement as AddApplicationManagementWordReplacement;
use CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForAwardsCategory as RemoveCustomGroupSupportForAwardsCategory;
use CRM_CiviAwards_Uninstall_RemoveCustomGroupSupportForApplicantManagement as RemoveCustomGroupSupportForApplicantManagement;
use CRM_CiviAwards_Setup_CreateAwardsMenus as CreateAwardsMenus;

/**
 * Collection of upgrade steps.
 */
class CRM_CiviAwards_Upgrader extends CRM_CiviAwards_Upgrader_Base {

  /**
   * A list of directories to be scanned for XML installation files.
   *
   * @var array
   */
  private $xmlDirectories = ['custom_fields'];

  /**
   * Custom extension installation logic.
   */
  public function install() {
    $steps = [
      new CreateAwardsCaseCategoryOption(),
      new ProcessAwardsCategoryForCustomGroupSupport(),
      new AddApplicantManagementCaseTypeCustomGroupSupport(),
      new CreateAwardSubtypeOptionGroup(),
      new CreateApplicantManagementOption(),
      new CreateApplicantReviewActivityType(),
      new AddApplicationManagementWordReplacement(),
      new CreateAwardsMenus(),
    ];

    foreach ($steps as $step) {
      $step->apply();
    }

    $this->processXmlInstallationFiles();
  }

  /**
   * Scans all the directories in $xmlDirectories for installation files.
   *
   * (xml files ending with _install.xml) and processes them.
   */
  private function processXmlInstallationFiles() {
    foreach ($this->xmlDirectories as $directory) {
      $files = glob($this->extensionDir . "/xml/{$directory}/*_install.xml");
      if (is_array($files)) {
        foreach ($files as $file) {
          $this->executeCustomDataFileByAbsPath($file);
        }
      }
    }
  }

  /**
   * Custom extension un-install logic.
   */
  public function uninstall() {
    $steps = [
      new DeleteAwardsCaseCategoryOption(),
      new RemoveCustomGroupSupportForAwardsCategory(),
      new DeleteApplicantReviewCustomField(),
      new RemoveCustomGroupSupportForApplicantManagement(),
    ];

    foreach ($steps as $step) {
      $step->apply();
    }
  }

  /**
   * Checks for pending revisions for extension.
   *
   * @inheritdoc
   */
  public function hasPendingRevisions() {
    $revisions = $this->getRevisions();
    $currentRevisionNum = $this->getCurrentRevision();
    if (empty($revisions)) {
      return FALSE;
    }
    if (empty($currentRevisionNum)) {
      return TRUE;
    }

    return ($currentRevisionNum < max($revisions));
  }

  /**
   * Enqueue pending revisions.
   *
   * @inheritdoc
   */
  public function enqueuePendingRevisions(CRM_Queue_Queue $queue) {
    $currentRevisionNum = (int) $this->getCurrentRevision();
    foreach ($this->getRevisions() as $revisionClass => $revisionNum) {

      if ($revisionNum <= $currentRevisionNum) {
        continue;
      }
      $tsParams = [1 => $this->extensionName, 2 => $revisionNum];
      $title = ts('Upgrade %1 to revision %2', $tsParams);
      $upgradeTask = new CRM_Queue_Task(
        [get_class($this), 'runStepUpgrade'],
        [(new $revisionClass())],
        $title
      );
      $queue->createItem($upgradeTask);
      $setRevisionTask = new CRM_Queue_Task(
        [get_class($this), '_queueAdapter'],
        ['setCurrentRevision', $revisionNum],
        $title
      );
      $queue->createItem($setRevisionTask);
    }
  }

  /**
   * This is a callback for running step upgraders from the queue.
   *
   * @param CRM_Queue_TaskContext $context
   *   The Queue Task context.
   * @param object $step
   *   The upgrader step.
   *
   * @return true
   *   The queue requires that true is returned on successful upgrade, but we
   *   use exceptions to indicate an error instead.
   */
  public function runStepUpgrade(CRM_Queue_TaskContext $context, $step) {
    $step->apply();

    return TRUE;
  }

  /**
   * Get a list of revisions.
   *
   * @return array
   *   An array of revisions sorted by the upgrader class as keys
   */
  public function getRevisions() {
    $extensionRoot = __DIR__;
    $stepClassFiles = glob($extensionRoot . '/Upgrader/Steps/Step*.php');
    $sortedKeyedClasses = [];
    foreach ($stepClassFiles as $file) {
      $class = $this->getUpgraderClassnameFromFile($file);
      $numberPrefix = 'Steps_Step';
      $startPos = strpos($class, $numberPrefix) + strlen($numberPrefix);
      $revisionNum = (int) substr($class, $startPos);
      $sortedKeyedClasses[$class] = $revisionNum;
    }
    asort($sortedKeyedClasses, SORT_NUMERIC);

    return $sortedKeyedClasses;
  }

  /**
   * Gets the PEAR style classname from an upgrader file.
   *
   * @param string $file
   *   The file name.
   *
   * @return string
   *   Class name.
   */
  private function getUpgraderClassnameFromFile($file) {
    $file = str_replace(realpath(__DIR__ . '/../../'), '', $file);
    $file = str_replace('.php', '', $file);
    $file = str_replace('/', '_', $file);

    return ltrim($file, '_');
  }

  /**
   * On upgrade.
   *
   * @param string $op
   *   Operation name.
   * @param CRM_Queue_Queue|null $queue
   *   Queue object.
   *
   * @return bool[]|void
   *   Operation result.
   */
  public function onUpgrade($op, CRM_Queue_Queue $queue = NULL) {
    switch ($op) {
      case 'check':
        return [$this->hasPendingRevisions()];

      case 'enqueue':
        $result = $this->enqueuePendingRevisions($queue);
        $syncLogTableTask = new CRM_Queue_Task(
          [get_class($this), 'syncLogTables'],
          [],
          'Sync Log Tables'
        );
        $queue->createItem($syncLogTableTask);
        return $result;

      default:
    }
  }

  /**
   * Sync log tables.
   *
   * @param CRM_Queue_TaskContext $context
   *   Queue task context.
   */
  public function syncLogTables(CRM_Queue_TaskContext $context) {
    $logging = new CRM_Logging_Schema();
    $logging->fixSchemaDifferences();

    return TRUE;
  }

}
