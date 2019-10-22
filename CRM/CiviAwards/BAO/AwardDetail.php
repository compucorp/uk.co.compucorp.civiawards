<?php

/**
 * Class CRM_CiviAwards_BAO_AwardDetail.
 */
class CRM_CiviAwards_BAO_AwardDetail extends CRM_CiviAwards_DAO_AwardDetail {

  /**
   * Create a new AwardDetail based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return CRM_CiviAwards_DAO_AwardDetail
   *   AwardDetail instance.
   */
  public static function create(array $params) {
    $entityName = 'AwardDetail';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

}
