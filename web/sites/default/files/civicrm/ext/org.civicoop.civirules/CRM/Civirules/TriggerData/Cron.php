<?php

class CRM_Civirules_TriggerData_Cron extends CRM_Civirules_TriggerData_TriggerData {

  protected $entity;

  public function __construct($contactId, $entity, $data, $entity_id=null) {
    parent::__construct();

    $this->entity = $entity;
    if ($entity_id) {
      $this->entity_id = $entity_id;
    } elseif (isset($data['id'])) {
      $this->entity_id = $data['id'];
    }
    $this->contact_id = $contactId;

    $this->setEntityData($entity, $data);
  }

  public function getEntity() {
    return $this->entity;
  }

}
