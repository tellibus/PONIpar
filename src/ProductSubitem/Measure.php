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
 * A <Measure> subitem.
 */
class Measure extends Subitem
{
    // List 48
    const TYPE_HEIGHT = '01';
    const TYPE_WIDTH = '02';
    const TYPE_THICKNESS = '03';
    const TYPE_WEIGHT = '08';
    
    // List 50
    const UNIT_CENTIMETERS = 'cm';
    const UNIT_GRAMS = 'gr';
    const UNIT_INCHES = 'in';
    const UNIT_KILOGRAMS = 'kg';
    const UNIT_POUNDS = 'lb';
    const UNIT_MILLIMETERS = 'mm';
    const UNIT_OUNCES = 'oz';

    /**
     * Text data
     */
    protected $type = null;
    protected $value = null;
    protected $unit = null;

    /**
     * Create a new Measure.
     *
     * @param mixed $in The <Measure> DOMDocument or DOMElement.
     */
    public function __construct($in)
    {
        parent::__construct($in);

        try {
            $this->type = $this->_getSingleChildElementText('MeasureTypeCode');
        } catch (\Exception $e) {
        }

        try {
            $this->value = $this->_getSingleChildElementText('Measurement');
        } catch (\Exception $e) {
        }

        try {
            $this->unit = $this->_getSingleChildElementText('MeasureUnitCode');
        } catch (\Exception $e) {
        }

        // Save memory.
        $this->_forgetSource();
    }

    /**
     * Retrieve the type code of the measure.
     *
     * @return string The contents of <MeasureTypeCode>.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Retrieve the actual value of the measure.
     *
     * @return string The contents of <Measurement>.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Retrieve the unit code of the measure.
     *
     * @return string The contents of <MeasureUnitCode>.
     */
    public function getUnit()
    {
        return $this->unit;
    }
};
