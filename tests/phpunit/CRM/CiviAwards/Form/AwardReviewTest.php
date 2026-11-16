<?php

use CRM_CiviAwards_Helper_CaseTypeCategory as CaseTypeCategory;
use CRM_CiviAwards_Test_Fabricator_Case as CaseFabricator;
use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Test_Fabricator_Contact as ContactFabricator;

/**
 * Test class for the Award Review form.
 *
 * @group headless
 */
class CRM_CiviAwards_Form_AwardReviewTest extends BaseHeadlessTest {

  use CRM_CiviAwards_Helper_SessionTrait;

  /**
   * Reviewer contact ID.
   *
   * @var int
   */
  private $reviewerId;

  /**
   * Award application (case) ID.
   *
   * @var int
   */
  private $caseId;

  /**
   * ID of the Number review field.
   *
   * @var int
   */
  private $numberFieldId;

  /**
   * ID of the Text review field.
   *
   * @var int
   */
  private $textFieldId;

  /**
   * Sets up an award application with a Number and a Text review field.
   */
  public function setUp() {
    $this->reviewerId = ContactFabricator::fabricate()['id'];
    $this->registerCurrentLoggedInContactInSession($this->reviewerId);
    $this->createReviewFields();
    $this->createAwardApplication();
  }

  /**
   * Restores the default number format and logs the reviewer out.
   */
  public function tearDown() {
    $this->setNumberFormat(',', '.');
    $this->unregisterCurrentLoggedInContactFromSession();
  }

  /**
   * Test a Number field in the site's number format is saved on add.
   *
   * @dataProvider getNumbersInSiteFormat
   */
  public function testNumberFieldInSiteFormatIsSavedWhenAddingReview($thousandSeparator, $decimalPoint, $submittedNumber, $expectedNumber) {
    $this->setNumberFormat($thousandSeparator, $decimalPoint);

    $activityId = $this->submitReview(CRM_Core_Action::ADD, $submittedNumber, 'First review');

    $this->assertEquals($expectedNumber, (float) $this->getStoredValue($this->numberFieldId, $activityId));
    $this->assertEquals('First review', $this->getStoredValue($this->textFieldId, $activityId));
  }

  /**
   * Test a Number field in the site's number format is saved on update.
   *
   * CiviCRM pre-fills Number fields in the site's number format (e.g.
   * "5,000") when a review is edited, so saving the review again sends
   * the number back in that format.
   *
   * @dataProvider getNumbersInSiteFormat
   */
  public function testNumberFieldInSiteFormatIsSavedWhenUpdatingReview($thousandSeparator, $decimalPoint, $submittedNumber, $expectedNumber) {
    $this->setNumberFormat($thousandSeparator, $decimalPoint);
    $activityId = $this->submitReview(CRM_Core_Action::ADD, '1', 'First review');

    $this->submitReview(CRM_Core_Action::UPDATE, $submittedNumber, 'Edited review', $activityId);

    $this->assertEquals($expectedNumber, (float) $this->getStoredValue($this->numberFieldId, $activityId));
    $this->assertEquals('Edited review', $this->getStoredValue($this->textFieldId, $activityId));
  }

  /**
   * Numbers as a reviewer sees them, for different site number formats.
   *
   * @return array
   *   Thousand separator, decimal point, submitted number, stored number.
   */
  public function getNumbersInSiteFormat() {
    return [
      'thousand separator' => [',', '.', '5,000', 5000],
      'thousand separator and decimals' => [',', '.', '12,345.5', 12345.5],
      'no separators' => [',', '.', '5000', 5000],
      'decimal comma site format' => ['.', ',', '12.345,5', 12345.5],
    ];
  }

