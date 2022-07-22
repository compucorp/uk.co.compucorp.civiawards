<?php

use CRM_CiviAwards_Test_Fabricator_Case as CaseFabricator;
use CRM_CiviAwards_Test_Fabricator_Contact as ContactFabricator;
use CRM_CiviAwards_Service_AwardPanelContact as AwardPanelContact;
use CRM_CiviAwards_Test_Fabricator_CaseType as CaseTypeFabricator;
use CRM_CiviAwards_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_CiviAwards_Test_Fabricator_AwardReviewPanel as AwardReviewPanelFabricator;
use CRM_CiviAwards_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;

/**
 * Class to test AwardPanelContact service.
 *
 * @group headless
 */
class CRM_CiviAwards_Service_AwardPanelContactTest extends BaseHeadlessTest {

  /**
   * Test Get Returns Contacts Belonging To Group.
   */
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
        'include_groups' => [$groupA, $groupB],
      ],
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
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get Does Not Return Excluded Group Contacts.
   */
  public function testGetDoesNotReturnExcludedGroupContacts() {
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
        'exclude_groups' => [$groupC],
      ],
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id);

    // Contact B should be excluded because contact belongs to Group C also.
    $expectedResult = [
      $contactA['id'] => [
        'display_name' => $contactA['display_name'],
        'email' => '',
      ],
      $contactC['id'] => [
        'display_name' => $contactC['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get Returns Contacts Based On Relationship.
   */
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
    $contactD = ContactFabricator::fabricate();

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B is Manager to Contact D.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactD['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B is Regulator to Contact C.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactC['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
    ];
    RelationshipFabricator::fabricate($params);

    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => [$contactB['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id'],
          ],
          [
            'contact_id' => [$contactC['id']],
            'is_a_to_b' => 0,
            'relationship_type_id' => $relationshipTypeB['id'],
          ],
        ],
      ],
    ];

    // Returned contacts should be ContactA : Since it's manager is contact B.
    // Contact B is regulator of Contact C and Contact D: manager is Contact B.
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
      ],
      $contactD['id'] => [
        'display_name' => $contactB['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get Returns Contacts Based On Relationship And Groups.
   */
  public function testGetReturnsContactsBasedOnRelationshipAndGroups() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    $groupC = $this->createGroup('Group C');
    $this->addContactToGroup($groupC, $contactC['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupC],
        'relationship' => [
          [
            'contact_id' => [$contactB['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id'],
          ],
        ],
      ],
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
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get Returns Contacts Belonging To Group When Contact Filter Is Passed.
   */
  public function testGetReturnsContactsBelongingToGroupWhenContactFilterIsPassed() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');

    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactB['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB],
      ],
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    // ContactA and ContactB is supposed to be returned but since we are
    // filtering By contactB, only contactB is returned.
    $contacts = $awardPanelContact->get($awardPanel->id, [$contactB['id']]);

    $expectedResult = [
      $contactB['id'] => [
        'display_name' => $contactB['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get Returns Contacts Based On Relationship When Filter Is Passed.
   */
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

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B is Regulator to Contact C.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactC['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
    ];
    RelationshipFabricator::fabricate($params);

    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => [$contactB['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id'],
          ],
          [
            'contact_id' => [$contactC['id']],
            'is_a_to_b' => 0,
            'relationship_type_id' => $relationshipTypeB['id'],
          ],
        ],
      ],
    ];

    // Returned contacts should be ContactA and ContactA but since we are
    // filtering By contactA, only contactA is returned.
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

  /**
   * Test Get Does Not Return Duplicate Contact.
   */
  public function testGetDoesNotReturnDuplicateContactForContactBelongingToDifferentGroup() {
    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');

    $contactA = ContactFabricator::fabricate();
    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupB, $contactA['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA, $groupB],
      ],
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

  /**
   * Test Get Does Not Return Duplicate Contact For More Than One Relationships.
   */
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

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact C is Regulator to Contact A.
    $params = [
      'contact_id_b' => $contactC['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeB['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Both parameter condition should return Contact A.
    $params = [
      'contact_settings' => [
        'relationship' => [
          [
            'contact_id' => [$contactB['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id'],
          ],
          [
            'contact_id' => [$contactC['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeB['id'],
          ],
        ],
      ],
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

  /**
   * Test Get Does Not Return Excluded Group Contact.
   *
   * Excluded group contact is not returned even when contact belongs
   * to an included group or is part of a relationship condition.
   */
  public function testGetDoesNotReturnExcludedGroupContactWhenContactBelongsToGroupAndRelationShip() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];

    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();
    $contactC = ContactFabricator::fabricate();

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    $groupA = $this->createGroup('Group A');
    $groupB = $this->createGroup('Group B');

    $this->addContactToGroup($groupA, $contactA['id']);
    $this->addContactToGroup($groupA, $contactC['id']);
    $this->addContactToGroup($groupB, $contactA['id']);

    $params = [
      'contact_settings' => [
        'include_groups' => [$groupA],
        'exclude_groups' => [$groupB],

      ],
      'relationship' => [
        [
          'contact_id' => [$contactB['id']],
          'is_a_to_b' => 1,
          'relationship_type_id' => $relationshipTypeA['id'],
        ],
      ],
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();

    // Contact A will be returned in relationship because contact B is manger
    // Contact A will be returned in Groups because it belongs to group A
    // But because groupB is excluded and contact A belongs to group B, only
    // Contact C is returned.
    $contacts = $awardPanelContact->get($awardPanel->id);

    $expectedResult = [
      $contactC['id'] => [
        'display_name' => $contactC['display_name'],
        'email' => '',
      ],
    ];
    $this->cleanupUnwantedKeys($contacts);

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get returns contacts assigned To Role.
   */
  public function testGetReturnsContactsAssignedToRole() {
    [$role, $caseType, $awardPanel] = $this->setupAwardPanel();

    $client = ContactFabricator::fabricate();
    $reviewer = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $client['id'],
    ]);
    $this->assignRoleToCase($client['id'], $reviewer['id'], $case['id'], $role);

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel, [$reviewer['id']]);

    $expectedResult = [
      $reviewer['id'] => [
        'id' => $reviewer['id'],
        'display_name' => $reviewer['display_name'],
        'email' => NULL,
        'case_ids' => [$case['id']],
      ],
    ];

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get returns contacts for award panel with multiple roles.
   */
  public function testGetReturnsContactsAssignedToRoleWhenPanelHasMultipleRoleConfiguration() {
    // Create a new role: reviewer.
    $roleAParams = ['name_a_b' => 'Reviewer is', 'name_b_a' => 'Reviewer'];
    $role = RelationshipTypeFabricator::fabricate($roleAParams);

    // Create a second role: manager.
    $roleBParams = ['name_a_b' => 'Manager is', 'name_b_a' => 'Manager'];
    $roleB = RelationshipTypeFabricator::fabricate($roleBParams);

    $caseType = CaseTypeFabricator::fabricate();
    // Create a new review panel: that grants access to reviewer and manager.
    $params = [
      'case_type_id' => $caseType['id'],
      'contact_settings' => [
        'case_roles' => [$role['name_b_a'], $roleB['name_b_a']],
      ],
    ];
    $awardPanel = AwardReviewPanelFabricator::fabricate($params);

    $role = $role['id'];
    $caseType = $caseType['id'];
    $awardPanel = $awardPanel->id;

    $client = ContactFabricator::fabricate();
    $reviewer = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $client['id'],
    ]);
    $this->assignRoleToCase($client['id'], $reviewer['id'], $case['id'], $role);

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel, [$reviewer['id']]);

    $expectedResult = [
      $reviewer['id'] => [
        'id' => $reviewer['id'],
        'display_name' => $reviewer['display_name'],
        'email' => NULL,
        'case_ids' => [$case['id']],
      ],
    ];

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get will not returns role contacts if relationship has not started.
   */
  public function testGetWillNotReturnContactsAssignedToRoleWhenRelationshipHasNotStarted() {
    // Create a new role: reviewer.
    [$role, $caseType, $awardPanel] = $this->setupAwardPanel();

    $client = ContactFabricator::fabricate();
    $reviewer = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $client['id'],
    ]);
    $this->assignRoleToCase(
      $client['id'],
      $reviewer['id'],
      $case['id'],
      $role,
      ['start_date' => date("Y-m-d", strtotime("tomorrow"))]
    );

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel, [$reviewer['id']]);

    $expectedResult = [];

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get will not returns role contacts if relationship has ended.
   */
  public function testGetWillNotReturnContactsAssignedToRoleWhenRelationshipHasEnded() {
    // Create a new role: reviewer.
    [$role, $caseType, $awardPanel] = $this->setupAwardPanel();

    $client = ContactFabricator::fabricate();
    $reviewer = ContactFabricator::fabricate();

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $client['id'],
    ]);
    $this->assignRoleToCase(
      $client['id'],
      $reviewer['id'],
      $case['id'],
      $role,
      ['end_date' => date("Y-m-d", strtotime("yesterday"))]
    );

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel, [$reviewer['id']]);

    $expectedResult = [];

    $this->assertEquals($expectedResult, $contacts);
  }

  /**
   * Test Get returns only allowed cases when permission is granted by role.
   */
  public function testGetReturnsOnlyAllowedCaseIdsWhenPermissionIsGrnatedByRole() {
    [$role, $caseType, $awardPanel] = $this->setupAwardPanel();

    $clientA = ContactFabricator::fabricate();
    $clientB = ContactFabricator::fabricate();
    $clientC = ContactFabricator::fabricate();
    $reviewer = ContactFabricator::fabricate();

    $caseA = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $clientA['id'],
    ]);
    $caseB = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $clientB['id'],
    ]);
    $caseC = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType,
      'contact_id' => $clientC['id'],
    ]);
    $this->assignRoleToCase($clientA['id'], $reviewer['id'], $caseA['id'], $role);
    $this->assignRoleToCase($clientB['id'], $reviewer['id'], $caseB['id'], $role);

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel, [$reviewer['id']]);

    $expectedResult = [$caseA['id'], $caseB['id']];

    $this->assertEquals($expectedResult, ...array_column($contacts, 'case_ids'));
  }

  /**
   * Test Get when permission is granted by relationship and role.
   *
   * I.e. permission granted by relationship superceeds case_role since
   * relationship is not limited to specific case(s).
   */
  public function testGetAllowsAllCasesWhenPermissionIsGrantedByRelationshipAndRole() {
    $relationshipTypeAParams = [
      'name_a_b' => 'Manager is',
      'name_b_a' => 'Manager',
    ];
    $relationshipTypeA = RelationshipTypeFabricator::fabricate($relationshipTypeAParams);

    $roleParams = [
      'name_a_b' => 'Reviewer is',
      'name_b_a' => 'Reviewer',
    ];
    $role = RelationshipTypeFabricator::fabricate($roleParams);

    $caseType = CaseTypeFabricator::fabricate();
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();

    // Contact B is Manager to Contact A.
    $params = [
      'contact_id_b' => $contactB['id'],
      'contact_id_a' => $contactA['id'],
      'relationship_type_id' => $relationshipTypeA['id'],
    ];
    RelationshipFabricator::fabricate($params);

    $params = [
      'case_type_id' => $caseType['id'],
      'contact_settings' => [
        'case_roles' => [$role['name_b_a']],
        'relationship' => [
          [
            'contact_id' => [$contactB['id']],
            'is_a_to_b' => 1,
            'relationship_type_id' => $relationshipTypeA['id'],
          ],
        ],
      ],
    ];

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType['id'],
      'contact_id' => $contactB['id'],
    ]);
    $this->assignRoleToCase($contactB['id'], $contactA['id'], $case['id'], $role['id']);

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);
    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id, [$contactA['id']]);

    $expectedContactIds = [$contactA['id']];

    $this->assertEquals($expectedContactIds, array_column($contacts, 'id'));
    // case_ids is empty, i.e. all case can be reviewed by the contact.
    $this->assertCount(0, array_column($contacts, 'case_ids'));
  }

  /**
   * Test Get allows all cases when permission is granted by group and role.
   *
   * I.e. permission granted by grouup superceeds case_role since
   * group is not limited to specific case(s).
   */
  public function testGetAllowsAllCasesWhenPermissionIsGrantedByGroupAndRole() {
    $roleParams = [
      'name_a_b' => 'Reviewer is',
      'name_b_a' => 'Reviewer',
    ];
    $role = RelationshipTypeFabricator::fabricate($roleParams);

    $caseType = CaseTypeFabricator::fabricate();
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();

    $groupA = $this->createGroup('Group A');
    $this->addContactToGroup($groupA, $contactA['id']);

    $case = CaseFabricator::fabricate([
      'status_id' => 1,
      'case_type_id' => $caseType['id'],
      'contact_id' => $contactB['id'],
    ]);
    $this->assignRoleToCase($contactB['id'], $contactA['id'], $case['id'], $role['id']);

    $params = [
      'case_type_id' => $caseType['id'],
      'contact_settings' => [
        'case_roles' => [$role['name_b_a']],
        'include_groups' => [$groupA],
      ],
    ];

    $awardPanel = AwardReviewPanelFabricator::fabricate($params);

    $awardPanelContact = new AwardPanelContact();
    $contacts = $awardPanelContact->get($awardPanel->id, [$contactA['id']]);

    $expectedContactIds = [$contactA['id']];

    $this->assertEquals($expectedContactIds, array_column($contacts, 'id'));
    // case_ids is empty, i.e. all case can be reviewed by the contact.
    $this->assertCount(0, array_column($contacts, 'case_ids'));
  }

  /**
   * Add contact to group.
   *
   * @param int $groupId
   *   Group ID.
   * @param int $contactId
   *   Contact ID.
   */
  private function addContactToGroup($groupId, $contactId) {
    civicrm_api3('GroupContact', 'create', [
      'group_id' => $groupId,
      'contact_id' => $contactId,
    ]);
  }

  /**
   * Create group.
   *
   * @param string $title
   *   Group title.
   *
   * @return int
   *   Group Id.
   */
  private function createGroup($title) {
    $result = civicrm_api3('Group', 'create', [
      'title' => $title,
    ]);

    return $result['id'];
  }

  /**
   * Cleanup unwanted keys.
   *
   * The return result from the Contact.get API is not reliable
   * based on the parameters it returns even if the specific parameters
   * are passed in the return array.
   * This function makes sure only the keys we are concerned with are present.
   *
   * @param array $contacts
   *   Contacts.
   */
  private function cleanupUnwantedKeys(array &$contacts) {
    $expectedKeys = ['email' => 0, 'display_name' => 1];
    foreach ($contacts as &$contact) {
      $contact = array_intersect_key($contact, $expectedKeys);
    }

  }

  /**
   * Assigns a role to case.
   *
   * @param int $contactIdA
   *   The contact A ID.
   * @param int $contactIdB
   *   The contact B ID.
   * @param int $caseId
   *   The case ID.
   * @param int $relationshipTypeId
   *   The relationship type ID.
   * @param array $extraParams
   *   Extra relationship data.
   */
  private function assignRoleToCase($contactIdA, $contactIdB, $caseId, $relationshipTypeId, array $extraParams = []) {
    $params = [
      'is_active' => 1,
      'contact_id_a' => $contactIdA,
      'contact_id_b' => $contactIdB,
      'relationship_type_id' => $relationshipTypeId,
    ];
    $relationship = RelationshipFabricator::fabricate($params);
    civicrm_api3('Relationship', 'create', array_merge([
      'id' => $relationship['id'],
      'case_id' => $caseId,
    ], $extraParams));
  }

  /**
   * Sets up award panel data, with role and case type.
   */
  public function setupAwardPanel() {
    // Create a new role: reviewer.
    $roleParams = ['name_a_b' => 'Reviewer is', 'name_b_a' => 'Reviewer'];
    $role = RelationshipTypeFabricator::fabricate($roleParams);

    $caseType = CaseTypeFabricator::fabricate();
    // Create a new review panel: that grants access to the reviewer role.
    $params = [
      'case_type_id' => $caseType['id'],
      'contact_settings' => [
        'case_roles' => [$role['name_b_a']],
      ],
    ];
    $awardPanel = AwardReviewPanelFabricator::fabricate($params);

    return [$role['id'], $caseType['id'], $awardPanel->id];
  }

}
