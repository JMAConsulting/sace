<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;

<<<<<<< HEAD
=======
#[Deprecated('Create an Attribute class that commands can use.')]
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
#[Attribute(Attribute::TARGET_METHOD  | \Attribute::IS_REPEATABLE)]
class HookSelector extends \Consolidation\AnnotatedCommand\Attributes\HookSelector
{
}
