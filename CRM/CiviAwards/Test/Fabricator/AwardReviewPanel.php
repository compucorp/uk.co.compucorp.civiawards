<?php

use CRM_CiviAwards_BAO_AwardReviewPanel as AwardReviewPanel;

class CRM_CiviAwards_Test_Fabricator_AwardReviewPanel {

  public static function fabricate($params = []) {
    $params = self::mergeDefaultParams($params);

    return AwardReviewPanel::create($params);
  }

  private static function mergeDefaultParams($params) {
    $defaultParams = [
      'title' => uniqid(),
      'case_type_id' => 1,
      'is_active' => 1,
    ];

    return array_merge($defaultParams, $params);
  }

}
