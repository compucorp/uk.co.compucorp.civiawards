<?php

use Civi\API\Event\AuthorizeEvent;
use Civi\API\Event\RespondEvent;
use Civi\API\Event\Event;
use Civi\Api4\Generic\Result;
use CRM_CiviAwards_Hook_AlterAPIPermissions_Award as AwardPermission;

/**
 * Allow custom group access to users with review permission.
 */
class CRM_CiviAwards_Event_Listener_AlterCustomGroupPermission {

  /**
   * Allow custom group access to users with review permission.
   *
   * @param Civi\API\Event\AuthorizeEvent $event
   *   API Authorize Event Object.
   */
  public static function authorize(AuthorizeEvent $event) {
    if (!self::shouldRun($event)) {
      return;
    }

    $event->setAuthorized(TRUE);
  }

  /**
   * Restrict to review custom groups for users with review permission.
   *
   * @param Civi\API\Event\RespondEvent $event
   *   API Respond Event Object.
   */
  public static function respond(RespondEvent $event) {
    if (!self::shouldRun($event)) {
      return;
    }

    $reviewCustomGroupIds = self::getReviewCustomGroupIds();
    $result = $event->getResponse();
    $apiRequest = $event->getApiRequest();

    foreach ($result as $customGroup) {
      $fieldName = $apiRequest['entity'] === 'CustomGroup' ? 'id' : 'custom_group_id';

      if (empty($customGroup[$fieldName]) || !in_array($customGroup[$fieldName], $reviewCustomGroupIds)) {
        $event->setResponse(new Result());
        break;
      }
    }
  }

  /**
   * Determines if the processing will run.
   *
   * @param Civi\API\Event\Event $event
   *   Api request data.
   *
   * @return bool
   *   TRUE if processing should run, FALSE otherwise.
   */
  protected static function shouldRun(Event $event): bool {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest['version'] != 4) {
      return FALSE;
    }

    if (!CRM_Core_Permission::check(AwardPermission::REVIEW_FIELD_SET_PERM) ||
      (CRM_Core_Permission::check('access all custom data') && CRM_Core_Permission::check('administer CiviCRM'))) {
      return FALSE;
    }

    return in_array($apiRequest['entity'], ['CustomGroup', 'CustomField']) && $apiRequest['action'] === 'get';
  }

  /**
   * Get Ids of review custom group.
   *
   * @return array
   *   Ids of review custom group.
   */
  private static function getReviewCustomGroupIds(): array {
    $reviewCustomGroups = [];
    $reviewActivityType = civicrm_api3('OptionValue', 'get', [
      'return' => ['value'],
      'sequential' => 1,
      'option_group_id' => 'activity_type',
      'name' => CRM_CiviAwards_Setup_CreateApplicantReviewActivityType::APPLICANT_REVIEW,
    ]);

    if (empty($reviewActivityType['values'][0]['value'])) {
      return $reviewCustomGroups;
    }

    $reviewActivityTypeId = $reviewActivityType['values'][0]['value'];
    $result = civicrm_api3('CustomGroup', 'get', [
      'return' => ['id'],
      'sequential' => 1,
      'extends' => 'Activity',
      'extends_entity_column_value' => ['LIKE' => '%' . CRM_Core_DAO::VALUE_SEPARATOR . $reviewActivityTypeId . CRM_Core_DAO::VALUE_SEPARATOR . '%'],
    ]);

    if (!empty($result['values'])) {
      $reviewCustomGroups = array_column($result['values'], 'id');
    }

    return $reviewCustomGroups;
  }

}
