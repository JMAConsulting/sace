<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Helper\Renderer;

use DrupalCodeGenerator\Asset\RenderableInterface;

/**
 * Renderer interface.
 */
interface RendererInterface {

  /**
   * Renders a template.
   *
   * Templates with 'twig' extension are processed with Twig template engine.
   */
  public function render(string $template, array $vars): string;

  /**
   * Renders a template string directly.
   */
  public function renderInline(string $inline_template, array $vars): string;

  /**
   * Renders an asset.
   */
  public function renderAsset(RenderableInterface $asset): void;

  /**
   * Registers a path where templates are stored.
   */
  public function registerTemplatePath(string $path): void;

}
