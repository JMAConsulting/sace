<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Asset;

use DrupalCodeGenerator\Helper\Renderer\RendererInterface;

/**
 * An interface for renderable assets.
 */
interface RenderableInterface {

  /**
   * Renders the asset.
   */
  public function render(RendererInterface $renderer): void;

}
