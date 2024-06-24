<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Event;

use DrupalCodeGenerator\Asset\AssetCollection;

/**
 * Fired after dumped assets.
 */
final class AssetPostProcess {

  /**
   * Constructs the event object.
   */
  public function __construct(
    public AssetCollection $assets,
    // Unlike PreProcessEvent::$destination this one is readonly as there is no
    // point in modifying it after dumping the assets.
    public readonly string $destination,
    public readonly string $commandName,
    public readonly bool $isDry,
  ) {}

}
