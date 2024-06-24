<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Drush\Commands\config\ConfigCommands;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateConfigName
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateConfigName extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param string $argumentName
     *   The name of the argument which specifies the config ID.
     */
    public function __construct(
        public string $argumentName = 'config_name'
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $commandInfo->addAnnotation(ConfigCommands::VALIDATE_CONFIG_NAME, $attribute->newInstance()->argumentName);
=======
    public function validate(CommandData $commandData)
    {
        $configName = $commandData->input()->getArgument($this->argumentName);
        $config = \Drupal::config($configName);
        if ($config->isNew()) {
            $msg = dt('Config !name does not exist', ['!name' => $configName]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
