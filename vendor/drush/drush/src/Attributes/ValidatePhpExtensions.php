<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidatePhpExtensions
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidatePhpExtensions extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param $extensions
     *   The required module name.
     */
    public function __construct(
        public array $extensions,
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $args = $attribute->getArguments();
        $commandInfo->addAnnotation('validate-php-extension', $args['extensions'] ?? $args[0]);
=======
    public function validate(CommandData $commandData)
    {
        $missing = array_filter($this->extensions, fn($extension) => !extension_loaded($extension));
        if ($missing) {
            $msg = dt('The following PHP extensions are required: !extensions', ['!extensions' => implode(', ', $missing)]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
