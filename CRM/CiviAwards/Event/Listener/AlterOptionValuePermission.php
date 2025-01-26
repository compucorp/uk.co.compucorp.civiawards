<?php

use Civi\API\Event\AuthorizeEvent;
use Civi\Api4\Generic\AbstractAction;
use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;

/**
 * Allow option value access to users with review permission.
 */
class CRM_CiviAwards_Event_Listener_AlterOptionValuePermission {

  /**
   * Allow option value access to users with review permission.
   *
   * @param \Civi\API\Event\AuthorizeEvent $event
   *   API Prepare Event Object.
   */
  public static function authorize(AuthorizeEvent $event) {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] != 4) {
      return;
    }

    if (!self::shouldRun($apiRequest)) {
      return;
    }

    $hasPermission = CRM_Core_Permission::check(AwardPermission::REVIEW_FIELD_SET_PERM);
    if (!$hasPermission) {
      return;
    }

    $params = CRM_Utils_Request::retrieve('params', 'String');
    if (is_null($params)) {
      return;
    }

    try {
      $params = json_decode($params);
      $formName = $params->formName ?? NULL;
      $event->setAuthorized($formName == 'qf:CRM_CiviAwards_Form_AwardReview');
    }
    catch (\Throwable $th) {
    }
  }

  /**
   * Determines if the processing will run.
   *
   * @param Civi\Api4\Generic\AbstractAction $apiRequest
   *   Api request data.
   *
   * @return bool
   *   TRUE if processing should run, FALSE otherwise.
   */
  protected static function shouldRun(AbstractAction $apiRequest) {
    return $apiRequest['entity'] == 'OptionValue' &&
      $apiRequest['action'] == 'get';
  }

}
