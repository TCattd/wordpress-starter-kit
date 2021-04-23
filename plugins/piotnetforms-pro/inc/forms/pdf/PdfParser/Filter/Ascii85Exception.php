<?php

/**
 * This file is part of FPDI
 *
 * @package   psetasign\Fpdi
 * @copyright Copyright (c) 2020 psetasign GmbH & Co. KG (https://www.psetasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace psetasign\Fpdi\PdfParser\Filter;

/**
 * Exception for Ascii85 filter class
 */
class Ascii85Exception extends FilterException
{
    /**
     * @var integer
     */
    const ILLEGAL_CHAR_FOUND = 0x0301;

    /**
     * @var integer
     */
    const ILLEGAL_LENGTH = 0x0302;
}
