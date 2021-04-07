<?php

use CRM_Civicase_Service_CaseMenu_RouteFactory as RouteFactory;

/**
 * Applicant Management Menu class.
 */
class CRM_CiviAwards_Service_ApplicantManagementMenu extends CRM_Civicase_Service_CaseCategoryMenu {

  /**
   * {@inheritDoc}
   */
  public function getSubRoutes(string $caseTypeCategoryName) {
    return [
      RouteFactory::create('dashboard', $caseTypeCategoryName),
      RouteFactory::create('all', $caseTypeCategoryName)
        ->setOverwrittenProperties([
          'label' => ts('Manage Applications'),
          'name' => "manage_{$caseTypeCategoryName}_applications",
        ]),
      RouteFactory::create('manage_workflows', $caseTypeCategoryName)
        ->setOverwrittenProperties([
          'label' => ts("Manage " . $caseTypeCategoryName),
        ]),
    ];
  }

}
