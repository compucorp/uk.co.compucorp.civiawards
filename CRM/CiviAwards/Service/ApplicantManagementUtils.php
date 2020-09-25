<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;

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

}
