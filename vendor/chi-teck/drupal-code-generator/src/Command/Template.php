<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command;

use DrupalCodeGenerator\Application;
use DrupalCodeGenerator\Asset\Assets;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\GeneratorType;

#[Generator(
  name: 'template',
  description: 'Generates a template',
  aliases: ['template'],
  templatePath: Application::TEMPLATE_PATH . '/_template',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Template extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['name'] = $ir->askName();

    $vars['template_name'] = $ir->ask('Template name', validator: [self::class, 'validateTemplateName']);
    if (!\str_ends_with($vars['template_name'], '.twig')) {
      $vars['template_name'] .= '.html.twig';
    }

    $vars['theme_key'] = \preg_replace(
      ['#(\.html)?\.twig$#', '#[^a-z\d]#'],
      ['', '_'],
      $vars['template_name'],
    );

    $vars['create_theme'] = $ir->confirm('Create theme hook?');
    $vars['create_preprocess'] = $ir->confirm('Create preprocess hook?');

    $assets->addFile('templates/{template_name}', 'template.twig');

    if ($vars['create_theme'] || $vars['create_preprocess']) {
      $assets->addFile('{machine_name}.module')
        ->template('module.twig')
<<<<<<< HEAD
        ->appendIfExists(7);
=======
        ->appendIfExists(9);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
  }

  /**
   * Validates template name.
   */
  public static function validateTemplateName(mixed $value): string {
    if (!\is_string($value) || !\preg_match('/^[a-z\d][a-z\d\.\-]*[a-z\d]$/', $value)) {
      throw new \UnexpectedValueException('The value is not correct template name.');
    }
    return $value;
  }

}
