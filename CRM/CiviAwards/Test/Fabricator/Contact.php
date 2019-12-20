<?php

class CRM_CiviAwards_Test_Fabricator_Contact {

  private static $defaultParams = [
    'contact_type' => 'Individual',
    'first_name'   => 'John',
    'last_name'    => 'Doe',
    'sequential'   => 1
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);
    $params['display_name'] = "{$params['first_name']} {$params['last_name']}";

    $result = civicrm_api3(
      'Contact',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function fabricateWithEmail($params = [], $email = 'johndoe@test.com') {
    $contact = self::fabricate($params);

    civicrm_api3('Email', 'create', [
      'email' => $email,
      'contact_id' => $contact['id'],
      'is_primary' => 1
    ]);

    return $contact;
  }
}
