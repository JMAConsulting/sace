<?php

namespace Drupal\nagios\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Allows admins to configure the Nagios module.
 *
 * @package Drupal\nagios\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * @param string $module
   * @param \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig $config
   *
   * @return bool
   */
  public static function getModuleHookEnabled($module, $config) {
    $enabled = $config->get('nagios.enable.' . $module);
    if ($enabled === NULL) {
      return TRUE;
    }

    return (bool) $enabled;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nagios_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'nagios.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    _nagios_update_os_user();

    $group = 'modules';
    $config = $this->config('nagios.settings');

    $form['nagios_status_page'] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Status page settings'),
    ];
    $form['nagios_status_page']['nagios_enable_status_page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable HTTP status page'),
      '#default_value' => $config->get('nagios.statuspage.enabled'),
      '#description' =>
        $this->t(
          'Even when the status page is ' .
          'disabled, you can use `$ drush nagios` to get the status from the ' .
          'command line.'
        ) . ' ' .
        $this->t('This integrates well with NRPE.'),
    ];
    $only_enabled_if_page = ['visible' => ['#edit-nagios-enable-status-page' => ['checked' => TRUE]]];
    $form['nagios_status_page']['nagios_page_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nagios page path'),
      '#description' => $this->t('Enter the path for the Nagios HTTP status page. It must be a valid Drupal path.'),
      '#default_value' => $config->get('nagios.statuspage.path'),
      '#states' => $only_enabled_if_page,
    ];
    $form['nagios_status_page']['nagios_ua'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unique ID'),
      '#default_value' => $config->get('nagios.ua'),
      '#description' => $this->t('Restrict sending information to requests identified by this Unique ID. You should change this to some unique string for your organization, and configure Nagios accordingly. This makes Nagios data less accessible to curious users. See the README.txt for more details.'),
      '#states' => $only_enabled_if_page,
    ];
    $form['nagios_status_page']['nagios_page_controller'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Nagios page controller'),
      '#description' =>
        $this->t('Enter the name of the controller and function to be used by the Nagios status page.') . ' ' .
        $this->t('Take care and be sure this function exists before submitting this form!'),
      '#default_value' => $config->get('nagios.statuspage.controller'),
    ];

    $form['nagios_status_page']['nagios_enable_status_page_get'] = [
      '#type' => 'radios',
      '#default_value' => (int) $config->get('nagios.statuspage.getparam'),
      '#options' => [
        0 => $this->t('The HTTP User Agent has to be exactly the Unique ID'),
        1 => $this->t('Enable Unique ID checking via GET parameter in the URL status page'),
      ],
      '#description' => $this->getUserAgentRadioDesc(),
      '#states' => $only_enabled_if_page,
    ];

    $form['nagios_error_levels'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Error levels'),
      '#description' => $this->t('Set the values to be used for error levels when reporting to Nagios.'),
    ];
    $form['nagios_error_levels']['nagios_status_ok_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Status OK'),
      '#description' => $this->t('The value to send to Nagios for a Status OK message.'),
      '#default_value' => $config->get('nagios.status.ok'),
    ];
    $form['nagios_error_levels']['nagios_status_warning_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Warning'),
      '#description' => $this->t('The value to send to Nagios for a Warning message.'),
      '#default_value' => $config->get('nagios.status.warning'),
    ];
    $form['nagios_error_levels']['nagios_status_critical_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Critical'),
      '#description' => $this->t('The value to send to Nagios for a Critical message.'),
      '#default_value' => $config->get('nagios.status.critical'),
    ];
    $form['nagios_error_levels']['nagios_status_unknown_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unknown'),
      '#description' => $this->t('The value to send to Nagios for an Unknown message.'),
      '#default_value' => $config->get('nagios.status.unknown'),
    ];

    $form[$group] = [
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Modules'),
      '#description' => $this->t('Select the modules that should report their data into Nagios.'),
    ];

    $modules = nagios_invoke_all('nagios_info');
    foreach ($modules as $module => $data) {
      $form[$group]['nagios_enable_' . $module] = [
        '#type' => 'checkbox',
        '#title' => $data['name'] . ' (' . $module . ')',
        '#default_value' => self::getModuleHookEnabled($module, $config),
      ];
    }
    if (count($modules) < 2) {
      $form[$group]['#access'] = FALSE;
    }

    if (\Drupal::moduleHandler()->loadInclude('dblog', 'admin.inc')) {
      $form['watchdog'] = [
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#title' => $this->t('Watchdog settings'),
        '#description' => $this->t('Controls how watchdog messages are retrieved and displayed when watchdog checking is set.'),
        '#states' => [
          'visible' => [
            '#edit-nagios #edit-nagios-func-watchdog' => ['checked' => TRUE],
            '#edit-nagios-enable-nagios' => ['checked' => TRUE],
          ],
        ],
        '#weight' => 5,
      ];
      $form['watchdog']['limit_watchdog_display'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Limit watchdog display'),
        '#default_value' => $config->get('nagios.limit_watchdog.display'),
        '#description' => $this->t('Limit watchdog messages to only those that are new since the last check.'),
      ];

      $form['watchdog']['channel_filter_group'] = [
        '#type' => 'fieldgroup',
        '#title' => $this->t('Channel filter'),
      ];

      $channel_filter = $config->get('nagios.limit_watchdog.channel_filter') ?: [];
      $filters = dblog_filters();
      $options = empty($filters['type']['options']) ?
        ['php' => 'php'] :
        $filters['type']['options'];
      $options += $channel_filter;

      $negate = $config->get('nagios.limit_watchdog.negate');
      $form['watchdog']['channel_filter_group']['channel_filter'] = [
        '#type' => 'select',
        '#title' => $negate ?
          $this->t('Message types to include') :
          $this->t('Message types to ignore'),
        '#default_value' => $channel_filter,
        '#options' => $options,
        '#multiple' => TRUE,
        '#size' => 8,
        '#description' => $this->t('Types are also known as channels. You can ignore multiple with the Ctrl or &#8984; key on your keyboard.'),
      ];

      $form['watchdog']['channel_filter_group']['negate'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Negate condition'),
        '#default_value' => $negate,
        '#description' => $this->t('If checked, the ignored message types are those that are not selected in the list above.'),
      ];
    }

    foreach (nagios_invoke_all('nagios_settings') as $module => $module_settings) {
      $form[$module] = [
        '#type' => 'fieldset',
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#title' => $module,
        '#states' => [
          'visible' => [
            '#edit-nagios-enable-' . str_replace('-', '_', $module) => ['checked' => TRUE],
          ],
        ],
      ];

      foreach ($module_settings as $element => $data) {
        $form[$module][$element] = $data;

        // Set #defaultvalue from #configname for first level form elements:
        if (!isset($data['#default_value']) && isset($data['#configname'])) {
          $form[$module][$element]['#default_value'] = $config->get($module . '.' . $data['#configname']);
        }

        // Set #defaultvalue from #configname for second level form elements:
        if (isset($data['#type']) && $data['#type'] == 'fieldset') {
          foreach ($data as $fieldset_element => $fieldset_data) {
            if (is_array($fieldset_data) && !isset($fieldset_data['#default_value']) && isset($fieldset_data['#configname'])) {
              $form[$module][$element][$fieldset_element]['#default_value'] = $config->get($module . '.' . $fieldset_data['#configname']);
            }
          }
        }
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Builds the description.
   *
   * @return string
   *   Dynamic help text
   */
  private function getUserAgentRadioDesc() {
    $config = $this->config('nagios.settings');
    $getParam = (int) $config->get('nagios.statuspage.getparam');
    $uniqueId = $config->get('nagios.ua');
    $routeParameters = $getParam ? ['unique_id' => $uniqueId] : [];
    $aUrl = [
      ':url' => Url::fromRoute(
        'nagios.statuspage',
        $routeParameters,
        ['absolute' => TRUE]
      )->toString(),
    ];
    $aUrlInfo = parse_url(reset($aUrl));

    $urlForCli = "'" . reset($aUrl) . "'";
    $commands[] = 'curl --no-progress-meter --url ' . $urlForCli . ($getParam ? '' : " --user-agent '$uniqueId'");
    $commands[] = 'wget --no-verbose -O -' . ($getParam ? '' : " -U '$uniqueId'") . ' ' . $urlForCli;
    $commands[] = realpath(__DIR__ . '/../../nagios-plugin/check_drupal') . ' -H ' . $aUrlInfo['host'] . ' -P ' . $config->get('nagios.statuspage.path') . ($getParam ? '?' . $aUrlInfo['query'] : " -U '$uniqueId'") . ($aUrlInfo['scheme'] == 'https' ? ' -S' : '');
    $commands[] = 'drush nagios # ' . $this->t('no HTTP status page needed');

    return $this->t('If enabled the $_GET variable "unique_id" is used for checking the correct Unique ID instead of "User Agent" ($_SERVER[\'HTTP_USER_AGENT\']).') . ' ' .
      ($getParam ? $this->t('You need to call the following URL from Nagios / Icinga / cURL: <a href=":url">:url</a>.', $aUrl) . ' ' : '') .
      $this->t('This feature is useful to avoid webserver stats with the Unique ID as "User Agent" and helpful for human testing.') . '<br><br>' .
      $this->t('Here are a few example Bash commands you can use to query the status:') . '<ul><li>' . join('<li>', $commands) . '</ul>';
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $default_ua = $form_state->getValue('nagios_ua') == 'Nagios';
    if ($default_ua && $form_state->getValue('nagios_enable_status_page')) {
      $form_state->setErrorByName(
        'nagios_ua',
        $this->t('You must change the Unique ID if you make the status page available over HTTP.') . ' ' .
        $this->t('This step reduces the risk of publicly sharing information that might make hacking your site easier.')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('nagios.settings');
    $config->set('nagios.ua', $form_state->getValue('nagios_ua'));
    $config->set('nagios.show_outdated_names', $form_state->getValue('nagios_show_outdated_names'));
    $status_page = $form_state->getValue('nagios_enable_status_page');
    $config->set('nagios.statuspage.enabled', (bool) $status_page);
    if ($status_page) {
      $route_info_changed =
        $config->get('nagios.statuspage.path') != $form_state->getValue('nagios_page_path') ||
        $config->get('nagios.statuspage.controller') != $form_state->getValue('nagios_page_controller');
    }

    $config->set('nagios.statuspage.path', $form_state->getValue('nagios_page_path'));
    $config->set('nagios.statuspage.controller', $form_state->getValue('nagios_page_controller'));
    $config->set('nagios.statuspage.getparam', $form_state->getValue('nagios_enable_status_page_get'));
    $config->set('nagios.status.ok', (int) $form_state->getValue('nagios_status_ok_value'));
    $config->set('nagios.status.warning', (int) $form_state->getValue('nagios_status_warning_value'));
    $config->set('nagios.status.critical', (int) $form_state->getValue('nagios_status_critical_value'));
    $config->set('nagios.status.unknown', (int) $form_state->getValue('nagios_status_unknown_value'));

    foreach (nagios_invoke_all('nagios_info') as $module => $data) {
      $config->set('nagios.enable.' . $module, $form_state->getValue('nagios_enable_' . $module));
    }

    $config->set('nagios.limit_watchdog.display', $form_state->getValue('limit_watchdog_display'));
    $config->set('nagios.limit_watchdog.channel_filter', $form_state->getValue('channel_filter'));
    $config->set('nagios.limit_watchdog.negate', $form_state->getValue('negate'));

    foreach (nagios_invoke_all('nagios_settings') as $module => $module_settings) {
      foreach ($module_settings as $element => $data) {
        // Save config for first level form elements.
        // For the nagios module, this saves if check functions are enabled,
        // e.g. "nagios.function.cron".
        if (isset($data['#configname'])) {
          $config->set($module . '.' . $data['#configname'], $form_state->getValue($element, $config->get($module . '.' . $data['#configname'])));
        }

        // Save config for second level form elements:
        if (isset($data['#type']) && $data['#type'] == 'fieldset') {
          foreach ($data as $fieldset_element => $fieldset_data) {
            if (is_array($fieldset_data) && !isset($fieldset_data['#default_value']) && isset($fieldset_data['#configname'])) {
              $config->set($module . '.' . $fieldset_data['#configname'], $form_state->getValue($fieldset_element, $config->get($module . '.' . $fieldset_data['#configname'])));
            }
          }
        }
      }
    }

    $config->save();

    if (!empty($route_info_changed)) {
      \Drupal::service('router.builder')->rebuild();
      \Drupal::cache('menu')->invalidateAll();
    }

    parent::submitForm($form, $form_state);
  }

}
