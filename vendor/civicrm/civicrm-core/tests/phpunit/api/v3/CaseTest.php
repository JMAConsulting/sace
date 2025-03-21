<?php
/**
 * @file
 * File for the TestCase class
 *
 *  (PHP 5)
 *
 * @author Walt Haas <walt@dharmatech.org> (801) 534-1262
 * @copyright Copyright CiviCRM LLC (C) 2009
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html
 *              GNU Affero General Public License version 3
 * @version   $Id: ActivityTest.php 31254 2010-12-15 10:09:29Z eileen $
 * @package   CiviCRM
 *
 *   This file is part of CiviCRM
 *
 *   CiviCRM is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU Affero General Public License
 *   as published by the Free Software Foundation; either version 3 of
 *   the License, or (at your option) any later version.
 *
 *   CiviCRM is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU Affero General Public License for more details.
 *
 *   You should have received a copy of the GNU Affero General Public
 *   License along with this program.  If not, see
 *   <http://www.gnu.org/licenses/>.
 */

/**
 *  Test APIv3 civicrm_case_* functions
 *
 * @package CiviCRM_APIv3
 * @group headless
 */
class api_v3_CaseTest extends CiviCaseTestCase {
  protected $_params;
  protected $_entity;
  protected $_apiversion = 3;
  protected $followup_activity_type_value;
  /**
   * Activity ID of created case.
   *
   * @var int
   */
  protected $_caseActivityId;

  /**
   * @var \Civi\Core\SettingsStack
   */
  protected $settingsStack;

  /**
   * Test setup for every test.
   *
   * Connect to the database, truncate the tables that will be used
   * and redirect stdin to a temporary file.
   */
  public function setUp(): void {
    $this->_entity = 'case';

    parent::setUp();

    $activityTypes = $this->callAPISuccess('option_value', 'get', [
      'option_group_id' => 2,
      'name' => 'Follow Up',
      'label' => 'Follow Up',
      'sequential' => 1,
    ]);
    $this->followup_activity_type_value = $activityTypes['values'][0]['value'];

    $this->_params = [
      'case_type_id' => $this->caseTypeId,
      'subject' => 'Test case',
      'contact_id' => 17,
    ];

    $this->settingsStack = new \Civi\Core\SettingsStack();
  }

  public function tearDown(): void {
    $this->settingsStack->popAll();
    parent::tearDown();
  }

  /**
   * Check with empty array.
   */
  public function testCaseCreateEmpty(): void {
    $this->callAPIFailure('case', 'create', []);
  }

  /**
   * Check if required fields are not passed.
   */
  public function testCaseCreateWithoutRequired(): void {
    $params = [
      'subject' => 'this case should fail',
      'case_type_id' => 1,
    ];

    $this->callAPIFailure('case', 'create', $params);
  }

  /**
   * Test Getlist with id and case_id
   */
  public function testCaseGetListById(): void {
    $params = $this->_params;
    $params['contact_id'] = $this->individualCreate();

    //Create 3 sample Cases.
    $case1 = $this->callAPISuccess('case', 'create', $params);
    $params['subject'] = 'Test Case 2';
    $case2 = $this->callAPISuccess('case', 'create', $params);
    $params['subject'] = 'Test Case 3';
    $this->callAPISuccess('case', 'create', $params);

    $getParams = [
      'id' => [$case1['id']],
      'extra' => ['contact_id'],
      'params' => [
        'version' => 3,
        'case_id' => ['!=' => $case2['id']],
        'case_id.is_deleted' => 0,
        'case_id.status_id' => ['!=' => "Closed"],
        'case_id.end_date' => ['IS NULL' => 1],
      ],
    ];
    $result = $this->callAPISuccess('case', 'getlist', $getParams);

    //Only 1 case should be returned.
    $this->assertEquals(count($result['values']), 1);
    $this->assertEquals($result['values'][0]['id'], $case1['id']);

    // For this next part to work we need to make sure we don't have a ton of cases in total.
    // We expect cases to have ids 1, 2, 3 etc, so the 'input' param below should match one and only one.
    $this->assertLessThan(10, $this->callAPISuccess('Case', 'getcount'));
    // These are the same params as the file-on-case widget
    $getParams = [
      'input' => '2',
      'extra' => ['contact_id'],
      'params' => [
        'version' => 3,
        'case_id' => ['!=' => NULL],
        'case_id.is_deleted' => 0,
        'case_id.status_id' => ['!=' => "Closed"],
        'case_id.end_date' => ['IS NULL' => 1],
      ],
    ];
    $result = $this->callAPISuccess('case', 'getlist', $getParams);
    $this->assertCount(1, $result['values']);
    $this->assertEquals(2, $result['values'][0]['id']);
  }

