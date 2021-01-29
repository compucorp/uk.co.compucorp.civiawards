<?php

use CRM_CiviAwards_ExtensionUtil as E;

/**
 * Award payments form controller class.
 */
class CRM_CiviAwards_Form_AwardPayment extends CRM_Core_Form {

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
   * Custom fields group tree.
   *
   * @var array
   */
  private $groupTree;

  /**
   * Activity statuses.
   *
   * @var array
   */
  private $activityStatuses;

  /**
   * Activity details.
   *
   * @var array
   */
  private $activity;

  /**
   * {@inheritDoc}
   */
  public function setDefaultValues() {
    $hasDefaultValues = !empty($this->_defaultValues);

    if ($hasDefaultValues) {
      return;
    }
    $this->_defaultValues = [];
    $this->_defaultValues['activity_date_time'] = date('Y-m-d H:i:s');
    $this->_defaultValues['status_id'] = $this->getValueForActivityStatus('approved_complete');

    if ($this->activityId) {
      $this->_defaultValues = $this->activity;
      $this->_defaultValues['target_contact_id'] = !empty($this->activity['target_contact_id'][0]) ? $this->activity['target_contact_id'][0] : NULL;
      CRM_Core_BAO_CustomGroup::setDefaults($this->groupTree, $this->_defaultValues);
    }

    return $this->_defaultValues;
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

    $this->setActivityStatuses();
    $this->setCustomFieldGroupTree();
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $isViewAction = $this->_action & CRM_Core_Action::VIEW;
    $isUpdateAction = $this->_action & CRM_Core_Action::UPDATE;
    $this->addFormFields();
    $this->assign('isViewAction', $isViewAction);
    $this->assign('isUpdateAction', $isUpdateAction);
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('entityID', $this->activityId);
    $this->assign('groupID', NULL);
    $this->assign('subType', NULL);

    if ($isViewAction) {
      $this->freeze();
      $editUrlParams = "action=update&id={$this->activityId}&reset=1";
      $this->assign('editUrlParams', $editUrlParams);
    }
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    $values = $this->exportValues();

    if ($this->_action & CRM_Core_Action::ADD) {
      $activityId = $this->createActivity();
    }
    else {
      $this->updateActivity();
      $activityId = $this->activityId;
    }

    CRM_Core_BAO_CustomValueTable::postProcess(
      $values,
      'civicrm_activity',
      $activityId,
      'Activity'
    );

    $status = $this->_action == CRM_Core_Action::ADD ? 'saved' : 'updated';
    CRM_Core_Session::setStatus(ts('Record ' . strtolower($status) . ' successfully.'), ts('Record ' . $status), 'success');

    $url = CRM_Utils_System::url('civicrm/awardpayment', [
      'action' => 'view',
      'id' => $activityId,
      'reset' => 1,
    ]);
    $session = CRM_Core_Session::singleton();
    $session->pushUserContext($url);
  }

  /**
   * Creates an activity of type awards payment.
   *
   * @return int
   *   Returns the created Activity Id.
   */
  public function createActivity() {
    $values = $this->exportValues();
    $defaultParams = $this->getDefaultActivityParams($values);
    $params = array_merge($defaultParams, [
      'activity_type_id' => 'Awards Payment',
    ]);
    $result = civicrm_api3('Activity', 'create', $params);

    return $result['id'];
  }

  /**
   * Returns default activity params.
   *
   * @param array $values
   *   Form values for submitted activity params.
   *
   * @return array
   *   Default activity params.
   */
  private function getDefaultActivityParams(array $values) {
    return [
      'target_id' => $values['target_contact_id'],
      'details' => $values['details'],
      'status_id' => $values['status_id'],
      'activity_date_time' => $values['activity_date_time'],
      'case_id' => $this->caseId,
    ];
  }

  /**
   * Updates the current activity with the submitted form values.
   */
  private function updateActivity() {
    $values = $this->exportValues();
    $defaultParams = $this->getDefaultActivityParams($values);
    $params = array_merge($defaultParams, [
      'id' => $this->activityId,
    ]);

    civicrm_api3('Activity', 'create', $params);
  }

