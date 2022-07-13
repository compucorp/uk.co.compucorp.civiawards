<?php

use CRM_CiviAwards_Setup_CreateApplicationReviewerRelationship as CreateApplicationReviewerRelationship;

/**
 * Creates the application reviewer relatioinship.
 */
class CRM_CiviAwards_Upgrader_Steps_Step1012 {

  /**
   * Creates the application reviewer relatioinship.
   *
   * @return bool
   *   return value.
   */
  public function apply() {
    (new CreateApplicationReviewerRelationship())->apply();

    return TRUE;
  }

}
