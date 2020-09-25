<?php

/**
 * Case type category fabricator.
 */
class CRM_CiviAwards_Test_Fabricator_CaseCategory {

  /**
   * Fabricates a Case Category.
   *
   * @param array $params
   *   Parameters.
   *
   * @return mixed
   *   API result.
   */
  public static function fabricate(array $params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('OptionValue', 'create', $params);

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
    $defaultParams = [
      'option_group_id' => 'case_type_categories',
    ];

    return array_merge($defaultParams, $params);
  }

}
