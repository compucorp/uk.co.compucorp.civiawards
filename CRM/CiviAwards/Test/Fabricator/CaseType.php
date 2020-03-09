<?php

class CRM_CiviAwards_Test_Fabricator_CaseType {

  public static function fabricate($params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('CaseType', 'create', $params);

    return array_shift($result['values']);
  }

  private static function mergeDefaultParams($params) {
    $name = uniqid();
    $defaultParams = [
      'name' => $name,
      'title' => $name,
      'is_active' => 1,
    ];

    return array_merge($defaultParams, $params);
  }
}
