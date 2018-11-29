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
 * A <Audience> subitem.
 */
class Audience extends Subitem
{
	// List 29
	const ONIX_AUDIENCE_CODE = '01';

	/**
	 * Text data
	 */
	protected $type = null;
	protected $value = null;

	/**
	 * Create a new Audience.
	 *
	 * @param mixed $in The <Audience> DOMDocument or DOMElement.
	 */
	public function __construct($in)
	{
		parent::__construct($in);

		try {
			$this->type = $this->_getSingleChildElementText('AudienceCodeType');
		} catch (\Exception $e) {
		}

		try {
			$this->value = $this->_getSingleChildElementText('AudienceCodeValue');
		} catch (\Exception $e) {
		}

		// Save memory.
		$this->_forgetSource();
	}

	/**
	 * Retrieve the type of this audience code.
	 *
	 * @return string The contents of <AudienceCodeType>.
	 */
	public function getType()
	{
		return $this->type;
	}

	/** 
	 * Retrieve the actual code of the audience.
	 *
	 * @return string The contents of <AudienceCodeValue>.
	 */
	public function getValue()
	{
		return $this->value;
	}
};
