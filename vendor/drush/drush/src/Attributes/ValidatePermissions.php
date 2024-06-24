<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
<<<<<<< HEAD
use Consolidation\AnnotatedCommand\Parser\CommandInfo;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidatePermissions
=======
use Consolidation\AnnotatedCommand\CommandData;
use Consolidation\AnnotatedCommand\CommandError;
use Drush\Utils\StringUtils;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidatePermissions extends ValidatorBase implements ValidatorInterface
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
{
    /**
     * @param $argName
     *   The argument name containing the required permissions.
     */
    public function __construct(
        public string $argName,
    ) {
    }

<<<<<<< HEAD
    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $args = $attribute->getArguments();
        $commandInfo->addAnnotation('validate-permissions', $args['argName']);
=======
    public function validate(CommandData $commandData)
    {
        $missing = [];
        $arg_or_option_name = $this->argName;
        if ($commandData->input()->hasArgument($arg_or_option_name)) {
            $permissions = StringUtils::csvToArray($commandData->input()->getArgument($arg_or_option_name));
        } else {
            $permissions = StringUtils::csvToArray($commandData->input()->getOption($arg_or_option_name));
        }
        $all_permissions = array_keys(\Drupal::service('user.permissions')->getPermissions());
        $missing = array_diff($permissions, $all_permissions);
        if ($missing) {
            $msg = dt('Permission(s) not found: !perms', ['!perms' => implode(', ', $missing)]);
            return new CommandError($msg);
        }
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
