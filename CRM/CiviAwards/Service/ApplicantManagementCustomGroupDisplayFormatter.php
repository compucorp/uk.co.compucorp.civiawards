<?php

use CRM_Civicase_Service_BaseCustomGroupDisplayFormatter as BaseCustomGroupDisplayFormatter;
use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;

/**
 * Case Management Instance Custom Group page formatter.
 */
class CRM_CiviAwards_Service_ApplicantManagementCustomGroupDisplayFormatter extends BaseCustomGroupDisplayFormatter {

  /**
   * Stores the CaseManagement Post process helper.
   *
   * @var \CRM_CiviAwards_Helper_CustomGroupPostProcess
   */
  private $postProcessHelper;

  /**
   * Stores case type categories.
   *
   * @var array
   */
  private $caseTypeCategories;

  /**
   * Stores option values for  `cg_extends` option group.
   *
   * @var array
   */
  private $cgExtendValues;

  /**
   * Stores custom group subtypes.
   *
   * @var array
   */
  private $customGroupSubTypes;

  /**
   * Constructor function.
   *
   * @param \CRM_CiviAwards_Helper_CustomGroupPostProcess $postProcessHelper
   *   Post process helper class.
   */
  public function __construct(CustomGroupPostProcessHelper $postProcessHelper) {
    $this->postProcessHelper = $postProcessHelper;
    $this->caseTypeCategories = $postProcessHelper->getCaseTypeCategories();
    $this->cgExtendValues = $postProcessHelper->getCgExtendValues();
    $this->customGroupSubTypes = $postProcessHelper->getCustomGroupSubTypesList();
  }

  /**
   * Sets the correct label for custom group category on listing page.
   *
   * @param array $row
   *   One of the rows of the custom group listing page.
   */
  public function processDisplay(array &$row) {
    $row['extends_display'] = $this->cgExtendValues[$this->caseTypeCategories[$row['extends_entity_column_id']]];
    if (empty($this->customGroupSubTypes[$row['id']])) {
      return;
    }

    $customGroupSubTypesList = $this->customGroupSubTypes[$row['id']];
    $subTypesList = $this->postProcessHelper->getAwardSubTypes();
    $relevantSubTypes = implode(', ', array_intersect_key($subTypesList, array_flip($customGroupSubTypesList)));
    $row['extends_entity_column_value'] = $relevantSubTypes;
  }

}
