<?php

/**
 * Manages award review panel entity.
 */
class CRM_CiviAwards_BAO_AwardReviewPanel extends CRM_CiviAwards_DAO_AwardReviewPanel {

  /**
   * Create a new AwardReviewPanel based on array-data.
   *
   * @param array $params
   *   Key-value pairs.
   *
   * @return \CRM_CiviAwards_DAO_AwardReviewPanel
   *   AwardReviewPanel instance
   */
  public static function create(array $params) {
    $entityName = 'AwardReviewPanel';
    $hook = empty($params['id']) ? 'create' : 'edit';
    self::validateParams($params);

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    self::serializeSettingsParams($params);
    $instance = new self();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * Serializes the values of the contact_settings and visibility_settings.
   *
   * @param array $params
   *   Parameters.
   */
  private static function serializeSettingsParams(array &$params) {
    $settingsParams = ['contact_settings', 'visibility_settings'];
    foreach ($settingsParams as $setting) {
      if (isset($params[$setting])) {
        $params[$setting] = serialize($params[$setting]);
      }
    }
  }

  /**
   * Validation function.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateParams(array $params) {
    self::validateVisibilitySettings($params);
    self::validateContactSettings($params);
  }

  /**
   * Validates the visibility_settings fields.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateVisibilitySettings(array $params) {
    if (empty($params['visibility_settings'])) {
      return;
    }
    $fieldConfig = [
      'anonymize_application' => [
        'is_required' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0, 'max_range' => 1],
      ],
      'application_tags' => [
        'is_array' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
      ],
      'application_status' => [
        'is_array' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
      ],
      'is_application_status_restricted' => [
        'is_required' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
        'options' => ['min_range' => 0, 'max_range' => 1],
      ],
      'restricted_application_status' => [
        'is_array' => TRUE,
        'is_required' => !empty($params['visibility_settings']['is_application_status_restricted']) ? TRUE : FALSE,
        'validate_filter' => FILTER_VALIDATE_INT,
      ],
    ];

    self::validateSettingFields($fieldConfig, $params['visibility_settings'], 'Visibility Settings');
  }

  /**
   * Validates the contact_settings fields.
   *
   * @param array $params
   *   Parameters.
   */
  private static function validateContactSettings(array $params) {
    if (empty($params['contact_settings'])) {
      return;
    }

    $fieldConfig = [
      'exclude_groups' => [
        'is_array' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
      ],
      'include_groups' => [
        'is_array' => TRUE,
        'validate_filter' => FILTER_VALIDATE_INT,
      ],
      'relationship' => [
        'is_array' => TRUE,
        'fields' => [
          'contact_id' => [
            'is_array' => TRUE,
            'validate_filter' => FILTER_VALIDATE_INT,
          ],
          'relationship_type_id' => ['validate_filter' => FILTER_VALIDATE_INT],
          'is_a_to_b' => [
            'validate_filter' => FILTER_VALIDATE_INT,
            'options' => ['min_range' => 0, 'max_range' => 1],
          ],
        ],
      ],
    ];

    self::validateSettingFields($fieldConfig, $params['contact_settings'], 'Contact Settings');

    // Multi dimensional array fields validation.
    foreach ($fieldConfig as $fieldName => $settings) {
      if (empty($settings['fields']) || empty($params['contact_settings'][$fieldName])) {
        continue;
      }

      foreach ($params['contact_settings'][$fieldName] as $fieldValue) {
        foreach ($fieldValue as $field => $value) {
          if (isset($fieldConfig[$fieldName]['fields'][$field])) {
            self::validateSettingFields(
              [$field => $fieldConfig[$fieldName]['fields'][$field]],
              [$field => $value],
              $fieldName
            );
          }
          else {
            throw new Exception("Illegal field ({$field}) found in {$fieldName} settings");
          }
        }
      }
    }
  }

  /**
   * Validates Settings fields for the visiblity and setting fields.
   *
   * @param array $config
   *   Field Config settings.
   * @param array $params
   *   Parameters.
   * @param string $settingName
   *   The setting name.
   */
  private static function validateSettingFields(array $config, array $params, $settingName) {
    $validFields = array_keys($config);
    $paramFields = array_keys($params);
    $illegalFields = array_diff($paramFields, $validFields);
    if (!empty($illegalFields)) {
      $illegalFields = implode(',', $illegalFields);
      throw new Exception("Illegal fields: ({$illegalFields}) found in {$settingName}");
    }

    foreach ($config as $fieldName => $setting) {
      if (empty($setting['is_required']) && !isset($params[$fieldName])) {
        continue;
      }
      if (!empty($setting['is_required']) &&
        (!isset($params[$fieldName]) || (is_array(($params[$fieldName])) && empty(($params[$fieldName]))))) {
        throw new Exception(ts("{$settingName}: {$fieldName} is required"));
      }

      if (!empty($setting['is_array'])) {
        if (!is_array($params[$fieldName])) {
          throw new Exception(ts("{$settingName}: {$fieldName} should be an array"));
        }

        if (!empty($setting['validate_filter'])) {
          $options = isset($setting['options']) ? ['options' => $setting['options']] : NULL;
          foreach ($params[$fieldName] as $value) {
            if (filter_var($value, $setting['validate_filter'], $options) === FALSE) {
              throw new Exception("{$settingName}: One of the values of {$fieldName} is not in a valid format");
            }
          }
        }
      }

      if (empty($setting['is_array']) && !empty($setting['validate_filter'])) {
        $options = isset($setting['options']) ? ['options' => $setting['options']] : NULL;
        if (filter_var($params[$fieldName], $setting['validate_filter'], $options) === FALSE) {
          throw new Exception("{$settingName}: {$fieldName} is not in a valid format");
        }
      }
    }
  }

}
