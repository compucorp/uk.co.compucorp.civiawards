<?php

use CRM_CiviAwards_ExtensionUtil as E;
use CRM_CiviAwards_BAO_AwardDetail as AwardDetail;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Service_AwardApplicationContactAccess as AwardApplicationContactAccess;
use Civi\Api4\OptionValue as OptionValueAPI;

/**
 * Form controller class.
 *
 * @see https://wiki.civicrm.org/confluence/display/CRMDOC/QuickForm+Reference
 */
class CRM_CiviAwards_Form_AwardReview extends CRM_Core_Form {

  /**
   * URL for accessing review form from SSP.
   */
  const SSP_REVIEW_URL = 'civicrm/ssp/awardreview';

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
   * Store form fields.
   *
   * @var array
   */
  private $fields;

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
      return NULL;
    }

    $this->defaultValues = [];
    $isAddAction = $this->_action & CRM_Core_Action::ADD;

    if ($this->activityId) {
      $profileFieldDefaults = [];
      CRM_Core_BAO_UFGroup::setComponentDefaults($this->fields, $this->activityId, 'Activity', $profileFieldDefaults, TRUE);
      $this->defaultValues = $profileFieldDefaults;
      $this->defaultValues['status_id'] = $this->activity['status_id'];
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
    $this->fields = CRM_Core_BAO_UFGroup::getFields(
      $this->profileId, FALSE, CRM_Core_Action::ADD, NULL,
      NULL, FALSE, NULL, FALSE, 'AwardReview', CRM_Core_Permission::CREATE, 'weight'
    );

    $error = $this->getErrorMessage();
    if ($error) {
      $this->displayErrorMessage($error);
    }
    else {
      $this->displayReviewForm();
    }
  }

  /**
   * Returns the error messages for the form if any.
   *
   * @return string
   *   Error message.
   */
  private function getErrorMessage() {
    if (empty($this->fields)) {
      return 'There are no review fields assigned to this award type.
        Please add Review Fields by editing the the Award Type located under the
        Overview dropdown in Award Dashboard.';
    }

    $isViewAction = $this->_action & CRM_Core_Action::VIEW;
    $isAddAction = $this->_action & CRM_Core_Action::ADD;
    $isUpdateAction = $this->_action & CRM_Core_Action::UPDATE;
    $hasSubmittedReview = $this->userAlreadySubmittedReview();
    $canNotViewReview = $isViewAction && $this->isReviewFromSsp() && !$this->isReviewOwner();

    if ($this->isReviewFromSsp() && $this->isCaseApplicationDeleted()) {
      $action = $isViewAction ? 'view' : 'submit';
      return "You cannot $action a review for a deleted application.";
    }

    if ($this->isReviewFromSsp() && $hasSubmittedReview && ($isAddAction || $isUpdateAction)) {
      return 'You have already submitted a review for this Award and you cannot add another review';
    }

    if ($canNotViewReview) {
      return 'You can only view the reviews that you have added!';
    }

    if ($this->isReviewFromSsp() && !$this->contactHasPanelAccessToAward()) {
      return 'You are not a member of any panel on this Award';
    }

    return NULL;
  }

  /**
   * Checks if the logged in contact has access to the Award.
   *
   * The contact has access if it belongs to a panel on the
   * award.
   *
   * @return bool
   *   Whether contact has access or not.
   */
  private function contactHasPanelAccessToAward() {
    $loggedInContact = CRM_Core_Session::getLoggedInContactID();
    $awardId = $this->getCaseTypeId();
    $awardPanelContact = new AwardPanelContact();
    $contactAccessService = new AwardApplicationContactAccess();

    try {
      $contactAccess = $contactAccessService->get($loggedInContact, $awardId, $awardPanelContact);

      return !empty($contactAccess) ? TRUE : FALSE;
    }
    catch (Exception $e) {
      return FALSE;
    }
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
      $this->redirectUser($values, $activityId);
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus(ts('An error occurred'), 'Error', 'error');
    }

  }

  /**
   * Redirects user based on submitting button and forms.
   *
   * @param array $values
   *   Form submission values.
   * @param int $activityId
   *   Activity ID.
   */
  private function redirectUser(array $values, int $activityId) {
    $status = $this->getSessionStatusText($values);
    if ($this->isReviewFromSsp() && !$values['_qf_AwardReview_submit']) {
      $url = $this->getRedirectUrlForNonSubmitReview($values, $activityId);
      CRM_Utils_System::setUFMessage(ts($status));
      CRM_Utils_System::redirect($url);
    }

    $url = $this->getRedirectUrlForSubmitReview($activityId);
    CRM_Core_Session::setStatus(ts('Your review has been ' . strtolower($status) . ' successfully.'), ts('Review ' . $status), 'success');

    if ($this->isReviewFromSsp()) {
      CRM_Utils_System::redirect($url);
    }

    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($url);
  }

  /**
   * Returns a text either Submitted or Updated based on form and action.
   *
   * @return string
   *   Status text ether Submitted or Updated.
   */
  private function getSessionStatusText(array $values) {
    $submittedStatus = 'Submitted';

    if (!$this->isReviewFromSsp()) {
      return $this->_action == CRM_Core_Action::ADD ? $submittedStatus : 'Updated';
    }

    if ($this->isReviewFromSsp() && $values['_qf_AwardReview_submit']) {
      return $submittedStatus;
    }

    if ($values['_qf_AwardReview_next']) {
      return 'Details saved successfully';
    }

    return 'Draft saved successfully';
  }

  /**
   * Returns a redirect URL for non submitted review buttons.
   *
   * @param array $values
   *   Form submission values.
   * @param int $activityId
   *   Activity ID.
   *
   * @return string
   *   Redirect URL.
   */
  private function getRedirectUrlForNonSubmitReview(array $values, int $activityId) {
    // Save & Continue button.
    if ($values['_qf_AwardReview_next']) {
      return CRM_Utils_System::url(self::SSP_REVIEW_URL, [
        'action' => 'update',
        'id' => $activityId,
        'reset' => 1,
      ]);
    }

    // Save Draft button.
    return CRM_Utils_System::url('ssp/awards/review-applications');
  }

  /**
   * Returns a URL when user submitted the review.
   *
   * @param int $activityId
   *   Activity ID.
   *
   * @return string
   *   Redirect URL for view action.
   */
  private function getRedirectUrlForSubmitReview(int $activityId) {
    $awardPath = $this->isReviewFromSsp() ? self::SSP_REVIEW_URL : self::CIVICRM_REVIEW_URL;

    return CRM_Utils_System::url($awardPath, [
      'action' => 'view',
      'id' => $activityId,
      'reset' => 1,
    ]);
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
   */
  private function displayReviewForm() {
    $isViewAction = $this->_action & CRM_Core_Action::VIEW;

    $this->assign('caseContactDisplayName', $this->getCaseContactDisplayName());
    $this->assign('caseTypeName', $this->caseTypeName);
    $this->assign('caseTags', $this->caseTags);
    $this->assign('isViewAction', $isViewAction);
    $this->assign('isReviewFromSsp', $this->isReviewFromSsp());
    $this->assign('hasSubmittedReview', $this->userAlreadySubmittedReview());

    if ($isViewAction) {
      $this->assign('sourceContactId', $this->activity['source_contact_id']);
      $this->assign('sourceContactName', $this->activity['source_contact_name']);
      $this->assign('activityStatus', $this->activity['status_id.label']);
      $editUrlParams = "action=update&id={$this->activityId}&reset=1";
      $this->assign('editUrlParams', $editUrlParams);
    }
    else {
      $this->addEntityRef('source_contact_id', ts('Reported By'));
      if (!$this->isReviewFromSsp()) {
        $this->add('select', 'status_id', ts('Status'), $this->getReviewStatus());
      }
    }

    $customFieldData = $this->getCustomFieldData(array_keys($this->fields));
    $elementData = [];

    foreach ($this->fields as $name => $field) {
      $elementData[$field['name']]['name'] = $field['name'];
      $elementData[$field['name']]['help_post'] = $customFieldData[$field['name']]['help_post'];
      $elementData[$field['name']]['help_pre'] = $customFieldData[$field['name']]['help_pre'];
      $field['title'] = $customFieldData[$field['name']]['label'];
      CRM_Core_BAO_UFGroup::buildProfile($this, $field, CRM_Profile_Form::MODE_CREATE);
    }

    $elementNames = array_keys($elementData);
    if ($isViewAction) {
      foreach ($this->_elements as $element) {
        // This check eliminates checkbox and radio buttons as
        // freeze should not be called on those elements.
        if (in_array($element->_attributes['name'], $elementNames)) {
          $element->freeze();
        }
        $this->disableField($element);
      }
    }

    if (!$isViewAction && $this->isReviewFromSsp()) {
      $sourceContact = $this->getElement('source_contact_id');
      $sourceContact->freeze();
    }

    $this->profileFields = $elementNames;
    $this->assign('elementData', $elementData);

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
      $this->addReviewButtons();
    }

    CRM_Utils_System::setTitle(E::ts($pageTitle));
  }

  /**
   * Adds buttons to the form based on form types i.e SSP, CiviAward.
   */
  private function addReviewButtons() {
    if ($this->isReviewFromSsp()) {
      $this->addButtons([
        [
          'type' => 'done',
          'name' => E::ts('Save Draft'),
          'class' => 'btn btn-primary default validate pull-left',
          'icon' => '',
        ],
        [
          'type' => 'submit',
          'name' => E::ts('Submit Review'),
          'isDefault' => TRUE,
          'class' => 'btn btn-primary default validate pull-right',
          'icon' => '',
        ],
        [
          'type' => 'next',
          'name' => E::ts('Save & Continue'),
          'class' => 'btn btn-primary default validate pull-right',
          'icon' => '',
        ],
      ]);
    }
    else {
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
  }

  /**
   * Disable field.
   *
   * @param HTML_QuickForm_element $element
   *   Form element object.
   */
  private function disableField(HTML_QuickForm_element $element) {
    if ($element instanceof HTML_QuickForm_group) {
      foreach ($element->_elements as $elem) {
        $this->setDisableAttribute($elem);
      }
      return;
    }

    $this->setDisableAttribute($element);
  }

  /**
   * Set disabled attribute on form element.
   *
   * @param HTML_QuickForm_element $element
   *   Form element object.
   */
  private function setDisableAttribute(HTML_QuickForm_element $element) {
    $attributes = $element->getAttributes();
    $attributes['disabled'] = TRUE;
    $element->setAttributes($attributes);
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
        'status_id',
        'status_id.label',
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
      return NULL;
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
    if ($this->isReviewFromSsp()) {
      // Alter display name of contact to "Anonymous [case-id]" if award
      // ssp review panel has anonymize_application set to true.
      $userID = CRM_Core_Session::getLoggedInContactID();
      $contactAccessService = new CRM_CiviAwards_Service_AwardApplicationContactAccess();
      $awardPanelContact = new CRM_CiviAwards_Service_AwardPanelContact();
      $contactAccess = $contactAccessService->getReviewAccess($userID, $this->caseId, $awardPanelContact);
      if (!empty($contactAccess) && $contactAccess['anonymize_application']) {
        return 'Anonymous ' . $this->caseId;
      }
    }

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
      'entity_id' => $this->caseId,
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
      'status_id' => $this->getSubmittedReviewStatus($values),
      'case_id' => $this->caseId,
    ]);

    return $result['id'];
  }

  /**
   * Gets Custom field profile data.
   *
   * The custom fields for a review (which is an activity) are stored
   * on a profile but the actual labels of these custom fields may change
   * so we need to fetch these labels from the custom fields and use that.
   * Also we need some other data from the custom fields like pre and post
   * help fields.
   *
   * @param array $customFieldNames
   *   Custom field names on profile.
   *
   * @return array
   *   Array of profile field label/values.
   */
  private function getCustomFieldData(array $customFieldNames) {
    $customFieldIds = [];
    foreach ($customFieldNames as $customFieldName) {
      $customFieldIds[] = substr($customFieldName, 7);
    }

    $results = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ['label', 'help_pre', 'help_post'],
      'id' => ['IN' => $customFieldIds],
    ]);

    $customFieldData = [];
    foreach ($results['values'] as $customField) {
      $customFieldData['custom_' . $customField['id']]['label'] = $customField['label'];
      $customFieldData['custom_' . $customField['id']]['help_pre'] = $customField['help_pre'];
      $customFieldData['custom_' . $customField['id']]['help_post'] = $customField['help_post'];
    }

    return $customFieldData;
  }

  /**
   * Updates the current activity with the submitted form values.
   */
  private function updateActivity() {
    $values = $this->exportValues();

    civicrm_api3('Activity', 'create', [
      'id' => $this->activityId,
      'source_contact_id' => $values['source_contact_id'],
      'status_id' => $this->getSubmittedReviewStatus($values),
    ]);
  }

  /**
   * Gets submitted activity status based on button and form.
   *
   * @param array $values
   *   Form submission values.
   *
   * @return string
   *   Review status
   */
  private function getSubmittedReviewStatus(array $values) {
    // Save draft && Save & Continue buttons.
    if ($values['_qf_AwardReview_next'] || $values['_qf_AwardReview_done']) {
      return 'Draft';
    }

    // Status ID submitted only by CiviCRM form.
    // return 'Completed' if the form submitted by SSP.
    return $values['status_id'] ?? 'Completed';
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
      'status_id' => 'Completed',
    ]);

    return $result['count'] > 0;
  }

  /**
   * Checks if Case Application is deleted.
   *
   * @return bool
   *   Bool value.
   */
  private function isCaseApplicationDeleted() {
    $result = civicrm_api3('Case', 'get', [
      'sequential' => 1,
      'id' => $this->caseId,
      'is_deleted' => 0,
    ]);

    return empty($result['values']) ? TRUE : FALSE;
  }

  /**
   * Gets Completed and Draft status from activity status.
   *
   * @return array
   *   Completed and Draft activity type
   */
  private function getReviewStatus() {
    $optionValues = OptionValueAPI::get()
      ->addWhere('option_group_id:name', '=', 'activity_status')
      ->addWhere('grouping', 'CONTAINS', 'Applicant Review')
      ->execute();

    $status = [];
    foreach ($optionValues as $optionValue) {
      if (!in_array($optionValue['label'], ['Completed', 'Draft'])) {
        continue;
      }
      $status[$optionValue['value']] = $optionValue['label'];
    }

    return $status;
  }

}
