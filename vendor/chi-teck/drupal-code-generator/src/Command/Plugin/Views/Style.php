<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\Plugin\Views;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\AssetCollection;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

#[Generator(
  name: 'plugin:views:style',
  description: 'Generates views style plugin',
  aliases: ['views-style'],
  templatePath: Application::TEMPLATE_PATH . '/Plugin/Views/_style',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Style extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->askName();

    $vars['plugin_label'] = $ir->askPluginLabel();
    $vars['plugin_id'] = $ir->askPluginId();
    $vars['class'] = $ir->askPluginClass();

    $vars['configurable'] = $ir->confirm('Make the plugin configurable?');

    $assets->addFile('src/Plugin/views/style/{class}.php')
      ->template('style.twig');

    $assets->addFile('templates/views-style-{plugin_id|u2h}.html.twig')
      ->template('template.twig');

    $assets->addFile('{machine_name}.module')
      ->template('preprocess.twig')
<<<<<<< HEAD
      ->appendIfExists(7);
=======
      ->appendIfExists(9);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

    if ($vars['configurable']) {
      $assets->addSchemaFile()->template('schema.twig');
    }
  }

}
