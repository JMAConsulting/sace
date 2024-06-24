<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy\Exception;

/**
 * A RuntimeException for Psy.
 */
class RuntimeException extends \RuntimeException implements Exception
{
    private $rawMessage;

    /**
     * Make this bad boy.
     *
     * @param string          $message  (default: "")
     * @param int             $code     (default: 0)
     * @param \Throwable|null $previous (default: null)
     */
<<<<<<< HEAD
    public function __construct(string $message = '', int $code = 0, \Throwable $previous = null)
=======
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    {
        $this->rawMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Return a raw (unformatted) version of the error message.
     */
    public function getRawMessage(): string
    {
        return $this->rawMessage;
    }
}
