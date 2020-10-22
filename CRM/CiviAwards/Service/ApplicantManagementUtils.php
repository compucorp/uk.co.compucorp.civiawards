<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;
use CRM_CiviAwards_Helper_CustomGroupPostProcess as CustomGroupPostProcessHelper;
use CRM_CiviAwards_Service_ApplicantManagementCustomGroupDisplayFormatter as ApplicantManagementCustomGroupDisplayFormatter;
use CRM_CiviAwards_Service_ApplicantManagementCustomGroupPostProcessor as ApplicantManagementCustomGroupPostProcessor;

/**
 * Applicant management utilities class.
 *
 * This class contains useful methods that helps distinguish an applicant
 * management instance from other instance types.
 */
class CRM_CiviAwards_Service_ApplicantManagementUtils extends CRM_Civicase_Service_CaseCategoryInstanceUtils {

  /**
   * Returns the menu object for the applicant management instance.
   *
   * @return \CRM_Civicase_Service_CaseCategoryMenu
   *   Menu object.
   */
  public function getMenuObject() {
    return new ApplicantManagementMenu();
  }

  /**
   * Returns function to fetch Case category entity types.
   *
   * Returns function to fetch entity types for the case category
   * custom group entity for the category instance. For most case category
   * instances, this function is not necessary so returning NULL is fine here.
   *
   * This function is returned in the format understood by Civicrm for getting
   * entity types for a custom group entity.
   */
  public function getCustomGroupEntityTypesFunction() {
    return 'CRM_CiviAwards_Helper_CaseTypeCategory::getSubTypes;';
  }

  /**
   * {@inheritDoc}
   */
  public function getCustomGroupPostProcessor() {
    return new ApplicantManagementCustomGroupPostProcessor(new CustomGroupPostProcessHelper());
  }

  /**
   * {@inheritDoc}
   */
  public function getCustomGroupDisplayFormatter() {
    return new ApplicantManagementCustomGroupDisplayFormatter(new CustomGroupPostProcessHelper());
  }

  /**
   * The Applicant management instance does not need this object.
   *
   * Post processing for Applicant management is not on the Case type
   * entity, rather it is on the Award Detail entity.
   */
  public function getCaseTypePostProcessor() {
    return NULL;
  }

}
