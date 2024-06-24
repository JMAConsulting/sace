<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\InputOutput;

/**
 * Interface for classes that depend on the console input and output.
 */
interface IOAwareInterface {

  /**
   * Sets or gets the console IO.
   *
   * @throws \LogicException
   *   When IO is not not initialized.
   */
  public function io(?IO $io = NULL): IO;

}
