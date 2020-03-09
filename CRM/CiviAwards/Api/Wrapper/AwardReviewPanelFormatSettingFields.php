<?php

/**
 * Class CRM_CiviAwards_Api_Wrapper_AwardReviewPanelFormatSettingFields.
 */
class CRM_CiviAwards_Api_Wrapper_AwardReviewPanelFormatSettingFields implements API_Wrapper {

  /**
   * {@inheritdoc}
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    if ($this->canHandleTheRequest($apiRequest)) {
      $this->unserializeFilterValues($result['values']);
    }
    return $result;
  }

  /**
   * Checks whether the request can be handled or not.
   *
   * @param array $apiRequest
   *   API Request.
   *
   * @return bool
   *   If request can be handled.
   */
  private function canHandleTheRequest(array $apiRequest) {
    return $apiRequest['entity'] === 'AwardReviewPanel' &&
      in_array($apiRequest['action'], ['get', 'create']);
  }

  /**
   * Unserializes Setting fields.
   *
   * The setting fields (contact_settings, visibility_settings) are stored
   * in the database as serialized values. This function basically
   * unserializes the values for these fields.
   *
   * @param array $result
   *   API results.
   */
  private function unserializeFilterValues(array &$result) {
    $filterFields = ['contact_settings', 'visibility_settings'];
    foreach ($result as $key => $value) {
      foreach ($filterFields as $field) {
        if (isset($value[$field])) {
          $result[$key][$field] = unserialize($value[$field]);
        }
      }
    }
  }

}
