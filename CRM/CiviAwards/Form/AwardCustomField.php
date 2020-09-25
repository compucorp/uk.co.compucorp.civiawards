<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategoryHelper;

/**
 * This form class defines the custom fields form for an Award.
 */
class CRM_CiviAwards_Form_AwardCustomField extends CRM_Civicase_Form_CaseCategoryTypeCustomField {

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    $awardId = CRM_Utils_Request::retrieve('entityId', 'Positive', $this, TRUE);
    if (!$this->isValidApplicantManagementType($awardId)) {
      $this->assign('invalidAwardType', TRUE);
    }

    parent::preProcess();
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
    return CaseTypeCategoryHelper::APPLICATION_MANAGEMENT_NAME . "Type";
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
  private function isValidApplicantManagementType($awardTypeId) {
    $applicantManagementCaseTypes = CRM_CiviAwards_Helper_CaseTypeCategory::getApplicantManagementCaseTypes();

    return !empty($applicantManagementCaseTypes[$awardTypeId]);
  }

}