  /**
   * Submits the review form as the reviewer.
   *
   * @param int $action
   *   Form action, CRM_Core_Action::ADD or CRM_Core_Action::UPDATE.
   * @param string $number
   *   Value for the Number review field.
   * @param string $text
   *   Value for the Text review field.
   * @param int|null $activityId
   *   Review activity ID, when updating a review.
   *
   * @return int
   *   Review activity ID.
   */
  private function submitReview($action, $number, $text, $activityId = NULL) {
    $form = new CRM_CiviAwards_Form_AwardReview();
    $form->controller = new CRM_Core_Controller_Simple('CRM_CiviAwards_Form_AwardReview', 'Award Review');
    $form->_action = $action;
    $form->_submitValues = [
      'source_contact_id' => $this->reviewerId,
      'status_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Completed'),
      'custom_' . $this->numberFieldId => $number,
      'custom_' . $this->textFieldId => $text,
    ];

    if ($activityId) {
      $form->set('id', $activityId);
    }
    else {
      $form->set('case_id', $this->caseId);
    }

    $form->buildForm();
    $form->postProcess();

    return $activityId ?? civicrm_api3('Activity', 'getvalue', [
      'case_id' => $this->caseId,
      'activity_type_id' => 'Applicant Review',
      'return' => 'id',
    ]);
  }

  /**
   * Returns the value stored in the database for a review field.
   *
   * @param int $customFieldId
   *   Custom field ID.
   * @param int $activityId
   *   Review activity ID.
   *
   * @return string|null
   *   Stored value.
   */
  private function getStoredValue($customFieldId, $activityId) {
    [$tableName, $columnName] = CRM_Core_BAO_CustomField::getTableColumnGroup($customFieldId);

    return CRM_Core_DAO::singleValueQuery(
      "SELECT {$columnName} FROM {$tableName} WHERE entity_id = %1",
      [1 => [$activityId, 'Positive']]
    );
  }

  /**
   * Sets the site's thousand separator and decimal point.
   *
   * @param string $thousandSeparator
   *   Thousand separator.
   * @param string $decimalPoint
   *   Decimal point.
   */
  private function setNumberFormat($thousandSeparator, $decimalPoint) {
    Civi::settings()->set('monetaryThousandSeparator', $thousandSeparator);
    Civi::settings()->set('monetaryDecimalPoint', $decimalPoint);
  }

  /**
   * Creates a Number and a Text review field.
   */
  private function createReviewFields() {
    $customGroup = civicrm_api3('CustomGroup', 'create', [
      'title' => 'Review ' . uniqid(),
      'extends' => 'Activity',
      'extends_entity_column_value' => [
        CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Applicant Review'),
      ],
    ]);

    $this->numberFieldId = civicrm_api3('CustomField', 'create', [
      'custom_group_id' => $customGroup['id'],
      'label' => 'Amount requested',
      'data_type' => 'Float',
      'html_type' => 'Text',
    ])['id'];

    $this->textFieldId = civicrm_api3('CustomField', 'create', [
      'custom_group_id' => $customGroup['id'],
      'label' => 'Comments',
      'data_type' => 'String',
      'html_type' => 'Text',
    ])['id'];
  }

  /**
   * Creates an award using the review fields, and an application for it.
   */
  private function createAwardApplication() {
    $caseType = CaseTypeFabricator::fabricate([
      'case_type_category' => civicrm_api3('OptionValue', 'getvalue', [
        'option_group_id' => 'case_type_categories',
        'name' => CaseTypeCategory::AWARDS_CASE_TYPE_CATEGORY_NAME,
        'return' => 'value',
      ]),
    ]);

    civicrm_api3('AwardDetail', 'create', [
      'case_type_id' => $caseType['id'],
      'start_date' => date('Y-m-d'),
      'award_subtype' => civicrm_api3('OptionValue', 'getvalue', [
        'option_group_id' => 'civiawards_award_subtype',
        'options' => ['limit' => 1],
        'return' => 'value',
      ]),
      'award_manager' => [$this->reviewerId],
      'review_fields' => [
        ['id' => $this->numberFieldId, 'weight' => 1, 'required' => FALSE],
        ['id' => $this->textFieldId, 'weight' => 2, 'required' => FALSE],
      ],
    ]);

    $this->caseId = CaseFabricator::fabricate([
      'case_type_id' => $caseType['id'],
      'status_id' => 1,
    ])['id'];
  }

}
