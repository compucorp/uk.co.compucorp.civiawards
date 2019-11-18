<?php

use CRM_CiviAwards_Setup_CreateAwardsCaseCategoryOption as CreateAwardsCaseCategoryOption;
use CRM_CiviAwards_Setup_AddAwardsCgExtendsOptionValue as AddAwardsCgExtendsOptionValue;
use CRM_CiviAwards_Setup_DeleteAwardsCaseCategoryOption as DeleteAwardsCaseCategoryOption;
use CRM_CiviAwards_Setup_DeleteAwardsCgExtendsOption as DeleteAwardsCgExtendsOption;

/**
 * Collection of upgrade steps.
 */
class CRM_CiviAwards_Upgrader extends CRM_CiviAwards_Upgrader_Base {

  /**
   * Custom extension installation logic.
   */
  public function install() {
    $steps = [
      new CreateAwardsCaseCategoryOption(),
      new AddAwardsCgExtendsOptionValue(),
    ];

    foreach ($steps as $step) {
      $step->apply();
    }
  }

  /**
   * Custom extension un-install logic.
   */
  public function uninstall() {
    $steps = [
      new DeleteAwardsCaseCategoryOption(),
      new DeleteAwardsCgExtendsOption(),
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
   * Action triggered when extension is enabled.
   *
   * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
   */
  public function enable() {
    $awardsCgExtendsOptionValue = new AddAwardsCgExtendsOptionValue();
    $awardsCgExtendsOptionValue->toggleOptionValueStatus(TRUE);
  }

  /**
   * Action triggered when extension is disabled.
   *
   * @see https://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
   */
  public function disable() {
    $awardsCgExtendsOptionValue = new AddAwardsCgExtendsOptionValue();
    $awardsCgExtendsOptionValue->toggleOptionValueStatus(FALSE);
  }

}
