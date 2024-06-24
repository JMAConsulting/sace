<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateModulesEnabled
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateModulesEnabled extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param $modules
     *   The required module names.
     */
    public function __construct(
        public array $modules,
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $args = $attribute->getArguments();
        $commandInfo->addAnnotation('validate-module-enabled', $args['modules'] ?? $args[0]);
=======
    public function validate(CommandData $commandData)
    {
        $missing = array_filter($this->modules, fn($module) => !\Drupal::moduleHandler()->moduleExists($module));
        if ($missing) {
            $msg = dt('The following modules are required: !modules', ['!modules' => implode(', ', $missing)]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
