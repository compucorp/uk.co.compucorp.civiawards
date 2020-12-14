<?php

/**
 * Base class for the CaseCategoryForm hook classes.
 *
 * This is implemented by hook classes responding to changes
 * on the case category form page. It defines common functions
 * that can be shared by the hook classes.
 */
class CRM_CiviAwards_Hook_CaseCategoryFormBase {

  /**
   * Determines the condition for the hook to run.
   *
   * The hook will run when the option value for the case type category
   * option group is being created or edited.
   *
   * @param CRM_Core_Form $form
   *   Form object class.
   * @param string $formName
   *   Form name.
   *
   * @return bool
   *   True if the hook class should run.
   */
  protected function shouldRun(CRM_Core_Form $form, $formName) {
    $optionGroupName = $form->getVar('_gName');
    return $formName == CRM_Admin_Form_Options::class && $optionGroupName == 'case_type_categories';
  }

}
