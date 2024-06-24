<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

<<<<<<<< HEAD:vendor/symfony/mime/HtmlToTextConverter/DefaultHtmlToTextConverter.php
namespace Symfony\Component\Mime\HtmlToTextConverter;
========
namespace Symfony\Component\Mailer\Exception;
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/mailer/Exception/InvalidArgumentException.php

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
<<<<<<<< HEAD:vendor/symfony/mime/HtmlToTextConverter/DefaultHtmlToTextConverter.php
class DefaultHtmlToTextConverter implements HtmlToTextConverterInterface
========
class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
>>>>>>>> 6a554a825f521a86c6b530852924f3d817076498:vendor/symfony.bak/mailer/Exception/InvalidArgumentException.php
{
    public function convert(string $html, string $charset): string
    {
        return strip_tags(preg_replace('{<(head|style)\b.*?</\1>}is', '', $html));
    }
}