  /**
   * Test create function with valid parameters.
   */
  public function testCaseCreate(): void {
    $params = $this->_params;
    // Test using name instead of value.
    unset($params['case_type_id']);
    $params['case_type'] = $this->caseType;
    $result = $this->callAPISuccess('case', 'create', $params);
    $id = $result['id'];

    // Check result
    $result = $this->callAPISuccess('case', 'get', ['id' => $id]);
    $this->assertEquals($result['values'][$id]['id'], $id);
    $this->assertEquals($result['values'][$id]['case_type_id'], $this->caseTypeId);
    $this->assertEquals($result['values'][$id]['subject'], $params['subject']);
  }

  /**
   * Test create function with resolved status.
   */
  public function testCaseCreateWithResolvedStatus(): void {
    $params = $this->_params;
    // Test using name instead of value.
    unset($params['case_type_id']);
    $params['case_type'] = $this->caseType;
    $params['status_id'] = 'Closed';
    $result = $this->callAPISuccess('case', 'create', $params);
    $id = $result['id'];

    // Check result
    $result = $this->callAPISuccess('case', 'get', ['id' => $id]);
    $this->assertEquals($result['values'][$id]['id'], $id);
    $this->assertEquals($result['values'][$id]['case_type_id'], $this->caseTypeId);
    $this->assertEquals($result['values'][$id]['subject'], $params['subject']);
    $this->assertEquals($result['values'][$id]['end_date'], date('Y-m-d'));

    //Check all relationship end dates are set to case end date.
    $relationships = $this->callAPISuccess('Relationship', 'get', [
      'sequential' => 1,
      'case_id' => $id,
    ]);
    foreach ($relationships['values'] as $values) {
      $this->assertEquals($values['end_date'], date('Y-m-d'));
    }

    //Verify there are no active relationships.
    $activeCaseRelationships = CRM_Case_BAO_Case::getCaseRoles($result['values'][$id]['client_id'][1], $id);
    $this->assertEquals(count($activeCaseRelationships), 0, "Checking for empty array");

    //Check if getCaseRoles() is able to return inactive relationships.
    $caseRelationships = CRM_Case_BAO_Case::getCaseRoles($result['values'][$id]['client_id'][1], $id, NULL, FALSE);
    $this->assertEquals(count($caseRelationships), 1);
  }

  /**
   * Test case create with valid parameters and custom data.
   */
  public function testCaseCreateCustom(): void {
    $ids = $this->entityCustomGroupWithSingleFieldCreate(__FUNCTION__, __FILE__);
    $params = $this->_params;
    $params['custom_' . $ids['custom_field_id']] = "custom string";
    $result = $this->callAPISuccess($this->_entity, 'create', $params);
    $result = $this->callAPISuccess($this->_entity, 'get', [
      'return.custom_' . $ids['custom_field_id'] => 1,
      'id' => $result['id'],
    ]);
    $this->assertEquals("custom string", $result['values'][$result['id']]['custom_' . $ids['custom_field_id']]);

    $this->customFieldDelete($ids['custom_field_id']);
    $this->customGroupDelete($ids['custom_group_id']);
  }

  /**
   * Test update (create with id) function with valid parameters.
   */
  public function testCaseUpdate(): void {
    $params = $this->_params;
    // Test using name instead of value
    unset($params['case_type_id']);
    $params['case_type'] = $this->caseType;
    $result = $this->callAPISuccess('case', 'create', $params);
    $id = $result['id'];
    $case = $this->callAPISuccess('case', 'getsingle', ['id' => $id]);

    // Update Case.
    $params = ['id' => $id];
    $params['subject'] = $case['subject'] = 'Something Else';
    $this->callAPISuccess('case', 'create', $params);

    // Verify that updated case is equal to the original with new subject.
    $result = $this->callAPISuccessGetSingle('Case', ['case_id' => $id]);
    // Modification dates are likely to differ by 0-2 sec. Check manually.
    $this->assertGreaterThanOrEqual($case['modified_date'], $result['modified_date']);
    unset($result['modified_date'], $case['modified_date']);
    // Everything else should be identical.
    $this->assertAPIArrayComparison($result, $case);
  }

