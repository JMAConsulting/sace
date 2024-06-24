<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Command;

/**
 * Interface for generators that provide human-readable label.
 */
interface LabelInterface {

  /**
   * Returns the human-readable command label.
   */
  public function getLabel(): ?string;

}
