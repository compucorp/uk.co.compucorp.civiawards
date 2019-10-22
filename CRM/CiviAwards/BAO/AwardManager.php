<?php

/**
 * Class CRM_CiviAwards_BAO_AwardManager.
 */
class CRM_CiviAwards_BAO_AwardManager extends CRM_CiviAwards_DAO_AwardManager {

  /**
   * Create a new AwardManager based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return CRM_CiviAwards_DAO_AwardManager
   *   AwardManager instance
   */
  public static function create(array $params) {
    $className = 'CRM_CiviAwards_DAO_AwardManager';
    $entityName = 'AwardManager';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
