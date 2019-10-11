<?php

/**
 * Class CRM_CiviAwards_Helper_CaseTypeCategory.
 */
class CRM_CiviAwards_Helper_CaseTypeCategory {

  const AWARDS_CASE_TYPE_CATEGORY_NAME = 'awards';

  /**
   * Returns the case types for the awards category.
   *
   * @return array
   *   Array of Case Types indexed by Id.
   */
  public static function getAwardCaseTypes() {
    $result = civicrm_api3('CaseType', 'get', [
      'return' => ['title', 'id'],
      'case_type_category' => self::AWARDS_CASE_TYPE_CATEGORY_NAME,
    ]);

    return array_column($result['values'], 'title', 'id');
  }

}
