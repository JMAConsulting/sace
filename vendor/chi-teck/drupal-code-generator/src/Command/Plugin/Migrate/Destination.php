<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\Plugin\Migrate;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\AssetCollection;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

#[Generator(
  name: 'plugin:migrate:destination',
  description: 'Generates migrate destination plugin',
  aliases: ['migrate-destination'],
  templatePath: Application::TEMPLATE_PATH . '/Plugin/Migrate/_destination',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Destination extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['plugin_id'] = $ir->askPluginId(default: NULL);
    $vars['class'] = $ir->askPluginClass();
    $vars['services'] = $ir->askServices(FALSE);
    $assets->addFile('src/Plugin/migrate/destination/{class}.php', 'destination.twig');
  }

}
