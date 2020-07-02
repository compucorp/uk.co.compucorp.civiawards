<?php

use CRM_CiviAwards_Service_ApplicantManagementMenu as ApplicantManagementMenu;

/**
 * Class CRM_CiviAwards_Service_ApplicantManagementUtils.
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
