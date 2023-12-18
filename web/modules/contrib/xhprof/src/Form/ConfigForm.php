<?php

namespace Drupal\xhprof\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to configure profiling settings.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['xhprof.config'];
  }

  /**
   * The storage.
   *
   * @var \Drupal\xhprof\XHProfLib\Storage\StorageManager
   */
  private $storageManager;

  /**
   * The profiler.
   *
   * @var \Drupal\xhprof\ProfilerInterface
   */
  private $profiler;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  private $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->storageManager = $container->get('xhprof.storage_manager');
    $instance->profiler = $container->get('xhprof.profiler');
    $instance->moduleHandler = $container->get('module_handler');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'xhprof_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('xhprof.config');
    $extension_loaded = $this->profiler->isLoaded();

    if ($extension_loaded) {
      $help = $this->t('Profile requests with the XHProf or Tideways php extension.');
    }
    else {
      $help = $this->t('You must enable the <a href=":xhprof">XHProf</a> or <a href=":tideways">Tideways</a> php extension.', [
        ':xhprof' => 'https://www.drupal.org/node/946182',
        ':tideways' => 'https://github.com/tideways/php-xhprof-extension',
      ]);
    }
    $form['help'] = [
      '#type' => 'inline_template',
      '#template' => '<span class="warning">{{ help }}</span>',
      '#context' => [
        'help' => $help,
      ],
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable profiling of page views.'),
      '#default_value' => $extension_loaded & $config->get('enabled'),
      '#disabled' => !$extension_loaded,
    ];

    $form['settings'] = [
      '#title' => $this->t('Profiling settings'),
      '#type' => 'details',
      '#open' => TRUE,
      '#states' => [
        'invisible' => [
          'input[name="enabled"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['settings']['extension'] = [
      '#type' => 'select',
      '#title' => $this->t('Extension'),
      '#options' => $this->profiler->getExtensions(),
      '#default_value' => $config->get('extension'),
      '#description' => $this->t('Choose the extension to use for profiling. The recommended extension is <a href=":url">%profiler</a> because it is actively maintained.', [
        ':url' => 'https://github.com/longxinH/xhprof',
        '%profiler' => 'XHProf',
      ]),
    ];

    $form['settings']['exclude'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#default_value' => $config->get('exclude'),
      '#description' => $this->t('Path to exclude for profiling. One path per line.'),
    ];

    $form['settings']['interval'] = [
      '#type' => 'number',
      '#title' => 'Profiling interval',
      '#min' => 0,
      '#default_value' => $config->get('interval'),
      '#description' => $this->t('The approximate number of requests between XHProf samples. Leave zero to profile all requests.'),
    ];

    $flags = [
      'FLAGS_CPU' => $this->t('Cpu'),
      'FLAGS_MEMORY' => $this->t('Memory'),
      'FLAGS_NO_BUILTINS' => $this->t('Exclude PHP builtin functions'),
    ];
    $form['settings']['flags'] = [
      '#type' => 'checkboxes',
      '#title' => 'Profile',
      '#options' => $flags,
      '#default_value' => $config->get('flags') ?: [],
      '#description' => $this->t('Flags to choose what profile.'),
    ];

    $form['settings']['exclude_indirect_functions'] = [
      '#type' => 'checkbox',
      '#title' => 'Exclude indirect functions',
      '#default_value' => $config->get('exclude_indirect_functions'),
      '#description' => $this->t('Exclude functions like %call_user_func and %call_user_func_array.', [
        '%call_user_func' => 'call_user_func',
        '%call_user_func_array' => 'call_user_func_array',
      ]),
    ];

    $options = $this->storageManager->getStorages();
    $form['settings']['storage'] = [
      '#type' => 'radios',
      '#title' => $this->t('Profile storage'),
      '#default_value' => $config->get('storage'),
      '#options' => $options,
      '#description' => $this->t('Choose the storage class.'),
    ];

    if ($this->moduleHandler->moduleExists('webprofiler')) {
      $form['webprofiler'] = [
        '#title' => $this->t('Webprofiler integration'),
        '#type' => 'details',
        '#open' => TRUE,
        '#states' => [
          'invisible' => [
            'input[name="enabled"]' => ['checked' => FALSE],
          ],
        ],
      ];

      $form['webprofiler']['show_summary_toolbar'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show summary data in toolbar.'),
        '#default_value' => $config->get('show_summary_toolbar'),
        '#description' => $this->t('Show data from the overall summary directly into the Webprofiler toolbar. May slow down the toolbar rendering.'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('xhprof.config')
      ->set('enabled', $form_state->getValue('enabled'))
      ->set('extension', $form_state->getValue('extension'))
      ->set('exclude', $form_state->getValue('exclude'))
      ->set('interval', $form_state->getValue('interval'))
      ->set('storage', $form_state->getValue('storage'))
      ->set('flags', $form_state->getValue('flags'))
      ->set('exclude_indirect_functions', $form_state->getValue('exclude_indirect_functions'))
      ->set('show_summary_toolbar', $form_state->getValue('show_summary_toolbar'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