  /**
   * Test update (create with id) function with valid parameters.
   */
  public function testCaseUpdateWithExistingCaseContact(): void {
    $params = $this->_params;
    // Test using name instead of value
    unset($params['case_type_id']);
    $params['case_type'] = $this->caseType;
    $result = $this->callAPISuccess('case', 'create', $params);
    $id = $result['id'];
    $case = $this->callAPISuccess('case', 'getsingle', ['id' => $id]);

    // Update Case, we specify existing case ID and existing contact ID to verify that CaseContact.create is not called
    $params = $this->_params;
    $params['id'] = $id;
    $this->callAPISuccess('case', 'create', $params);

    // Verify that updated case is equal to the original with new subject.
    $result = $this->callAPISuccessGetSingle('Case', ['case_id' => $id]);
    // Modification dates are likely to differ by 0-2 sec. Check manually.
    $this->assertGreaterThanOrEqual($case['modified_date'], $result['modified_date']);
    unset($result['modified_date'], $case['modified_date']);
    // Everything else should be identical.
    $this->assertAPIArrayComparison($result, $case);
  }

  /**
   * Test case update with custom data
   */
  public function testCaseUpdateCustom(): void {
    $ids = $this->entityCustomGroupWithSingleFieldCreate(__FUNCTION__, __FILE__);
    $params = $this->_params;

    // Create a case with custom data
    $params['custom_' . $ids['custom_field_id']] = 'custom string';
    $result = $this->callAPISuccess($this->_entity, 'create', $params);

    $caseId = $result['id'];
    $result = $this->callAPISuccess($this->_entity, 'get', [
      'return.custom_' . $ids['custom_field_id'] => 1,
      'version' => 3,
      'id' => $result['id'],
    ]);
    $this->assertEquals("custom string", $result['values'][$result['id']]['custom_' . $ids['custom_field_id']]);
    $fields = $this->callAPISuccess($this->_entity, 'getfields', ['version' => $this->_apiversion]);
    $this->assertTrue(is_array($fields['values']['custom_' . $ids['custom_field_id']]));

    // Update the activity with custom data.
    $params = [
      'id' => $caseId,
      'custom_' . $ids['custom_field_id'] => 'Updated my test data',
      'version' => $this->_apiversion,
    ];
    $result = $this->callAPISuccess($this->_entity, 'create', $params);

    $result = $this->callAPISuccess($this->_entity, 'get', [
      'return.custom_' . $ids['custom_field_id'] => 1,
      'version' => 3,
      'id' => $result['id'],
    ]);
    $this->assertEquals("Updated my test data", $result['values'][$result['id']]['custom_' . $ids['custom_field_id']]);
  }

  /**
   * Test delete function with valid parameters.
   */
  public function testCaseDelete(): void {
    // Create Case
    $result = $this->callAPISuccess('case', 'create', $this->_params);

    // Move Case to Trash
    $id = $result['id'];
    $this->callAPISuccess('case', 'delete', ['id' => $id, 'move_to_trash' => 1]);

    // Check result - also check that 'case_id' works as well as 'id'
    $result = $this->callAPISuccess('case', 'get', ['case_id' => $id]);
    $this->assertEquals(1, $result['values'][$id]['is_deleted']);

    // Restore Case from Trash
    $this->callAPISuccess('case', 'restore', ['id' => $id]);

    // Check result
    $result = $this->callAPISuccess('case', 'get', ['case_id' => $id]);
    $this->assertEquals(0, $result['values'][$id]['is_deleted']);

    // Delete Case Permanently
    $this->callAPISuccess('case', 'delete', ['case_id' => $id]);

    // Check result - case should no longer exist
    $result = $this->callAPISuccess('case', 'get', ['id' => $id]);
    $this->assertEquals(0, $result['count']);
  }

  /**
   * Test Case role relationship is correctly created
   * for contacts.
   */
  public function testCaseRoleRelationships(): void {
    // Create Case
    $case = $this->callAPISuccess('case', 'create', $this->_params);
    $relType = $this->relationshipTypeCreate(['name_a_b' => 'Test AB', 'name_b_a' => 'Test BA', 'contact_type_b' => 'Individual']);
    $relContact = $this->individualCreate(['first_name' => 'First', 'last_name' => 'Last']);

    $_REQUEST = [
      'rel_type' => "{$relType}_b_a",
      'rel_contact' => $relContact,
      'case_id' => $case['id'],
      'is_unit_test' => TRUE,
    ];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    $ret = CRM_Contact_Page_AJAX::relationship();
    unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    $this->assertEquals(0, $ret['is_error']);
    //Check if relationship exist for the case.
    $relationship = $this->callAPISuccess('Relationship', 'get', [
      'sequential' => 1,
      'relationship_type_id' => $relType,
      'case_id' => $case['id'],
    ]);
    $this->assertEquals($relContact, $relationship['values'][0]['contact_id_a']);
    $this->assertEquals($this->_params['contact_id'], $relationship['values'][0]['contact_id_b']);

    //Check if activity is assigned to correct contact.
    $activity = $this->callAPISuccess('Activity', 'get', [
      'subject' => 'Test BA : Mr. First Last II',
    ]);
    $this->callAPISuccess('ActivityContact', 'get', [
      'contact_id' => $relContact,
      'activity_id' => $activity['id'],
    ]);
  }

