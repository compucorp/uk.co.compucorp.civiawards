<?php

use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Test_Fabricator_Case as CaseFabricator;

/**
 * Test class for Award Payment form.
 *
 * @group headless
 */
class CRM_CiviAwards_Form_AwardPaymentTest extends BaseHeadlessTest {

  use CRM_CiviAwards_Helper_SessionTrait;

  /**
   * Activity Statuses.
   *
   * @var array
   */
  private $activityStatuses;

  /**
   * Activity Contact.
   *
   * @var int
   */
  private $activityContact;

  /**
   * Case activity data.
   *
   * @var array
   */
  private $activityCase;

  /**
   * Set up data.
   */
  public function setUp() {
    $this->activityContact = CRM_CiviAwards_Test_Fabricator_Contact::fabricate()['id'];
    $this->setActivityStatuses();
    $this->setActivityCase();
  }

  /**
   * Test Error is thrown when updating exported activity.
   */
  public function testErrorIsThrownWhenUserIsTryingToUpdateAnExportedActivity() {
    $activityId = $this->createActivity('exported_complete');
    $form = $this->initializeAwardsForm(CRM_Core_Action::UPDATE);
    $form->set('id', $activityId);
    $form->set('case_id', $this->activityCase['id']);
    $this->expectException(Exception::class);
    $this->expectExceptionMessage(
      "Action not supported!"
    );
    $form->preProcess();
  }

  /**
   * Test activity is updated correctly.
   */
  public function testActivityFormValuesAreCorrectlyUpdated() {
    $activityId = $this->createActivity('approved_complete');
    $formValues = [
      'status_id' => $this->getValueForActivityStatus('paid_complete'),
      'target_contact_id' => $this->activityContact,
      'details' => 'This is test details',
      'activity_date_time' => date('Y-m-d H:i:s'),
    ];
    $form = $this->initializeAwardsForm(CRM_Core_Action::UPDATE, $formValues);
    $form->set('id', $activityId);
    $form->buildForm();
    $form->postProcess();
    $result = $this->getActivity($activityId);
    $this->assertEquals($formValues['details'], $result['details']);
    $this->assertEquals($formValues['target_contact_id'], $result['target_contact_id'][0]);
    $this->assertEquals($formValues['status_id'], $result['status_id']);
  }

  /**
   * Test activity is created correctly.
   */
  public function testActivityIsCorrectlyCreated() {
    $this->registerCurrentLoggedInContactInSession($this->activityContact);
    $formValues = [
      'status_id' => $this->getValueForActivityStatus('paid_complete'),
      'target_contact_id' => $this->activityContact,
      'details' => 'Test details',
      'activity_date_time' => date('Y-m-d H:i:s'),
    ];
    $form = $this->initializeAwardsForm(CRM_Core_Action::ADD, $formValues);
    $form->set('case_id', $this->activityCase['id']);
    $form->buildForm();
    $form->postProcess();
    $result = $this->getActivity($this->getCaseActivity($this->activityCase['id']));
    $this->assertEquals($formValues['details'], $result['details']);
    $this->assertEquals($formValues['target_contact_id'], $result['target_contact_id'][0]);
    $this->assertEquals($formValues['status_id'], $result['status_id']);
    $this->assertEquals($this->activityCase['id'], $result['case_id'][0]);
  }

  /**
   * Test activity status is exported correctly set in template.
   *
   * @dataProvider getDataForIsTestingIsExported
   */
  public function testIsExportedStatusIsCorrectlySet($activityStaus, $isExportedValue) {
    $activityId = $this->createActivity($activityStaus);
    $form = $this->initializeAwardsForm(CRM_Core_Action::VIEW);
    $form->set('id', $activityId);
    $form->buildForm();
    $isActivityStatusExported = $form->getTemplate()->get_template_vars('isActivityStatusExported');
    $this->assertEquals($isExportedValue, $isActivityStatusExported);
  }

  /**
   * Test activity status is locked correctly set in template.
   *
   * @dataProvider getDataForActivityStatusIsLocked
   */
  public function testActivityStatusIsLockedCorrectlySet($activityStaus, $isExportedValue) {
    $activityId = $this->createActivity($activityStaus);
    $form = $this->initializeAwardsForm(CRM_Core_Action::VIEW);
    $form->set('id', $activityId);
    $form->buildForm();
    $activityStatusIsLocked = $form->getTemplate()->get_template_vars('activityStatusIsLocked');
    $this->assertEquals($isExportedValue, $activityStatusIsLocked);
  }

  /**
   * Get activity linked to the case.
   *
   * @param int $caseId
   *   Case Id.
   *
   * @return int
   *   Activity id.
   */
  private function getCaseActivity($caseId) {
    $activity = civicrm_api3('Activity', 'getsingle', [
      'sequential' => 1,
      'case_id' => $caseId,
    ]);

    return $activity['id'];
  }

  /**
   * Data set for testIsExportedStatusIsCorrectlySet.
   *
   * @return array
   *   Data set.
   */
  public function getDataForIsTestingIsExported() {
    return [
      ['approved_complete', FALSE],
      ['approved_complete', FALSE],
      ['exported_complete', TRUE],
    ];
  }

  /**
   * Data set for testActivityStatusIsLockedCorrectlySet.
   *
   * @return array
   *   Data set.
   */
  public function getDataForActivityStatusIsLocked() {
    return [
      ['paid_complete', TRUE],
      ['failed_incomplete', TRUE],
      ['exported_complete', TRUE],
      ['approved_complete', FALSE],
      ['applied_for_incomplete', FALSE],
    ];
  }

  /**
   * Get activity.
   *
   * @param int $activityId
   *   Activity Id.
   *
   * @return array
   *   Activity data.
   */
  private function getActivity($activityId) {
    return civicrm_api3('Activity', 'getsingle', [
      'id' => $activityId,
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
   * Get value for activity status.
   *
   * @param string $statusName
   *   Status Name.
   *
   * @return mixed
   *   Activity status value.
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
   * Create Activity.
   *
   * @param string $activityStatusName
   *   Activity status name.
   *
   * @return int
   *   Activity Id.
   */
  public function createActivity($activityStatusName) {
    $result = civicrm_api3('Activity', 'create', [
      'status_id' => $this->getValueForActivityStatus($activityStatusName),
      'source_contact_id' => $this->activityContact,
      'case_id' => $this->activityCase['id'],
      'activity_type_id' => 'Awards Payment',
    ]);

    return $result['id'];
  }

  /**
   * Fabricates a case and sets it for use.
   */
  private function setActivityCase() {
    $caseType = CaseTypeFabricator::fabricate();
    $this->activityCase = CaseFabricator::fabricate(
      ['status_id' => 1, 'case_type_id' => $caseType['id']]
    );
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
   * Gets initialized form object.
   *
   * @param string $action
   *   Form action.
   * @param array $formValues
   *   Form values.
   *
   * @return \CRM_CiviAwards_Form_AwardPayment
   *   Form object.
   */
  private function initializeAwardsForm($action, array $formValues = []) {
    $form = new CRM_CiviAwards_Form_AwardPayment();
    $form->controller = new CRM_Core_Controller_Simple('CRM_CiviAwards_Form_AwardPayment', 'Award Payment');
    $form->_action = $action;
    $form->_submitValues = $formValues;

    return $form;
  }

}
