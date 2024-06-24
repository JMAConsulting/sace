<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session;

/**
<<<<<<<< HEAD:vendor/symfony/http-foundation/Session/SessionFactoryInterface.php
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface SessionFactoryInterface
========
 * Base exception for all configuration exceptions.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class Exception extends \RuntimeException
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/config/Definition/Exception/Exception.php
{
    public function createSession(): SessionInterface;
}
