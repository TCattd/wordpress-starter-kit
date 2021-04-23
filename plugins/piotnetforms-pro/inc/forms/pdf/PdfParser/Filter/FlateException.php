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
 * Exception for flate filter class
 */
class FlateException extends FilterException
{
    /**
     * @var integer
     */
    const NO_ZLIB = 0x0401;

    /**
     * @var integer
     */
    const DECOMPRESS_ERROR = 0x0402;
}
