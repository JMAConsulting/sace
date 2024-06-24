<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Drush\Commands\ValidatorsCommands;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateEntityLoad
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;
use Drush\Utils\StringUtils;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateEntityLoad extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param $entityType
     *   The type of entity.
     * @param string $argumentName
     *   The name of the argument which specifies the entity ID.
     */
    public function __construct(
        public string $entityType,
        public string $argumentName
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $args = $attribute->getArguments();
        $commandInfo->addAnnotation(ValidatorsCommands::VALIDATE_ENTITY_LOAD, "{$args['entityType']} {$args['argumentName']}");
=======
    public function validate(CommandData $commandData)
    {
        $names = StringUtils::csvToArray($commandData->input()->getArgument($this->argumentName));
        $loaded = \Drupal::entityTypeManager()->getStorage($this->entityType)->loadMultiple($names);
        if ($missing = array_diff($names, array_keys($loaded))) {
            $msg = dt('Unable to load the !type: !str', ['!type' => $this->entityType, '!str' => implode(', ', $missing)]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
