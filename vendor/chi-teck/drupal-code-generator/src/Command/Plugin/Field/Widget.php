<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\Plugin\Field;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\AssetCollection;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

#[Generator(
  name: 'plugin:field:widget',
  description: 'Generates field widget plugin',
  aliases: ['field-widget'],
  templatePath: Application::TEMPLATE_PATH . '/Plugin/Field/_widget',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Widget extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);

    $vars['machine_name'] = $ir->askMachineName();
    $vars['plugin_label'] = $ir->askPluginLabel();
    $vars['plugin_id'] = $ir->askPluginId();
    $vars['class'] = $ir->askPluginClass(suffix: 'Widget');
    $vars['configurable'] = $ir->confirm('Make the widget configurable?', FALSE);
    $vars['services'] = $ir->askServices(FALSE);

    $assets->addFile('src/Plugin/Field/FieldWidget/{class}.php', 'widget.twig');
    if ($vars['configurable']) {
      $assets->addSchemaFile()->template('schema.twig');
    }
  }

}
