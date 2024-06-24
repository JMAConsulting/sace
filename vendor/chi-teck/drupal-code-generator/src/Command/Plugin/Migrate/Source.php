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
  name: 'plugin:migrate:source',
  description: 'Generates migrate source plugin',
  aliases: ['migrate-source'],
  templatePath: Application::TEMPLATE_PATH . '/Plugin/Migrate/_source',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Source extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, AssetCollection $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['plugin_id'] = $ir->askPluginId(default: NULL);
    $vars['class'] = $ir->askPluginClass();
    $choices = [
      'sql' => 'SQL',
      'other' => 'Other',
    ];
    $vars['source_type'] = $ir->choice('Source type', $choices);
    $vars['base_class'] = $vars['source_type'] === 'sql' ? 'SqlBase' : 'SourcePluginBase';
    $assets->addFile('src/Plugin/migrate/source/{class}.php', 'source.twig');
  }

}
