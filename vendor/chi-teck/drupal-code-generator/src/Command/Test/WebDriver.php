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
use DrupalCodeGenerator\Validator\RequiredClassName;

#[Generator(
  name: 'test:webdriver',
  description: 'Generates a test that supports JavaScript',
  aliases: ['webdriver-test'],
  templatePath: Application::TEMPLATE_PATH . '/Test/_webdriver',
  type: GeneratorType::MODULE_COMPONENT,
  label: 'WebDriver',
)]
final class WebDriver extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->askName();
    $vars['class'] = $ir->ask('Class', 'ExampleTest', new RequiredClassName());
    $assets->addFile('tests/src/FunctionalJavascript/{class}.php', 'webdriver.twig');
  }

}
