<?php

use CRM_CiviAwards_Test_Fabricator_Contact as ContactFabricator;
use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;
use CRM_CiviAwards_Test_Fabricator_Relationship as RelationshipFabricator;

/**
 * @group headless
 */
class CRM_CiviAwards_Service_AwardPanelContactTest extends BaseHeadlessTest {

  public function testGetReturnsContactsBelongingToGroup() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');
    $groupC = $this->createGroup('Group C');

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactB['id']);
    $this->addContactToGroup($groupC, $contactC['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB]
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
      $contactB['id'] => [
        'display_name' => $contactB['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetDoesNotReturnsExcludedGroupContacts() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');
    $groupC = $this->createGroup('Group C');

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactC['id']);
    $this->addContactToGroup($groupB, $contactB['id']);
    $this->addContactToGroup($groupC, $contactB['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB],
        'exclude_groups' => [$groupC]
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    //Contact B should be excluded because contact belongs to Group C also.
    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
      $contactC['id'] => [
        'display_name' => $contactC['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetReturnsContactsBasedOnRelationship() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];

    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    //Contact B is Manager to Contact A
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id']
    ];
    RelationshipFabricator::fabricate($params);

    //Contact B is Regulator to Contact C
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactC['id'],
      'relationship_type_id' => $relationshipTypeB['id']
    ];
    RelationshipFabricator::fabricate($params);

    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => $contactB['id'],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id']
          ],
          [
            'contact_id' => $contactC['id'],
            'is_a_to_b' => 0,
            'relationship_type_id' => $relationshipTypeB['id']
          ],
        ],
      ]
    ];

    // Returned contacts should be ContactA : Since it's manager is contact B
    // Contact B since He is Regulator of ContactC
    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
      $contactB['id'] => [
        'display_name' => $contactB['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetReturnsContactsBasedOnRelationshipAndGroups() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    //Contact B is Manager to Contact A
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id']
    ];
    RelationshipFabricator::fabricate($params);

    $groupC = $this->createGroup('Group C');
    $this->addContactToGroup($groupC, $contactC['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupC],
        'relationship' => [
          [
            'contact_id' => $contactB['id'],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id']
          ],
        ],
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
      $contactC['id'] => [
        'display_name' => $contactC['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetReturnsContactsBelongingToGroupWhenContactFilterIsPassed() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactB['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB]
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    // ContactA and ContactB is supposed to be returned but since we are filtering
    // By contactB, only contactB is returned
    $contacts = $awardPanelContact->get($awardPanel->id, [$contactB['id']]);

    $expectedResult = [
      $contactB['id'] => [
        'display_name' => $contactB['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetReturnsContactsBasedOnRelationshipWhenContactFilterIsPassed() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];

    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    //Contact B is Manager to Contact A
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id']
    ];
    RelationshipFabricator::fabricate($params);

    //Contact B is Regulator to Contact C
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactC['id'],
      'relationship_type_id' => $relationshipTypeB['id']
    ];
    RelationshipFabricator::fabricate($params);

    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => $contactB['id'],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id']
          ],
          [
            'contact_id' => $contactC['id'],
            'is_a_to_b' => 0,
            'relationship_type_id' => $relationshipTypeB['id']
          ],
        ],
      ]
    ];

    // Returned contacts should be ContactA and ContactA but since we are filtering
    // By contactA, only contactA is returned
    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id, [$contactA['id']]);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetDoesNotReturnDuplicateContactForContactBelongingToDifferentGroup() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactA['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB]
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ]
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  public function testGetDoesNotReturnDuplicateContactForContactInvolvedInMoreThanOneRelationship() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $relationshipTypeBParams = [
      'name_a_b' => 'Regulator is',
      'name_b_a' => 'Regulator',
    ];

    $relationshipTypeB = RelationshipTypeFabricator::fabricate($relationshipTypeBParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    //Contact B is Manager to Contact A
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id']
    ];
    RelationshipFabricator::fabricate($params);

    //Contact C is Regulator to Contact A
    $params = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeB['id']
    ];
    RelationshipFabricator::fabricate($params);

    //Both parameter condition should return Contact A
    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => $contactB['id'],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id']
          ],
          [
            'contact_id' => $contactC['id'],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeB['id']
          ],
        ],
      ]
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }
  private function addContactToGroup($groupId, $contactId) {
    civicrm_api3('GroupContact', 'create', [
      'group_id' => $groupId,
      'contact_id' => $contactId,
    ]);
  }

  private function createGroup($title) {
    $result = civicrm_api3('Group', 'create', [
      'title' => $title,
    ]);

    return $result['id'];
  }

  /**
   * The return result from the Contact.get API is not reliable
   * based on the parameters it returns even if the specific parameters
   * are passed in the return array.
   * This function makes sure only the keys we are concerned with are present.
   *
   * @param array $contacts
   */
  private function cleanupUnwantedKeys(&$contacts) {
    $expectedKeys = ['email' => 0, 'display_name' => 1];
    foreach ($contacts as &$contact) {
      $contact = array_intersect_key($contact, $expectedKeys);
    }

  }
}
