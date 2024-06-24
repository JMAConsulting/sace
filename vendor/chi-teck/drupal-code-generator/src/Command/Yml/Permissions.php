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
use DrupalCodeGenerator\Validator\Chained;
use DrupalCodeGenerator\Validator\PermissionId;
use DrupalCodeGenerator\Validator\Required;

#[Generator(
  name: 'yml:permissions',
  description: 'Generates a permissions yml file',
  aliases: ['permissions', 'permissions.yml'],
  templatePath: Application::TEMPLATE_PATH . '/Yaml/_permissions',
  type: GeneratorType::MODULE_COMPONENT,
)]
final class Permissions extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);

    $vars['machine_name'] = $ir->askMachineName();

    $default_title = 'Administer {machine_name} configuration';
    $vars['title'] = $ir->ask('Permission title', $default_title, new Required());
    $vars['id'] = $ir->ask(
      question: 'Permission ID',
      default: self::getDefaultId($vars['title']),
      validator: new Chained(new Required(), new PermissionId()),
    );
    $vars['description'] = $ir->ask('Permission description');
    $vars['restrict_access'] = $ir->confirm('Display warning about site security on the Permissions page?', FALSE);

    $assets->addFile('{machine_name}.permissions.yml', 'permissions.twig')
      ->appendIfExists();
  }

  /**
   * Builds default permission ID.
   */
  private static function getDefaultId(string $title): string {
    $id = \strtolower($title);
    $id = \preg_replace(['/^[0-9]+/', '/[^a-z0-9_ ]+/', '/ +/'], ' ', $id);
    return \trim($id, '_ ');
  }

}
