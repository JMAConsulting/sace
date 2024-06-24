<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command\Yml;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\AssetCollection as Assets;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

#[Generator(
  name: 'yml:routing',
  description: 'Generates a routing yml file',
  aliases: ['routing', 'routing.yml'],
  templatePath: Application::TEMPLATE_PATH . '/Yaml/_routing',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Routing extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->askName();
    $vars['class'] = '{machine_name|camelize}Controller';
    $assets->addFile('{machine_name}.routing.yml', 'routing.twig');
  }

}
