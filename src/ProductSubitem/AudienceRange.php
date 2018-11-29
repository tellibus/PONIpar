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
 * A <AudienceRange> subitem.
 */
class AudienceRange extends Subitem
{
	// List 30
	const QUALIFIER_US_SCHOOL_GRADE_RANGE = '01'; // not implemented
	const QUALIFIER_INTEREST_AGE_IN_MONTHS = '16';
	const QUALIFIER_INTEREST_AGE_IN_YEARS = '17';

	// List 31
	const PRECISION_EXACT = '01';
	const PRECISION_FROM = '03';
	const PRECISION_TO = '04';

	/**
	 * Text data
	 */
	protected $qualifier = null;
	protected $precisions = null;
	protected $values = null;

	/**
	 * Create a new AudienceRange.
	 *
	 * @param mixed $in The <AudienceRange> DOMDocument or DOMElement.
	 */
	public function __construct($in)
	{
		parent::__construct($in);

		try {
			$this->qualifier = $this->_getSingleChildElementText('AudienceRangeQualifier');
		} catch (\Exception $e) {
		}

		try {
			$this->precisions = $this->_getCollectionOfChildElementsTexts('AudienceRangePrecision');
		} catch (\Exception $e) {
		}

		try {
			$this->values = $this->_getCollectionOfChildElementsTexts('AudienceRangeValue');
		} catch (\Exception $e) {
		}

		// Save memory.
		$this->_forgetSource();
	}

	/**
	 * Retrieve the qualifier.
	 *
	 * @return string The contents of <AudienceRangeQualifier>.
	 */
	public function getQualifier()
	{
		return $this->qualifier;
	}

	/**
	 * Retrieve the precisions for the values.
	 *
	 * @return array of <AudienceRangePrecision>.
	 */
	public function getPrecisions()
	{
		$precisions = [];
		$precisionCodes = $this->precisions;
		
		foreach ($precisionCodes as $precisionCode) {
			switch ($precisionCode) {
				case self::PRECISION_EXACT:
					$precisions[] = 'PRECISION_EXACT';

					break;
				case self::PRECISION_FROM:
					$precisions[] = 'PRECISION_FROM';

					break;
				case self::PRECISION_TO:
					$precisions[] = 'PRECISION_TO';
			}
		}

		return $precisions;
	}

	/**
	 * Retrieve the actual values.
	 *
	 * @return array of <AudienceRangeValue>.
	 */
	public function getValues()
	{
		return $this->values;
	}
};
