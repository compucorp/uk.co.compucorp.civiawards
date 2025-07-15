<?php

namespace Civi\Api4;

use Civi\Api4\Action\ApplicantReviewField\GetAction;
use Civi\Api4\Generic\AbstractEntity;
use Civi\Api4\Generic\BasicGetFieldsAction;
use CRM_CiviAwards_ExtensionUtil as E;

/**
 * ApplicantReviewCustomGroup entity.
 *
 * Provided by the CiviAwards extension.
 *
 * @package Civi\Api4
 */
class ApplicantReviewField extends AbstractEntity {

  /**
   * Get Application Review Fields.
   *
   * @param bool $checkPermissions
   *   Should permission be checked.
   *
   * @return \Civi\Api4\Action\ApplicantReviewField\GetAction
   *   Getter result
   */
  public static function get($checkPermissions = TRUE) {
    return (new GetAction(static::getEntityName(), __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  /**
   * {@inheritDoc}
   *
   * @param bool $checkPermissions
   *   Should permission be checked.
   *
   * @return \Civi\Api4\Generic\BasicGetFieldsAction
   *   Supported fields
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new BasicGetFieldsAction(__CLASS__, __FUNCTION__, function () {
      return [
          [
            'name' => 'name',
            'title' => E::ts('Name'),
          ],
      ];
    }))->setCheckPermissions($checkPermissions);
  }

  /**
   * {@inheritDoc}
   */
  public static function permissions() {
    return [
      'meta' => ['access CiviCRM'],
      'get' => ['access CiviCRM'],
    ];
  }

}
