<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\Test;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\AssetCollection as Assets;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;
use DrupalCodeGenerator\Utils;

#[Generator(
  name: 'test:nightwatch',
  description: 'Generates a nightwatch test',
  aliases: ['nightwatch-test'],
  templatePath: Application::TEMPLATE_PATH . '/Test/_nightwatch',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Nightwatch extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->askName();
    $vars['test_name'] = Utils::camelize($ir->ask('Test name', 'example'), FALSE);
    $assets->addFile('tests/src/Nightwatch/{test_name}Test.js', 'nightwatch.twig');
  }

}
