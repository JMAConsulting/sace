<<<<<<< HEAD
<?php declare(strict_types = 1);
=======
<?php

declare(strict_types=1);
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498

namespace DrupalCodeGenerator\Exception;

/**
 * The exception interface for DCG generators.
 *
 * Use this exception for errors caused by wrong user input or when environment
 * does not meet certain requirements. Do not use it for unexpected program
 * errors.
 */
interface ExceptionInterface extends \Throwable {

}
