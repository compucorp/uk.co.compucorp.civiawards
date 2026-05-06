<?php

/**
 * Regression tests for the removal of SetCustomGroupSubTypeValues.
 *
 * PR #299 deletes CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues
 * and delegates the responsibility of populating subtype values on the
 * custom group edit form to CiviCase (see civicase PR #1120).
 *
 * These tests verify that:
 *  - The deleted hook class no longer exists.
 *  - civiawards_civicrm_buildForm no longer references it.
 *  - Invoking civiawards_civicrm_buildForm against the custom group edit
 *    form is a no-op for awards subtype handling — the defaults that
 *    civicase (or core) has populated on the form are left intact, so
 *    the form "still loads correct values" for awards custom groups.
 *
 * @group headless
 */
class CRM_CiviAwards_Hook_BuildForm_CustomGroupSubTypeValuesFormTest extends BaseHeadlessTest {

  /**
   * The deleted hook class should no longer exist in the codebase.
   */
  public function testSetCustomGroupSubTypeValuesClassIsRemoved() {
    $this->assertFalse(
      class_exists('CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues'),
      'CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues should be deleted by PR #299.'
    );
  }

  /**
   * Hook should no longer reference the removed class.
   */
  public function testBuildFormHookDoesNotReferenceDeletedClass() {
    $reflection = new ReflectionFunction('civiawards_civicrm_buildForm');
    $source = file_get_contents($reflection->getFileName());
    $this->assertFalse(
      strpos($source, 'CRM_CiviAwards_Hook_BuildForm_SetCustomGroupSubTypeValues'),
      'civiawards.php should not reference the deleted SetCustomGroupSubTypeValues hook.'
    );
  }

  /**
   * Custom group edit form should leave the form's subtype defaults untouched.
   */
  public function testCustomGroupEditFormValuesAreNotAlteredByAwardsHook() {
    $expectedDefaults = [
      'extends' => ['Case', [1, 2, 3]],
      'extends_entity_column_id' => 1,
    ];
    $form = $this->getCustomGroupFormMock($expectedDefaults);

    // The awards build-form hook is expected to be a no-op for the
    // Custom Group form now that SetCustomGroupSubTypeValues has been
    // removed — the CaseCategoryFormBase-derived hooks short-circuit
    // against this form name.
    civiawards_civicrm_buildForm('CRM_Custom_Form_Group', $form);

    $defaults = $form->getVar('_defaults');
    $this->assertEquals($expectedDefaults['extends'], $defaults['extends']);
    $this->assertEquals(
      $expectedDefaults['extends_entity_column_id'],
      $defaults['extends_entity_column_id']
    );
  }

  /**
   * Returns a mocked CRM_Core_Form that exposes $defaults via getVar.
   *
   * @param array $defaults
   *   The _defaults array the form should report.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   A form mock suitable for passing to civiawards_civicrm_buildForm.
   */
  private function getCustomGroupFormMock(array $defaults) {
    $form = $this->getMockBuilder(CRM_Core_Form::class)
      ->disableOriginalConstructor()
      ->setMethods(['getVar', 'setVar', 'getElement', 'add', 'assign'])
      ->getMock();

    $vars = [
      '_defaults' => $defaults,
      '_action' => CRM_Core_Action::UPDATE,
      '_id' => 1,
      // Deliberately NOT 'case_type_categories' so AddFinanceManagementField
      // short-circuits without touching the form.
      '_gName' => NULL,
      '_values' => [],
    ];

    $form->method('getVar')->willReturnCallback(function ($name) use (&$vars) {
      return array_key_exists($name, $vars) ? $vars[$name] : NULL;
    });
    $form->method('setVar')->willReturnCallback(function ($name, $value) use (&$vars) {
      $vars[$name] = $value;
    });

    return $form;
  }

}