  /**
   * Sets the custom fields group tree.
   *
   * The custom fields group tree is needed to generate the form fields
   * for the custom fields and also to set defaults for these form fields.
   */
  private function setCustomFieldGroupTree() {
    $groupTree = CRM_Core_BAO_CustomGroup::getTree(
      'Activity',
      NULL,
      $this->activityId ? $this->activityId : NULL,
      NULL,
      $this->getActivityTypeValue('Awards Payment'),
      NULL,
      TRUE,
      TRUE
    );
    $groupTree = CRM_Core_BAO_CustomGroup::formatGroupTree($groupTree, 1, $this);
    $this->groupTree = $groupTree;
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
        'activity_date_time',
        'target_contact_id',
        'details',
        'status_id',
      ],
    ]);
  }

  /**
   * Returns the `value` field for the option value.
   *
   * The function returns option value `values` belonging
   * to the activity type option group.
   *
   * @param string $optionValueName
   *   The option value name.
   *
   * @return mixed
   *   Option value.
   */
  private function getActivityTypeValue($optionValueName) {
    $result = civicrm_api3('OptionValue', 'getsingle', [
      'option_group_id' => 'activity_type',
      'name' => $optionValueName,
    ]);

    return $result['value'];
  }

  /**
   * Adds the form fields for the Awards Payment form.
   */
  private function addFormFields() {
    $this->addEntityRef('target_contact_id', ts('Payee'), [], TRUE);
    // Adds custom field form fields.
    CRM_Core_BAO_CustomGroup::buildQuickForm($this, $this->groupTree);
    $this->add('datepicker', 'activity_date_time', ts('Due Date'), [], FALSE, ['time' => TRUE]);
    $this->addSelect(
      'status_id',
      [
        'label' => ts('Status'),
        'options' => array_column($this->activityStatuses, 'label', 'value'),
      ],
      TRUE
    );
    $this->add(
      'textarea',
      'details',
      ts('Notes'),
      ['rows' => 6, 'cols' => 40]
    );

    if (in_array(
      $this->_action,
      [CRM_Core_Action::ADD, CRM_Core_Action::UPDATE])
    ) {
      $this->addButtons(
        [
          [
            'type' => 'cancel',
            'name' => ts('Cancel'),
          ],
          [
            'type' => 'upload',
            'name' => ts('Save'),
            'isDefault' => TRUE,
          ],
        ]
      );
    }
    CRM_Utils_System::setTitle(E::ts($this->getPageTitle()));
  }

  /**
   * Returns the form page title.
   *
   * @return string
   *   Page title.
   */
  private function getPageTitle() {
    if ($this->_action & CRM_Core_Action::VIEW) {
      return 'View Payment';
    }

    if ($this->_action & CRM_Core_Action::UPDATE) {
      return 'Edit Payment';
    }

    if ($this->_action & CRM_Core_Action::ADD) {
      return 'Create New Payment';
    }
  }

  /**
   * Sets the activity statuses.
   */
  private function setActivityStatuses() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'activity_status',
      'grouping' => 'Awards Payments',
    ]);

    $this->activityStatuses = $result['values'];
  }

  /**
   * Returns the value of one of the option values of the activity status group.
   *
   * @param string $statusName
   *   Activity status name.
   *
   * @return mixed
   *   Option value
   */
  private function getValueForActivityStatus($statusName) {
    $activityStatuses = $this->activityStatuses;
    foreach ($activityStatuses as $activityStatus) {
      if ($activityStatus['name'] == $statusName) {
        return $activityStatus['value'];
      }
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array
   *   Form field names for the form.
   */
  public function getRenderableElementNames() {
    $elementNames = [];
    $allFieldsToInsert = $this->getAggregatedFieldsToInsertAfter();
    $toInsertAfter = $this->getFieldsToInsertAfter();
    foreach ($this->_elements as $element) {
      $label = $element->getLabel();
      if (empty($label)) {
        continue;
      }
      $elementName = $element->getName();
      $tagName = $element->getAttribute('data-crm-custom') ? $element->getAttribute('data-crm-custom') : $elementName;
      if (in_array($elementName, $allFieldsToInsert)) {
        continue;
      }
      $elementNames[] = $elementName;
      if (!empty($toInsertAfter[$tagName])) {
        $elementNames = array_merge($elementNames, $toInsertAfter[$tagName]);
      }
    }

    return $elementNames;
  }

  /**
   * Returns all the fields to be inserted after an element.
   *
   * @return array
   *   All fields to insert after.
   */
  private function getAggregatedFieldsToInsertAfter() {
    $fields = $this->getFieldsToInsertAfter();
    $allFields = [];
    foreach ($fields as $field) {
      $allFields = array_merge($allFields, $field);
    }

    return $allFields;
  }

  /**
   * Returns form fields to insert after.
   *
   * The arrays contains a list of form elements with the
   * form field which form elements will be inserted after as the key
   * and the value is the form elements that will be inserted after.
   *
   * @return array
   *   Elements to insert after array.
   */
  private function getFieldsToInsertAfter() {
    return [
      'Awards_Payment_Information:Payment_Currency' => ['activity_date_time'],
      'Awards_Payment_Information:Payee_Ref' => ['separator'],
      'Awards_Payment_Information:Date_Paid' => ['separator'],
      'status_id' => ['separator'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultEntity() {
    return 'Activity';
  }

}
