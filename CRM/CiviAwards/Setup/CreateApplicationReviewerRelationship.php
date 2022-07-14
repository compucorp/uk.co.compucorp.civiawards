<?php

/**
 * Class CreateApplicationReviewerRelationship.
 */
class CRM_CiviAwards_Setup_CreateApplicationReviewerRelationship {

  /**
   * Adds a new application reviewer relationship.
   */
  public function apply() {
    $this->addApplicationReviewerRelationship();
  }

  /**
   * Adds a new relationship type .
   *
   * The relationship will be used
   * for a “default” reviewer case role for all
   * awards cases when they are created.
   */
  public function addApplicationReviewerRelationship() {
    $labelAtoB = 'Application reviewer of';
    $labelBtoA = 'Has application reviewed by';
    $description = 'Default relationship type for application reviewers';

    $result = civicrm_api3('RelationshipType', 'get', [
      'name_a_b' => $labelAtoB,
      'name_b_a' => $labelBtoA,
    ]);

    if ($result['count'] > 0) {
      return;
    }

    civicrm_api3('RelationshipType', 'create', [
      'name_a_b' => $labelAtoB,
      'label_a_b' => $labelAtoB,
      'name_b_a' => $labelBtoA,
      'label_b_a' => $labelBtoA,
      'description' => $description,
      'is_reserved' => 1,
      'is_active' => 1,
    ]);
  }

}
