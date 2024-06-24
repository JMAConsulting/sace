<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateFileExists
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateFileExists extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param $argName
     *   The argument name containing the path to check.
     */
    public function __construct(
        public string $argName,
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $args = $attribute->getArguments();
        $commandInfo->addAnnotation('validate-file-exists', $args['argName']);
=======
    public function validate(CommandData $commandData)
    {
        $missing = [];
        $argName = $this->argName;
        if ($commandData->input()->hasArgument($argName)) {
            $path = $commandData->input()->getArgument($argName);
        } elseif ($commandData->input()->hasOption($argName)) {
            $path = $commandData->input()->getOption($argName);
        }
        if (!empty($path) && !file_exists($path)) {
            $missing[] = $path;
        }

        if ($missing) {
            $msg = dt('File(s) not found: !paths', ['!paths' => implode(', ', $missing)]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
