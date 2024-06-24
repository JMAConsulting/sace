<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

<<<<<<<< HEAD:vendor/symfony/var-exporter/Internal/Values.php
namespace Symfony\Component\VarExporter\Internal;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 *
 * @internal
========
namespace Symfony\Component\Mailer\Exception;

/**
 * @author Fabien Potencier <fabien@symfony.com>
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/mailer/Exception/TransportExceptionInterface.php
 */
class Values
{
<<<<<<<< HEAD:vendor/symfony/var-exporter/Internal/Values.php
    public $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
========
    public function getDebug(): string;

    public function appendDebug(string $debug): void;
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/mailer/Exception/TransportExceptionInterface.php
}
