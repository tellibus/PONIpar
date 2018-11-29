<?php

namespace PONIpar\ProductSubitem;

use PONIpar\ProductSubitem\Subitem;

/*
   This file is part of the PONIpar PHP Onix Parser Library.
   Copyright (c) 2012, [di] digitale informationssysteme gmbh
   All rights reserved.

   The software is provided under the terms of the new (3-clause) BSD license.
   Please see the file LICENSE for details.
*/

/**
 * A <MediaFile> subitem.
 */
class MediaFile extends Subitem
{
    // List 38
    const TYPE_FRONT_COVER = '04';

    // List 39
    const FORMAT_JPEG = '03';

    // List 40
    const LINK_URL = '01';

    /**
     * Text data
     */
    protected $type = null;
    protected $format = null;
    protected $linkType = null;
    protected $link = null;
    protected $date = null;

    /**
     * Create a new MediaFile.
     *
     * @param mixed $in The <MediaFile> DOMDocument or DOMElement.
     */
    public function __construct($in)
    {
        parent::__construct($in);

        try {
            $this->type = $this->_getSingleChildElementText('MediaFileTypeCode');
        } catch (\Exception $e) {
        }

        try {
            $this->format = $this->_getSingleChildElementText('MediaFileFormatCode');
        } catch (\Exception $e) {
        }

        try {
            $this->linkType = $this->_getSingleChildElementText('MediaFileLinkTypeCode');
        } catch (\Exception $e) {
        }

        try {
            $this->link = $this->_getSingleChildElementText('MediaFileLink');
        } catch (\Exception $e) {
        }

        try {
            $this->date = $this->_getSingleChildElementText('MediaFileDate');
        } catch (\Exception $e) {
        }

        // Save memory.
        $this->_forgetSource();
    }

    /**
     * Retrieve the url of the file.
     *
     * @return string The contents of <MediaFileLink>.
     */
    public function getUrl()
    {
        if ($this->type === self::TYPE_FRONT_COVER && $this->format === self::FORMAT_JPEG && $this->linkType === self::LINK_URL) {
            return $this->link;
        } else {
            return null;
        }
    }

    /**
     * Retrieve the date of the file.
     *
     * @return string The contents of <MediaFileDate>.
     */
    public function getDate()
    {
        if ($this->type === self::TYPE_FRONT_COVER && $this->format === self::FORMAT_JPEG && $this->linkType === self::LINK_URL) {
            return date('Y-m-d', strtotime($this->date));
        } else {
            return null;
        }
    }
};
