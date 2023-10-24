<?php

use CRM_Purgelogs_ExtensionUtil as E;
use CRM_Purgelogs_Config as C;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Purgelogs_Form_Config extends CRM_Core_Form {

  public function buildQuickForm() {

    // load configuration
    $processes = C::singleton()->getParams('processes');
    $theme = C::singleton()->getParams('theme') ?? 'jqueryui';

    if (!empty($processes)) {
      $this->configuration = $processes;
    }
    else {
      $this->configuration = '{
        "Folder": "",
        "Filename": "",
        "Period": "Months",
        "Frequency": "1",
        "Active": "No",
        "LastActivity": ""
      }';
    }

    $json_editor_mode = 'text';
    $this->assign('json_editor_mode', $json_editor_mode);
    $this->assign('theme', $theme);

    // set title
    CRM_Utils_System::setTitle(E::ts('Purgelogs config'));

    $themes = [
      "jqueryui" => "jQueryUI",
      "barebones" => "Barebones",
      "bootstrap3" => "Bootstrap 3",
      "bootstrap4" => "Bootstrap 4",
      "html" => "HTML",
    ];
    $this->addElement('select', "theme", "JSON Editor Theme", $themes);
    $this->add('hidden', 'configuration', $this->configuration);

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
    ]);
    $this->setDefaults(['theme' => $theme]);

    // add JSONEditor resources
    $resources = CRM_Core_Resources::singleton();
    $resources->addScriptFile('purgelogs', 'resources/jsoneditor/jsoneditor-1.3.5.min.js');
    $resources->addStyleFile('purgelogs', 'resources/css/purgelogs_jsoneditor.css');

    parent::buildQuickForm();
  }

  public function postProcess() {

    $values = $this->exportValues();
    $config = [];
    $config['processes'] = $values['configuration'];
    $config['theme'] = $values['theme'];
    $this->assign('theme', $config['theme']);

    C::singleton()->setParams($config);

    CRM_Core_Session::setStatus(E::ts('Purgelogs config have been saved', ['domain' => 'purgelogs']), 'Configuration Updated', 'success');

    parent::postProcess();
  }

}
