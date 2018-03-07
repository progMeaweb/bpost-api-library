<?php

namespace Bpost\BpostApiClient\Bpost\Order;

/**
 * This class was solely added with the purpose of properly scoping the pugo address with the international namespace
 * when calling the toXml function. Every other solution I could think of was a lot more dirty or implied a huge rewrite.
 * bPost IntlPugoAddress class
 *
 * @author Tijs Verkoyen <php-bpost@verkoyen.eu>
 */
class IntlPugoAddress extends PugoAddress
{

    const TAG_NAME = 'international:pugoAddress';

}