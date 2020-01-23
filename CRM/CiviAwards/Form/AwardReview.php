<?php

use CRM_CiviAwards_ExtensionUtil as E;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;

/**
 * Form controller class.
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_CiviAwards_Form_AwardReview extends CRM_Core_Form {

  /**
   * Case ID.
   *
   * @var int
   */
  private $caseId;

  /**
   * Activity Id.
   *
   * @var int
   */
  private $activityId;

  /**
   * Profile Id.
   *
   * @var int
   */
  private $profileId;

  /**
   * Case contact full name.
   *
   * @var string
   */
  private $caseContactDisplayName;

  /**
   * Case Type Name.
   *
   * @var string
   */
  private $caseTypeName;

  /**
   * Case Tags.
   *
   * @var string
   */
  private $caseTags;

  /**
   * List of profile fields.
   *
   * @var array
   */
  private $profileFields;

  /**
   * {@inheritDoc}
   */
  public function setDefaultValues() {
    if (empty($this->defaultValues) && $this->activityId) {
      $this->defaultValues = $this->getProfileFieldValues();

      return $this->defaultValues;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    if ($this->_action & CRM_Core_Action::ADD) {
      $this->caseId = CRM_Utils_Request::retrieve('case_id', 'Positive', $this);
    }
    else {
      $this->activityId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      $this->caseId = $this->getActivityCaseId();
    }

    $this->profileId = $this->getProfileIdFromCaseType();
    $this->caseContactDisplayName = $this->getCaseContactDisplayName();
    $this->caseTypeName = $this->getCaseTypeName();
    $this->caseTags = $this->getCaseTags();
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $this->assign('caseContactDisplayName', $this->getCaseContactDisplayName());
    $this->assign('caseTypeName', $this->caseTypeName);
    $this->assign('caseTags', $this->caseTags);
    if ($this->_action & CRM_Core_Action::VIEW) {
      $editUrlParams = "action=update&id={$this->activityId}&reset=1";
      $this->assign('editUrlParams', $editUrlParams);
    }


    $fields = CRM_Core_BAO_UFGroup::getFields(
      $this->profileId, FALSE, CRM_Core_Action::ADD, NULL,
      NULL, FALSE, NULL, FALSE, NULL, CRM_Core_Permission::CREATE, 'weight'
    );
    $customFieldLabels = $this->getCustomFieldLabels(array_keys($fields));
    $elementNames = [];

    foreach ($fields as $name => $field) {
      $elementNames[] = $field['name'];
      $field['title'] = $customFieldLabels[$field['name']];
      CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
    }

    if ($this->_action & CRM_Core_Action::VIEW) {
      foreach ($this->_elements as $element) {
        if (in_array($element->_attributes['name'], $elementNames)) {
          $element->freeze();
        }
      }
    }

    $this->profileFields = $elementNames;
    $this->assign('elementNames', $elementNames);

    if ($this->_action & CRM_Core_Action::VIEW) {
      $pageTitle = 'View Review - ' . $this->getPageTitle();
      $this->addButtons([
        [
          'type' => 'cancel',
          'name' => E::ts('Cancel'),
        ],
      ]);
    }
    else {
      $pageTitle = $this->_action == CRM_Core_Action::ADD ? 'Add Review' : 'Update Review - ' . $this->getPageTitle();
      $this->addButtons([
        [
          'type' => 'submit',
          'name' => E::ts('Save'),
          'isDefault' => TRUE,
        ],
        [
          'type' => 'cancel',
          'name' => E::ts('Cancel'),
        ],
      ]);
    }

    CRM_Utils_System::setTitle(E::ts($pageTitle));
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    $values = $this->exportValues();
    foreach ($this->profileFields as $profileField) {
      $profileFields[$profileField] = $values[$profileField];
    }

    if ($this->_action & CRM_Core_Action::ADD) {
      $activityId = $this->createActivity();
      $activityContact = CRM_Core_Session::getLoggedInContactID();
    }
    else {
      $activityId = $this->activityId;
      $activityContact = $this->getActivityTargetContact();
    }

    $profileFields['activity_id'] = $activityId;
    $profileFields['contact_id'] = $activityContact;
    $profileFields['profile_id'] = $this->profileId;

    try {
      civicrm_api3('Profile', 'submit', $profileFields);
      $status = $this->_action == CRM_Core_Action::ADD ? 'Added' : 'Updated';
      CRM_Core_Session::setStatus(ts("The review has been {$status} successfully"), 'Success', 'success');
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus(ts('An error occurred'), 'Error', 'error');
    }

    $url = CRM_Utils_System::url('civicrm/awardreview', [
      'action' => 'update',
      'id' => $activityId,
      'reset' => 1,
    ]);
    CRM_Utils_System::redirect($url);
  }

  /**
   * Returns the profile id linked to the Case Type.
   *
   * @return int
   *   Profile ID.
   */
  private function getProfileIdFromCaseType() {
    $caseTypeId = $this->getCaseTypeId();
    $awardDetail = new AwardDetail();
    $awardDetail->case_type_id = $caseTypeId;
    $awardDetail->find(TRUE);

    return $awardDetail->profile_id;
  }

  /**
   * Returns the case id linked to the activity.
   *
   * @return int|null
   *   Case ID.
   */
  private function getActivityCaseId() {
    $result = civicrm_api3('Activity', 'getsingle', [
      'id' => $this->activityId,
      'return' => ['case_id'],
    ]);

    if (empty($result['case_id'])) {
      return;
    }

    return $result['case_id'][0];
  }

  /**
   * Returns the case type Id from the case Id.
   *
   * @return int|null
   *   Case Type Id.
   */
  private function getCaseTypeId() {
    $result = civicrm_api3('Case', 'getsingle', [
      'id' => $this->caseId,
      'return' => ['case_type_id'],
    ]);

    if (empty($result['case_type_id'])) {
      return;
    }

    return $result['case_type_id'];
  }

  /**
   * Returns the Activity Target Contact.
   *
   * @return string
   *   Activity traget contact.
   */
  private function getActivityTargetContact() {
    $result = civicrm_api3('Activity', 'get', [
      'sequential' => 1,
      'return' => ['target_contact_id'],
      'id' => $this->activityId,
    ]);

    return !empty($result['values'][0]['target_contact_id'][0])
      ? $result['values'][0]['target_contact_id'][0] : '';
  }

  /**
   * Returns the Case Contact display Name.
   *
   * @return string
   *   Contact display name.
   */
  private function getCaseContactDisplayName() {
    $result = civicrm_api3('CaseContact', 'get', [
      'sequential' => 1,
      'case_id' => $this->caseId,
    ]);

    $contactIds = [];
    foreach ($result['values'] as $contact) {
      $contactIds[] = $contact['contact_id'];
    }

    $result = civicrm_api3('Contact', 'get', [
      'sequential' => 1,
      'return' => ['display_name'],
      'id' => ['IN' => $contactIds],
    ]);

    $displayName = '';
    foreach ($result['values'] as $contactDetails) {
      $displayName .= $contactDetails['display_name'] . ' ';
    }

    return $displayName;
  }

  /**
   * Returns the Case Type Name.
   *
   * @return mixed
   *   Case type name.
   */
  private function getCaseTypeName() {
    $caseTypeId = $this->getCaseTypeId();

    $result = civicrm_api3('CaseType', 'getsingle', [
      'id' => $caseTypeId,
      'return' => ['title'],
    ]);

    return $result['title'];
  }

  /**
   * Gets the tags for a case.
   *
   * @return array|string
   *   Case tags.
   */
  private function getCaseTags() {
    $result = civicrm_api3('EntityTag', 'get', [
      'sequential' => 1,
      'entity_table' => 'civicrm_case',
      'api.Tag.getsingle' => ['id' => "\$value.tag_id"],
    ]);

    if ($result['count'] == 0) {
      return '';
    }

    $caseTags = '';
    foreach ($result['values'] as $caseTag) {
      $caseTags .= $caseTag['api.Tag.getsingle']['name'] . ', ';
    }

    return rtrim($caseTags, "', '");
  }

  /**
   * Gets the title for the page.
   *
   * @return string
   *   Title.
   */
  private function getPageTitle() {
    $title = $this->caseContactDisplayName . ' - ' . $this->caseTypeName;
    if ($this->caseTags) {
      $title = $title . ' - ' . $this->caseTags;
    }

    return $title;
  }

  /**
   * Gets profile fields for case type and their values.
   *
   * Returns the earlier submitted profile fields for a case activity.
   *
   * @return mixed
   *   Profile field values.
   */
  private function getProfileFieldValues() {
    $contactId = $this->getActivityTargetContact();
    $result = civicrm_api3('Profile', 'get', [
      'profile_id' => $this->profileId,
      'contact_id' => $contactId,
      'activity_id' => $this->activityId,
    ]);

    return $result['values'];
  }

  /**
   * Creates an activity of type applicant review.
   *
   * The current user is set as the source and target contact.
   * We need to make sure that the user is set as the target contact also
   * or else it will not be possible to view submitted profile
   * fields by this user.
   *
   * @return int
   *   Returns the created Activity Id.
   */
  public function createActivity() {
    $result = civicrm_api3('Activity', 'create', [
      'source_contact_id' => 'user_contact_id',
      'target_id' => 'user_contact_id',
      'activity_type_id' => 'Applicant Review',
      'case_id' => $this->caseId,
    ]);

    return $result['id'];
  }

  /**
   * Gets Custom field profile labels.
   *
   * The custom fields for a review (which is an activity) are stored
   * on a profile but the actual labels of these custom fields may change
   * so we need to fetch these labels from the custom fields and use that.
   *
   * @param array $customFieldNames
   *   Custom field names on profile.
   *
   * @return array
   *   Array of profile field label/values.
   */
  private function getCustomFieldLabels(array $customFieldNames) {
    $customFieldIds = [];
    foreach ($customFieldNames as $customFieldName) {
      $customFieldIds[] = substr($customFieldName, 7);
    }

    $results = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ['label'],
      'id' => ['IN' => $customFieldIds],
    ]);

    $customFieldLabels = [];
    foreach ($results['values'] as $customField) {
      $customFieldLabels['custom_' . $customField['id']] = $customField['label'];
    }

    return $customFieldLabels;
  }

}