  /**
   * Test get function based on activity.
   */
  public function testCaseGetByActivity(): void {
    // Create Case
    $result = $this->callAPISuccess('case', 'create', $this->_params);
    $id = $result['id'];

    // Check result - we should get a list of activity ids
    $result = $this->callAPISuccess('case', 'get', ['id' => $id, 'return' => 'activities']);
    $case = $result['values'][$id];
    $activity = $case['activities'][0];

    // Fetch case based on an activity id
    $result = $this->callAPISuccess('case', 'get', [
      'activity_id' => $activity,
      'return' => 'activities',
    ]);
    $this->assertEquals(FALSE, empty($result['values'][$id]));
    $this->assertEquals($result['values'][$id], $case);
  }

  /**
   * Test get function based on contact id.
   */
  public function testCaseGetByContact(): void {
    // Create Case
    $result = $this->callAPISuccess('case', 'create', $this->_params);
    $id = $result['id'];

    // Store result for later
    $case = $this->callAPISuccessGetSingle('case', ['id' => $id, 'return' => ['activities', 'contacts']]);

    // Fetch case based on client contact id
    $result = $this->callAPISuccess('case', 'get', [
      'client_id' => $this->_params['contact_id'],
      'return' => ['activities', 'contacts'],
    ]);
    $this->assertAPIArrayComparison($result['values'][$id], $case);
  }

  /**
   * Test get function based on subject.
   */
  public function testCaseGetBySubject(): void {
    // Create Case
    $result = $this->callAPISuccess('case', 'create', $this->_params);
    $id = $result['id'];

    // Store result for later
    $case = $this->callAPISuccessGetSingle('Case', ['id' => $id, 'return' => 'subject']);

    // Fetch case based on client contact id
    $result = $this->callAPISuccess('case', 'get', [
      'subject' => $this->_params['subject'],
      'return' => ['subject'],
    ]);
    $this->assertAPIArrayComparison($result['values'][$id], $case);
  }

  /**
   * Test get function based on wrong subject.
   */
  public function testCaseGetByWrongSubject(): void {
    $this->callAPISuccess('case', 'create', $this->_params);

    // Append 'wrong' to subject so that it is no longer the same.
    $result = $this->callAPISuccess('case', 'get', [
      'subject' => $this->_params['subject'] . 'wrong',
      'return' => ['activities', 'contacts'],
    ]);
    $this->assertEquals(0, $result['count']);
  }

  /**
   * Test get function with no criteria.
   */
  public function testCaseGetNoCriteria(): void {
    $result = $this->callAPISuccess('case', 'create', $this->_params);
    $id = $result['id'];

    // Store result for later
    $case = $this->callAPISuccessGetSingle('Case', ['id' => $id, 'return' => 'contact_id']);

    $result = $this->callAPISuccess('case', 'get', ['limit' => 0, 'return' => ['contact_id']]);
    $this->assertAPIArrayComparison($result['values'][$id], $case);
  }

  /**
   * Test activity api create for case activities.
   */
  public function testCaseActivityCreate(): void {
    $params = $this->_params;
    $case = $this->callAPISuccess('case', 'create', $params);
    $params = [
      'case_id' => $case['id'],
      // follow up
      'activity_type_id' => $this->followup_activity_type_value,
      'subject' => 'Test followup 123',
      'source_contact_id' => $this->getLoggedInUser(),
      'target_contact_id' => $this->_params['contact_id'],
    ];
    $result = $this->callAPISuccess('Activity', 'create', $params);
    $this->assertEquals($result['values'][$result['id']]['activity_type_id'], $params['activity_type_id']);

    // might need this for other tests that piggyback on this one
    $this->_caseActivityId = $result['values'][$result['id']]['id'];

    // Check other DB tables populated properly - is there a better way to do this? assertDBState() requires that we know the id already.
    $dao = new CRM_Case_DAO_CaseActivity();
    $dao->case_id = $case['id'];
    $dao->activity_id = $this->_caseActivityId;
    $this->assertEquals($dao->find(), 1, 'case_activity table not populated correctly');

    $dao = new CRM_Activity_DAO_ActivityContact();
    $dao->activity_id = $this->_caseActivityId;
    $dao->contact_id = $this->_params['contact_id'];
    $dao->record_type_id = 3;
    $this->assertEquals($dao->find(), 1, 'activity_contact table not populated correctly');

    // Check that fetching an activity by case id works, as well as returning case_id
    $result = $this->callAPISuccessGetSingle('Activity', [
      'case_id' => $case['id'],
      'activity_type_id' => $this->followup_activity_type_value,
      'subject' => 'Test followup 123',
      'return' => ['case_id'],
    ]);
    $this->assertContainsEquals($case['id'], $result['case_id']);
  }

