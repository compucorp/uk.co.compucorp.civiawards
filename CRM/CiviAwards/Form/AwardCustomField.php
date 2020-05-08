<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * Class CRM_CiviAwards_Form_AwardCustomField.
 */
class CRM_CiviAwards_Form_AwardCustomField extends CRM_Civicase_Form_CaseCategoryTypeCustomField {

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    $awardId = CRM_Utils_Request::retrieve('entityId', 'Positive', $this, TRUE);
    if (!$this->isValidAwardType($awardId)) {
      CRM_Core_Session::setStatus(ts('An error occurred'), 'Error', 'error');
    }

    $this->assign('invalidAwardType', TRUE);
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $this->addButtons(
      [
        [
          'type' => 'upload',
          'name' => ts('Save'),
          'class' => 'award-custom-field',
          'isDefault' => TRUE,
        ],
      ]
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityType() {
    return CaseTypeCategoryHelper::AWARDS_CASE_TYPE_CATEGORY_NAME . "Type";
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    parent::postProcess();

    $url = CRM_Utils_System::url('civicrm/award/customfield', [
      'entityId' => $this->entityId,
      'reset' => 1,
    ]);

    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($url);
  }

  /**
   * Checks if the award case type is valid.
   *
   * @param int $awardTypeId
   *   Award Type Id.
   *
   * @return bool
   *   Checks if award is valid or not.
   */
  private function isValidAwardType($awardTypeId) {
    $awardCaseTypes = CRM_CiviAwards_Helper_CaseTypeCategory::getAwardCaseTypes();

    return !empty($awardCaseTypes[$awardTypeId]);
  }

}
