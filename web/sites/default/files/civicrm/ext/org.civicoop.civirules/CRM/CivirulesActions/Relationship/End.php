<?php
/**
 * Class to process action end or delete relationship
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 Aug 2021
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Relationship_End extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @throws Exception when error from API Contact create
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    $contactId = (int) $triggerData->getContactId();
    $api4Active = CRM_Civirules_Utils::isApi4Active();
    if (!empty($contactId) && isset($actionParams['operation'])) {
      if ($actionParams['operation'] == 1) {
        $this->deleteRelationship($actionParams, $contactId, $api4Active);
      }
      else {
        $this->disableRelationship($actionParams, $contactId, $api4Active);
      }
    }
  }

  /**
   * Method to delete the relationships with either API3 or API4
   *
   * @param array $actionParams
   * @param int $contactId
   * @param bool $api4Active
   */
  private function deleteRelationship(array $actionParams, int $contactId, bool $api4Active) {
    if ($api4Active) {
      try {
        \Civi\Api4\Relationship::delete()
          ->addWhere('contact_id_a', '=', $contactId)
          ->addWhere('relationship_type_id', '=', (int) $actionParams['relationship_type_id'])
          ->execute();
      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Could not delete relationships with CiviRules in ") . __METHOD__ . ", error from API4 Relationship delete: ". $ex->getMessage());
      }
    }
    else {
      // with API3 the ID's need to be retrieved first
      try {
        $relationships = civicrm_api3('Relationship', 'get', [
          'sequential' => 1,
          'relationship_type_id' => (int) $actionParams['relationship_type_id'],
          'contact_id_a' => $contactId,
          'return' => ['id'],
        ]);
        foreach ($relationships['values'] as $relationshipId) {
          civicrm_api3('Relationship', 'delete', ['id' => (int) $relationshipId]);
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Could not delete relationships with CiviRules in ") . __METHOD__ . ", error from API3 Relationship get or delete: ". $ex->getMessage());
      }
    }
  }

  /**
   * Method to disable the relationships with either API3 or API4
   *
   * @param array $actionParams
   * @param int $contactId
   * @param bool $api4Active
   * @throws
   */
  private function disableRelationship(array $actionParams, int $contactId, bool $api4Active) {
    if (isset($actionParams['end_date']) && !empty($actionParams['end_date'])) {
      $endDate = new DateTime($actionParams['end_date']);
    }
    else {
      $endDate = new DateTime();
    }
    if ($api4Active) {
      try {
        \Civi\Api4\Relationship::update()
          ->addValue('end_date', $endDate->format('Y-m-d'))
          ->addValue('is_active', FALSE)
          ->addWhere('relationship_type_id', '=', (int) $actionParams['relationship_type_id'])
          ->addWhere('contact_id_a', '=', $contactId)
          ->execute();      }
      catch (API_Exception $ex) {
        Civi::log()->error(E::ts("Could not disable relationships with CiviRules in ") . __METHOD__ . ", error from API4 Relationship update: ". $ex->getMessage());
      }
    }
    else {
      // with API3 need to retrieve current relationship data first
      try {
        $relationships = civicrm_api3('Relationship', 'get', [
          'sequential' => 1,
          'relationship_type_id' => (int) $actionParams['relationship_type_id'],
          'contact_id_a' => $contactId,
          'return' => ['id'],
        ]);
        foreach ($relationships['values'] as $relationshipId) {
          civicrm_api3('Relationship', 'create', [
            'id' => (int) $relationshipId,
            'end_date' => $endDate->format('Y-m-d'),
            'is_active' => FALSE,
          ]);        }
      }
      catch (CiviCRM_API3_Exception $ex) {
        Civi::log()->error(E::ts("Could not disable relationships with CiviRules in ") . __METHOD__ . ", error from API3 Relationship get or create: ". $ex->getMessage());
      }
    }
  }

  /**
   * Method to add url for form action for rule
   *
   * @param int $ruleActionId
   * @return string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/relationship/end', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Method to create a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   * @throws
   */
  public function userFriendlyConditionParams() {
    $actionParams = $this->getActionParameters();
    if ($actionParams['operation'] == 1) {
      $label = "Delete relationship(s) for contact of type ";
    }
    else {
      $label = "Disable relationship(s) for contact of type ";
    }
    $label .= $this->getRelationshipTypeLabel($actionParams['relationship_type_id'], $actionParams['relation_contact']);
    if (isset($actionParams['end_date']) && !empty($actionParams['end_date']) && $actionParams['operation'] != 1) {
      $endDate = new DateTime($actionParams['end_date']);
      $label .= " on end date " . $endDate->format("d-m-Y");
    }
    return $label;
  }

  /**
   * Returns condition data as an array and ready for export.
   * E.g. replace ids for names.
   *
   * @return array
   */
  public function exportActionParameters() {
    $action_params = parent::exportActionParameters();
    try {
      $action_params['relationship_type_id'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'name_a_b',
        'id' => $action_params['relationship_type_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $action_params;
  }

  /**
   * Returns condition data as an array and ready for import.
   * E.g. replace name for ids.
   *
   * @return string
   */
  public function importActionParameters($action_params = NULL) {
    try {
      $action_params['relationship_type_id'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'id',
        'name_a_b' => $action_params['relationship_type_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return parent::importActionParameters($action_params);
  }

  /**
   * Method to get the relationship type label based on contact_a or contact_b
   *
   * @param $relationshipTypeId
   * @param $relationContact
   * @return mixed|string
   */
  private function getRelationshipTypeLabel($relationshipTypeId, $relationContact) {
    if (CRM_Civirules_Utils::isApi4Active()) {
      try {
        $relationshipType = \Civi\Api4\RelationshipType::get()
          ->addSelect('label_a_b')
          ->addWhere('id', '=', (int) $relationshipTypeId)
          ->setLimit(1)
          ->execute();
        $label = $relationshipType->first();
        return $label['label_a_b'];
      }
      catch (API_Exception $ex) {
      }
    }
    else {
      try {
        $label = civicrm_api3('RelationshipType', 'getvalue', [
          'return' => ["label_a_b"],
          'id' => (int) $relationshipTypeId,
        ]);
        if ($label) {
          return $label;
        }
      }
      catch (CiviCRM_API3_Exception $ex) {
      }
    }
    return "";
  }

}