  public function testCaseGetByStatus(): void {
    // Create 2 cases with different status ids.
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case 1",
      'case_type_id' => $this->caseTypeId,
      'status_id' => "Open",
      'sequential' => 1,
    ]);
    $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case 2",
      'case_type_id' => $this->caseTypeId,
      'status_id' => "Urgent",
      'sequential' => 1,
    ]);
    $result = $this->callAPISuccessGetSingle('Case', [
      'sequential' => 1,
      'contact_id' => 17,
      'status_id' => "Open",
    ]);
    $this->assertEquals($case1['id'], $result['id']);
  }

  public function testCaseGetWithRoles(): void {
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case with roles",
      'case_type_id' => $this->caseTypeId,
      'status_id' => "Open",
    ]);
    $result = $this->callAPISuccessGetSingle('Case', [
      'id' => $case1['id'],
      'status_id' => "Open",
      'return' => ['contacts'],
    ]);

    $foundManager = FALSE;
    foreach ($result['contacts'] as $contact) {
      if ($contact['role'] == 'Client') {
        $this->assertEquals(17, $contact['contact_id']);
      }
      elseif ($contact['role'] == 'Homeless Services Coordinator is') {
        $this->assertEquals(1, $contact['creator']);
        $this->assertEquals(1, $contact['manager']);
        $foundManager = TRUE;
      }
    }
    $this->assertTrue($foundManager);
  }

  /**
   * Test that case role is not assigned to logged in user if you've unchecked
   * the assign to creator in the case type definition.
   */
  public function testCaseGetWithRolesNoCreator(): void {
    // Copy and adjust stock case type so that assign role to creator is not checked
    $caseType = $this->callAPISuccess('CaseType', 'get', ['id' => $this->caseTypeId]);
    $newCaseType = $caseType['values'][$this->caseTypeId];
    // Sanity check that we're changing what we think we're changing.
    $this->assertEquals('Homeless Services Coordinator', $newCaseType['definition']['caseRoles'][0]['name']);
    // string '0' to match what actually happens when you do it in UI
    $newCaseType['definition']['caseRoles'][0]['creator'] = '0';
    unset($newCaseType['id']);
    $newCaseType['name'] = 'tree_climbing';
    $newCaseType['title'] = 'Tree Climbing';
    $newCaseType = $this->callAPISuccess('CaseType', 'create', $newCaseType);

    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case with roles no creator",
      'case_type_id' => $newCaseType['id'],
      'status_id' => "Open",
    ]);
    $result = $this->callAPISuccessGetSingle('Case', [
      'id' => $case1['id'],
      'status_id' => "Open",
      'return' => ['contacts'],
    ]);

    // There should only be the client role.
    $this->assertCount(1, $result['contacts']);
    $contact = $result['contacts'][0];
    $this->assertEquals('Client', $contact['role']);
    // For good measure
    $this->assertNotEquals(1, $contact['creator'] ?? NULL);
    $this->assertNotEquals(1, $contact['manager'] ?? NULL);

    // clean up
    $this->callAPISuccess('Case', 'create', ['id' => $case1['id'], 'case_type_id' => $this->caseTypeId]);
    $this->callAPISuccess('CaseType', 'delete', ['id' => $newCaseType['id']]);
  }

  public function testCaseGetWithDefinition(): void {
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case with definition",
      'case_type_id' => $this->caseTypeId,
      'status_id' => "Open",
    ]);
    $result1 = $this->callAPISuccessGetSingle('Case', [
      'id' => $case1['id'],
      'status_id' => "Open",
      'return' => ['case_type_id.definition'],
    ]);
    $result2 = $this->callAPISuccessGetSingle('Case', [
      'id' => $case1['id'],
      'status_id' => "Open",
      'return' => ['case_type_id', 'case_type_id.definition'],
    ]);
    $this->assertEquals($result1['case_type_id.definition'], $result2['case_type_id.definition']);
    $def = $result1['case_type_id.definition'];
    $this->assertEquals(['name' => 'Open Case', 'max_instances' => 1], $def['activityTypes'][0]);
    $this->assertNotEmpty($def['activitySets'][0]['activityTypes']);
    $this->assertNotEmpty($def['caseRoles'][0]['manager']);
    $this->assertNotEmpty($def['caseRoles'][0]['creator']);
  }

  public function testCaseGetTags(): void {
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => 17,
      'subject' => "Test case with tags",
      'case_type_id' => $this->caseTypeId,
      'status_id' => "Open",
    ]);
    $tag1 = $this->tagCreate([
      'name' => 'CaseTag1',
      'used_for' => 'civicrm_case',
    ]);
    $tag2 = $this->tagCreate([
      'name' => 'CaseTag2',
      'used_for' => 'civicrm_case',
    ]);
    $this->callAPISuccess('EntityTag', 'create', [
      'entity_table' => 'civicrm_case',
      'entity_id' => $case1['id'],
      'tag_id' => $tag1['id'],
    ]);
    $this->callAPIFailure('Case', 'getsingle', [
      'tag_id' => $tag2['id'],
    ]);
    $result = $this->callAPISuccessGetSingle('Case', [
      'tag_id' => $tag1['id'],
      'return' => 'tag_id.name',
    ]);
    $this->assertEquals('CaseTag1', $result['tag_id'][$tag1['id']]['tag_id.name']);
  }

  /**
   * Test that a chained api call can use the operator syntax.
   *
   * E.g. array('IN' => $value.contact_id)
   *
   * @throws \Exception
   */
  public function testCaseGetChainedOp(): void {
    $contact1 = $this->individualCreate([], 1);
    $contact2 = $this->individualCreate([], 2);
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact1,
      'subject' => "Test case 1",
      'case_type_id' => $this->caseTypeId,
    ]);
    $case2 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact2,
      'subject' => "Test case 2",
      'case_type_id' => $this->caseTypeId,
    ]);
    $case3 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => [$contact1, $contact2],
      'subject' => "Test case 3",
      'case_type_id' => $this->caseTypeId,
    ]);

    // Fetch case 1 and all cases with the same client. Chained get should return case 3.
    $result = $this->callAPISuccessGetSingle('Case', [
      'id' => $case1['id'],
      'return' => 'contact_id',
      'api.Case.get' => [
        'contact_id' => ['IN' => "\$value.contact_id"],
        'id' => ['!=' => "\$value.id"],
      ],
    ]);
    $this->assertEquals($case3['id'], $result['api.Case.get']['id']);

    // Fetch case 3 and all cases with the same clients. Chained get should return case 1&2.
    $result = $this->callAPISuccessGetSingle('Case', [
      'id' => $case3['id'],
      'return' => ['contact_id'],
      'api.Case.get' => [
        'return' => 'id',
        'contact_id' => ['IN' => "\$value.contact_id"],
        'id' => ['!=' => "\$value.id"],
      ],
    ]);
    $this->assertEquals([$case1['id'], $case2['id']], array_keys(CRM_Utils_Array::rekey($result['api.Case.get']['values'], 'id')));
  }

  /**
   * Test the ability to order by client using the join syntax.
   *
   * For multi-client cases, should order by the first client.
   */
  public function testCaseGetOrderByClient(): void {
    $contact1 = $this->individualCreate(['first_name' => 'Aa', 'last_name' => 'Zz']);
    $contact2 = $this->individualCreate(['first_name' => 'Bb', 'last_name' => 'Zz']);
    $contact3 = $this->individualCreate(['first_name' => 'Cc', 'last_name' => 'Xx']);

    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact1,
      'subject' => "Test case 1",
      'case_type_id' => $this->caseTypeId,
    ]);
    $case2 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact2,
      'subject' => "Test case 2",
      'case_type_id' => $this->caseTypeId,
    ]);
    $case3 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => [$contact3, $contact1],
      'subject' => "Test case 3",
      'case_type_id' => $this->caseTypeId,
    ]);

    $result = $this->callAPISuccess('Case', 'get', [
      'contact_id' => ['IN' => [$contact1, $contact2, $contact3]],
      'sequential' => 1,
      'return' => 'id',
      'options' => ['sort' => 'contact_id.first_name'],
    ]);
    $this->assertEquals($case3['id'], $result['values'][2]['id']);
    $this->assertEquals($case2['id'], $result['values'][1]['id']);
    $this->assertEquals($case1['id'], $result['values'][0]['id']);

    $result = $this->callAPISuccess('Case', 'get', [
      'contact_id' => ['IN' => [$contact1, $contact2, $contact3]],
      'sequential' => 1,
      'return' => 'id',
      'options' => ['sort' => 'contact_id.last_name ASC, contact_id.first_name DESC'],
    ]);
    $this->assertEquals($case1['id'], $result['values'][2]['id']);
    $this->assertEquals($case2['id'], $result['values'][1]['id']);
    $this->assertEquals($case3['id'], $result['values'][0]['id']);

    $result = $this->callAPISuccess('Case', 'get', [
      'contact_id' => ['IN' => [$contact1, $contact2, $contact3]],
      'sequential' => 1,
      'return' => 'id',
      'options' => ['sort' => 'contact_id.first_name DESC'],
    ]);
    $this->assertEquals($case1['id'], $result['values'][2]['id']);
    $this->assertEquals($case2['id'], $result['values'][1]['id']);
    $this->assertEquals($case3['id'], $result['values'][0]['id']);

    $result = $this->callAPISuccess('Case', 'get', [
      'contact_id' => ['IN' => [$contact1, $contact2, $contact3]],
      'sequential' => 1,
      'return' => 'id',
      'options' => ['sort' => 'case_type_id, contact_id DESC, status_id'],
    ]);
    $this->assertEquals($case1['id'], $result['values'][2]['id']);
    $this->assertEquals($case2['id'], $result['values'][1]['id']);
    $this->assertEquals($case3['id'], $result['values'][0]['id']);
    $this->assertCount(3, $result['values']);
  }

  /**
   * Test Case.Get does not return case clients as part of related contacts.
   *
   * For multi-client cases, case clients should not be returned in duplicates for contacts.
   */
  public function testCaseGetDoesNotReturnClientsAsPartOfRelatedContacts(): void {
    $contact1 = $this->individualCreate(['first_name' => 'Aa', 'last_name' => 'Zz']);
    $contact2 = $this->individualCreate(['first_name' => 'Bb', 'last_name' => 'Zz']);
    $relContact = $this->individualCreate(['first_name' => 'Rel', 'last_name' => 'Contact']);

    $case = $this->callAPISuccess('Case', 'create', [
      'contact_id' => [$contact1, $contact2],
      'subject' => "Test case 1",
      'case_type_id' => $this->caseTypeId,
    ]);

    $relType = $this->relationshipTypeCreate(['name_a_b' => 'Test AB', 'name_b_a' => 'Test BA', 'contact_type_b' => 'Individual']);
    $relContact = $this->individualCreate(['first_name' => 'First', 'last_name' => 'Last']);
    $_REQUEST = [
      'rel_type' => "{$relType}_b_a",
      'rel_contact' => $relContact,
      'case_id' => $case['id'],
      'is_unit_test' => TRUE,
    ];
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    CRM_Contact_Page_AJAX::relationship();
    unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    $result = $this->callAPISuccess('Case', 'get', [
      'id' => $case['id'],
      'sequential' => 1,
      'return' => ['id', 'contacts'],
    ]);

    $caseContacts = $result['values'][0]['contacts'];
    $contactIds = array_column($caseContacts, 'contact_id');
    // We basically need to ensure that the case clients are not returned more than once.
    // i.e there should be no duplicates for case clients.
    $caseContactInstances = (array_count_values($contactIds));
    $this->assertEquals(1, $caseContactInstances[$contact1]);
    $this->assertEquals(1, $caseContactInstances[$contact2]);

    // Verify that the case clients are not part of related contacts.
    $relatedContacts = CRM_Case_BAO_Case::getRelatedContacts($case['id']);
    $relatedContacts = array_column($relatedContacts, 'contact_id');
    $this->assertNotContains($contact1, $relatedContacts);
    $this->assertNotContains($contact2, $relatedContacts);
  }

  /**
   * Test the ability to add a timeline to an existing case.
   *
   * See the case.addtimeline api.
   *
   * @throws \Exception
   */
  public function testCaseAddtimeline(): void {
    $caseSpec = [
      'title' => 'Application with Definition',
      'name' => 'Application_with_Definition',
      'is_active' => 1,
      'weight' => 4,
      'definition' => [
        'activityTypes' => [
          ['name' => 'Follow up'],
        ],
        'activitySets' => [
          [
            'name' => 'set1',
            'label' => 'Label 1',
            'timeline' => 1,
            'activityTypes' => [
              ['name' => 'Open Case', 'status' => 'Completed'],
            ],
          ],
          [
            'name' => 'set2',
            'label' => 'Label 2',
            'timeline' => 1,
            'activityTypes' => [
              ['name' => 'Follow up'],
            ],
          ],
        ],
        'caseRoles' => [
          ['name' => 'Homeless Services Coordinator', 'creator' => 1, 'manager' => 1],
        ],
      ],
    ];
    $cid = $this->individualCreate();
    $caseType = $this->callAPISuccess('CaseType', 'create', $caseSpec);
    $case = $this->callAPISuccess('Case', 'create', [
      'case_type_id' => $caseType['id'],
      'contact_id' => $cid,
      'subject' => 'Test case with timeline',
    ]);
    // Created case should only have 1 activity per the spec
    $result = $this->callAPISuccessGetSingle('Activity', ['case_id' => $case['id'], 'return' => 'activity_type_id.name']);
    $this->assertEquals('Open Case', $result['activity_type_id.name']);
    // Add timeline.
    $this->callAPISuccess('Case', 'addtimeline', [
      'case_id' => $case['id'],
      'timeline' => 'set2',
    ]);
    $result = $this->callAPISuccess('Activity', 'get', [
      'case_id' => $case['id'],
      'return' => 'activity_type_id.name',
      'sequential' => 1,
      'options' => ['sort' => 'id'],
    ]);
    $this->assertEquals(2, $result['count']);
    $this->assertEquals('Follow up', $result['values'][1]['activity_type_id.name']);
  }

  /**
   * Test the case merge function.
   *
   * 2 cases should be mergeable into 1
   */
  public function testCaseMerge(): void {
    $contact1 = $this->individualCreate([], 1);
    $case1 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact1,
      'subject' => 'Test case 1',
      'case_type_id' => $this->caseTypeId,
    ]);
    $case2 = $this->callAPISuccess('Case', 'create', [
      'contact_id' => $contact1,
      'subject' => 'Test case 2',
      'case_type_id' => $this->caseTypeId,
    ]);
    $result = $this->callAPISuccess('Case', 'getcount', ['contact_id' => $contact1]);
    $this->assertEquals(2, $result);

    $this->callAPISuccess('Case', 'merge', ['case_id_1' => $case1['id'], 'case_id_2' => $case2['id']]);

    $result = $this->callAPISuccess('Case', 'getsingle', ['id' => $case2['id']]);
    $this->assertEquals(1, $result['is_deleted']);
  }

  public function testTimestamps(): void {
    $params = $this->_params;
    $case_created = $this->callAPISuccess('case', 'create', $params);

    $case_1 = $this->callAPISuccess('Case', 'getsingle', [
      'id' => $case_created['id'],
    ]);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $case_1['created_date']);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $case_1['modified_date']);
    $this->assertApproxEquals(strtotime($case_1['created_date']), strtotime($case_1['modified_date']), 2);

    $activity_1 = $this->callAPISuccess('activity', 'getsingle', [
      'case_id' => $case_created['id'],
      'options' => [
        'limit' => 1,
      ],
    ]);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $activity_1['created_date']);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $activity_1['modified_date']);
    $this->assertApproxEquals(strtotime($activity_1['created_date']), strtotime($activity_1['modified_date']), 2);

    usleep(1.5 * 1000000);
    $this->callAPISuccess('activity', 'create', [
      'id' => $activity_1['id'],
      'subject' => 'Make cheese',
    ]);

    $activity_2 = $this->callAPISuccess('activity', 'getsingle', [
      'id' => $activity_1['id'],
    ]);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $activity_2['created_date']);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $activity_2['modified_date']);
    $this->assertNotEquals($activity_2['created_date'], $activity_2['modified_date']);

    $this->assertEquals($activity_1['created_date'], $activity_2['created_date']);
    $this->assertNotEquals($activity_1['modified_date'], $activity_2['modified_date']);
    $this->assertLessThan($activity_2['modified_date'], $activity_1['modified_date'],
      sprintf("Original modification time (%s) should predate later modification time (%s)", $activity_1['modified_date'], $activity_2['modified_date']));

    $case_2 = $this->callAPISuccess('Case', 'getsingle', [
      'id' => $case_created['id'],
    ]);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $case_2['created_date']);
    $this->assertMatchesRegularExpression(';^\d\d\d\d-\d\d-\d\d \d\d:\d\d;', $case_2['modified_date']);
    $this->assertEquals($case_1['created_date'], $case_2['created_date']);
    $this->assertNotEquals($case_2['created_date'], $case_2['modified_date']);
  }

}
