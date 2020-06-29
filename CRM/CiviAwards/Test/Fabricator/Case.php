<?php

use CRM_CiviAwards_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_CiviAwards_Test_Fabricator_Case.
 */
class CRM_CiviAwards_Test_Fabricator_Case {

  /**
   * Fabricate Case.
   *
   * @param array $params
   *   Parameters.
   *
   * @return mixed
   *   Api result.
   */
  public static function fabricate(array $params = []) {
    $params = self::mergeDefaultParams($params);
    $result = civicrm_api3('Case', 'create', $params);

    return array_shift($result['values']);
  }

  /**
   * Merges default parmeters.
   *
   * @param array $params
   *   Parameters.
   *
   * @return array
   *   Api result.
   */
  private static function mergeDefaultParams(array $params) {
    $contact = ContactFabricator::fabricate();
    $name = uniqid();
    $defaultParams = [
      'subject' => $name,
      'contact_id' => $contact['id'],
    ];

    return array_merge($defaultParams, $params);
  }

  /**
   * Fabricate case with tags.
   *
   * @param array $tags
   *   Case Tags.
   * @param array $caseParams
   *   Case Parameters.
   *
   * @return array
   *   Results.
   */
  public static function fabricateWithTags(array $tags, array $caseParams) {
    $case = self::fabricate($caseParams);
    $tagsData = [];

    foreach ($tags as $tag) {
      $tagEntity = civicrm_api3('Tag', 'create', [
        'name' => $tag,
      ]);
      $tagsData[] = $tagEntity;

      // We need to flush this so that an exception will not be thrown that
      // Tag ID value is not a valid option.
      CRM_Core_PseudoConstant::flush();
      civicrm_api3('EntityTag', 'create', [
        'tag_id' => $tagEntity['id'],
        'entity_table' => 'civicrm_case',
        'entity_id' => $case['id'],
      ]);
    }

    return [
      'case' => $case,
      'tags' => $tagsData,
    ];
  }

}
