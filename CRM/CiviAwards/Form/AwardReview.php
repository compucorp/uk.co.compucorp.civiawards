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
   * URL for accessing review form from SSP.
   */
  const SSP_REVIEW_URL = 'civicrm/award-review-ssp';

  /**
   * URL for accessing review form from civicrm.
   */
  const CIVICRM_REVIEW_URL = 'civicrm/awardreview';

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
   * Activity details.
   *
   * @var array
   */
  private $activity;

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
    $hasDefaultValues = !empty($this->defaultValues);

    if ($hasDefaultValues) {
      return;
    }

    $this->defaultValues = [];
    $isAddAction = $this->_action & CRM_Core_Action::ADD;

    if ($this->activityId) {
      $this->defaultValues = $this->getProfileFieldValues();
    }

    $this->defaultValues['source_contact_id'] = $isAddAction
      ? CRM_Core_Session::getLoggedInContactID()
      : $this->activity['source_contact_id'];

    return $this->defaultValues;
  }

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    if (!in_array($this->_action, [CRM_Core_Action::ADD, CRM_Core_Action::VIEW])) {
      throw new Exception('Action not supported!');
    }

    if ($this->_action & CRM_Core_Action::ADD) {
      $this->caseId = CRM_Utils_Request::retrieve('case_id', 'Positive', $this);
    }
    else {
      $this->activityId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
      $this->activity = $this->getActivity();
      $this->caseId = CRM_Utils_Array::first($this->activity['case_id']);
    }

    $this->profileId = $this->getProfileIdFromCaseType();
    $this->caseContactDisplayName = $this->getCaseContactDisplayName();
    $this->caseTypeName = $this->getCaseTypeName();
    $this->caseTags = $this->loadCaseTags();
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $fields = CRM_Core_BAO_UFGroup::getFields(
      $this->profileId, FALSE, CRM_Core_Action::ADD, NULL,
      NULL, FALSE, NULL, FALSE, NULL, CRM_Core_Permission::CREATE, 'weight'
    );

    $error = $this->getErrorMessage($fields);
    if ($error) {
      $this->displayErrorMessage($error);
    }
    else {
      $this->displayReviewForm($fields);
    }
  }

  /**
   * Returns the error messages for the form if any.
   *
   * @param array|null $fields
   *   Form fields.
   *
   * @return string
   *   Error message.
   */
  private function getErrorMessage($fields) {
    if (empty($fields)) {
      return 'There are no review fields assigned to this award type.
        Please add Review Fields by editing the the Award Type located under the
        Overview dropdown in Award Dashboard.';
    }

    $isViewAction = $this->_action & CRM_Core_Action::VIEW;
    $isAddAction = $this->_action & CRM_Core_Action::ADD;
    $hasSubmittedReview = $this->userAlreadySubmittedReview();
    $canNotViewReview = $isViewAction && $this->isReviewFromSsp() && !$this->isReviewOwner();

    if ($hasSubmittedReview && $isAddAction) {
      return 'You have already submitted a review for this Award and you can not add another review';
    }

    if ($canNotViewReview) {
      return 'You can only view the reviews that you have added!';
    }

    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    $values = $this->exportValues();
    foreach ($this->profileFields as $profileField) {
      $value = $values[$profileField];
      $value = is_array($value) ? array_filter($value) : $value;
      $profileFields[$profileField] = $value;
    }

    if ($this->_action & CRM_Core_Action::ADD) {
      $activityId = $this->createActivity();
      $activityContact = CRM_Core_Session::getLoggedInContactID();
    }
    else {
      $activityId = $this->activityId;
      $activityContact = $this->getActivityTargetContact();

      $this->updateActivity();
    }

    $profileFields['activity_id'] = $activityId;
    $profileFields['contact_id'] = $activityContact;
    $profileFields['profile_id'] = $this->profileId;

    try {
      civicrm_api3('Profile', 'submit', $profileFields);
      $status = $this->_action == CRM_Core_Action::ADD ? 'Submitted' : 'Updated';
      CRM_Core_Session::setStatus(ts('The review has been ' . strtolower($status) . ' successfully.'), ts('Review ' . $status), 'success');
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus(ts('An error occurred'), 'Error', 'error');
    }

    $awardPath = $this->isReviewFromSsp() ? self::SSP_REVIEW_URL : self::CIVICRM_REVIEW_URL;
    $url = CRM_Utils_System::url($awardPath, [
      'action' => 'view',
      'id' => $activityId,
      'reset' => 1,
    ]);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($url);
  }

  /**
   * Displays an error message when there are errors for the form.
   *
   * @param string $errorMessage
   *   Error message.
   */
  private function displayErrorMessage($errorMessage) {
    CRM_Utils_System::setTitle(E::ts('Warning'));

    $this->assign('errorMessage', $errorMessage);

    $this->addButtons([
      [
        'type' => 'cancel',
        'name' => E::ts('Dismiss'),
      ],
    ]);
  }

  /**
   * Displays the View, Create, or Update form using the given review fields.
   *
   * @param array $fields
   *   A list of review fields to use when displaying the form.
   */
  private function displayReviewForm(array $fields) {
    $isViewAction = $this->_action & CRM_Core_Action::VIEW;

    $this->assign('caseContactDisplayName', $this->getCaseContactDisplayName());
    $this->assign('caseTypeName', $this->caseTypeName);
    $this->assign('isViewAction', $isViewAction);
    $this->assign('isReviewFromSsp', $this->isReviewFromSsp());

    if ($isViewAction) {
      $this->assign('sourceContactId', $this->activity['source_contact_id']);
      $this->assign('sourceContactName', $this->activity['source_contact_name']);
      $editUrlParams = "action=update&id={$this->activityId}&reset=1";
      $this->assign('editUrlParams', $editUrlParams);
    }
    else {
      $this->addEntityRef('source_contact_id', ts('Reported By'));
    }

    $customFieldLabels = $this->getCustomFieldLabels(array_keys($fields));
    $elementNames = [];

    foreach ($fields as $name => $field) {
      $elementNames[] = $field['name'];
      $field['title'] = $customFieldLabels[$field['name']];
      CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
    }

    if ($isViewAction) {
      foreach ($this->_elements as $element) {
        if (in_array($element->_attributes['name'], $elementNames)) {
          $element->freeze();
        }
      }
    }

    if (!$isViewAction && $this->isReviewFromSsp()) {
      $sourceContact = $this->getElement('source_contact_id');
      $sourceContact->freeze();
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
          'type' => 'done',
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
   * Returns case and source contact details for the current activity.
   *
   * @return array
   *   Activity details.
   */
  private function getActivity() {
    return civicrm_api3('Activity', 'getsingle', [
      'id' => $this->activityId,
      'return' => [
        'case_id',
        'source_contact_id',
        'source_contact_name',
      ],
    ]);
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
   * Load the tags for a case.
   *
   * @return array|string
   *   Case tags.
   */
  private function loadCaseTags() {
    $result = civicrm_api3('EntityTag', 'get', [
      'sequential' => 1,
      'entity_table' => 'civicrm_case',
      'api.Tag.getsingle' => ['id' => "\$value.tag_id"],
    ]);

    if ($result['count'] == 0) {
      return [];
    }

    $caseTags = [];
    foreach ($result['values'] as $caseTag) {
      $caseTag = [
        'id' => $caseTag['api.Tag.getsingle']['id'],
        'parent_id' => $caseTag['api.Tag.getsingle']['parent_id'],
        'name' => $caseTag['api.Tag.getsingle']['name'],
        'background_color' => !empty($caseTag['api.Tag.getsingle']['color']) ? $caseTag['api.Tag.getsingle']['color'] : '#ffffff',
      ];
      $caseTag['color'] = $this->getTextColor($caseTag['background_color']);
      $caseTags[] = $caseTag;
    }

    return $caseTags;
  }

  /**
   * Get Case tags list.
   *
   * @return string
   *   HTML code of Case tags as badge list.
   */
  private function getCaseTags() {
    $res = [];
    foreach ($this->caseTags as $caseTag) {
      $res[] = '<span class="crm-tag-item" style="background-color: ' . $caseTag['background_color'] . '; color: ' . $caseTag['color'] . '">'
        . $caseTag['name'] . '</span>';
    }

    return implode(' ', $res);
  }

  /**
   * Returns contrasting text color for given background color.
   *
   * @param string $bgColor
   *   Background color in hex format (e.g: #ffffff).
   *
   * @return string
   *   Text color suitable for background color in hex format (e.g: #000000).
   *   Returns black text color for light background and white text color
   *   for dark background.
   */
  private function getTextColor($bgColor) {
    // Ensure that the color code will have # in the beginning.
    if (strpos($bgColor, '#') === FALSE) {
      $bgColor = '#' . $bgColor;
    }

    // Calculate background color luminance.
    list($r, $g, $b) = sscanf($bgColor, "#%02x%02x%02x");
    $luminance = 1 - (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    // Calculate text color.
    $color = $luminance < 0.5 ? '#000000' : '#ffffff';

    return $color;
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
      $title = $title . ' &nbsp; ' . $this->getCaseTags();
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
    $values = $this->exportValues();
    $result = civicrm_api3('Activity', 'create', [
      'source_contact_id' => $values['source_contact_id'],
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

  /**
   * Updates the current activity with the submitted form values.
   */
  private function updateActivity() {
    $values = $this->exportValues();

    civicrm_api3('Activity', 'create', [
      'id' => $this->activityId,
      'source_contact_id' => $values['source_contact_id'],
    ]);
  }

  /**
   * Checks if this form was loaded from SSP portal.
   *
   * @return bool
   *   From SSP portal or not.
   */
  private function isReviewFromSsp() {
    return CRM_Utils_System::currentPath() == self::SSP_REVIEW_URL;
  }

  /**
   * Checks if logged in user is the owner of review activity.
   *
   * @return bool
   *   Whether owner or not.
   */
  private function isReviewOwner() {
    return $this->activity['source_contact_id'] == CRM_Core_Session::getLoggedInContactID();
  }

  /**
   * Checks if the user has already submitted a review for the Award.
   *
   * @return bool
   *   Bool value.
   */
  private function userAlreadySubmittedReview() {
    $result = civicrm_api3('Activity', 'get', [
      'activity_type_id' => 'Applicant Review',
      'case_id' => $this->caseId,
      'source_contact_id' => "user_contact_id",
    ]);

    return $result['count'] > 0;
  }

}
