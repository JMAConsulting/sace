<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

<<<<<<<< HEAD:vendor/symfony/serializer/Exception/UnsupportedFormatException.php
namespace Symfony\Component\Serializer\Exception;

/**
 * @author Konstantin Myakshin <molodchick@gmail.com>
 */
class UnsupportedFormatException extends NotEncodableValueException
========
namespace Symfony\Component\HttpFoundation\File;

/**
 * A PHP stream of unknown size.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Stream extends File
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/http-foundation/File/Stream.php
{
    public function getSize(): int|false
    {
        return false;
    }
}
