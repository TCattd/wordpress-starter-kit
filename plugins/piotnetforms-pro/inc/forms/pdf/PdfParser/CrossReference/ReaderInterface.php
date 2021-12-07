<?php

/**
 * This file is part of FPDI
 *
 * @package   psetasign\Fpdi
 * @copyright Copyright (c) 2020 psetasign GmbH & Co. KG (https://www.psetasign.com)
 * @license   http://opensource.org/licenses/mit-license The MIT License
 */

namespace psetasign\Fpdi\PdfParser\CrossReference;

use psetasign\Fpdi\PdfParser\Type\PdfDictionary;

/**
 * ReaderInterface for cross-reference readers.
 */
interface ReaderInterface
{
    /**
     * Get an offset by an object number.
     *
     * @param int $objectNumber
     * @return int|bool False if the offset was not found.
     */
    public function getOffsetFor($objectNumber);

    /**
     * Get the trailer related to this cross reference.
     *
     * @return PdfDictionary
     */
    public function getTrailer();
}
