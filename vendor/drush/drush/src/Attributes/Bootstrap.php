<?php

declare(strict_types=1);

namespace Drush\Attributes;

use Attribute;
use Consolidation\AnnotatedCommand\Parser\CommandInfo;
use Drush\Boot\DrupalBootLevels;
use JetBrains\PhpStorm\ExpectedValues;

<<<<<<< HEAD
#[Attribute(Attribute::TARGET_METHOD)]
=======
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
class Bootstrap
{
    /**
     * @param $level
     *   The level to bootstrap to.
     * @package $extra
     *   A maximum level when used with MAX.
     */
    public function __construct(
        #[ExpectedValues(valuesFromClass: DrupalBootLevels::class)] public int $level,
        public ?int $max_level = null,
    ) {
    }

    public static function handle(\ReflectionAttribute $attribute, CommandInfo $commandInfo)
    {
        $instance = $attribute->newInstance();
        $commandInfo->addAnnotation('bootstrap', $instance->level . ( isset($instance->max_level) ? " $instance->max_level" : ''));
    }
}
