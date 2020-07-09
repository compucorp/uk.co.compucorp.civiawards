<?php

/**
 * Class CRM_CiviAwards_Test_Fabricator_CaseType.
 */
class CRM_CiviAwards_Test_Fabricator_CaseType {

  /**
   * Fabricates a Case Type.
   *
   * @param array $params
   *   Parameters.
   *
   * @return mixed
   *   API result.
   */
  public static function fabricate(array $params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('CaseType', 'create', $params);

    return array_shift($result['values']);
  }

  /**
   * Merges default parameters.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   API result.
   */
  private static function mergeDefaultParams(array $params) {
    $name = uniqid();
    $defaultParams = [
      'name' => $name,
      'title' => $name,
      'is_active' => 1,
      'definition' => ['activitySets' => self::getActivitySets()],
    ];

    return array_merge($defaultParams, $params);
  }

  /**
   * Get Activity sets.
   *
   * @return array
   *   Results.
   */
  private static function getActivitySets() {
    return [
      [
        'timeline' => TRUE,
        'name' => 'standard_timeline',
        'label' => 'Standard Timeline',
      ],
    ];
  }

}
