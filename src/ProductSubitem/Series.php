<?php

namespace PONIpar\ProductSubitem;

use PONIpar\ProductSubitem\Subitem;

/*
   This file is part of the PONIpar PHP Onix Parser Library.
   Copyright (c) 2018, tellibus
   All rights reserved.

   The software is provided under the terms of the new (3-clause) BSD license.
   Please see the file LICENSE for details.
*/

/**
 * A <Series> subitem.
 */
class Series extends Subitem
{

	/**
	 * The title's values
	 */
	protected $titleOfSeries = null;
	protected $numberWithinSeries = null;

	/**
	 * Create a new Series.
	 *
	 * @param mixed $in The <Series> DOMDocument or DOMElement.
	 */
	public function __construct($in)
	{
		parent::__construct($in);

		try {
			$this->titleOfSeries = $this->_getSingleChildElementText('TitleOfSeries');
		} catch (\Exception $e) {
		}

		try {
			$this->numberWithinSeries = $this->_getSingleChildElementText('NumberWithinSeries');
		} catch (\Exception $e) {
		}

		$this->cleanValue();

		// Save memory.
		$this->_forgetSource();
	}

	/**
	 * Retrieve the title of the series.
	 *
	 * @return string The contents of <TitleOfSeries>.
	 */
	public function getTitleOfSeries()
	{
		return $this->titleOfSeries;
	}

	/**
	 * Retrieve the number within the series.
	 *
	 * @return string The contents of <NumberWithinSeries>.
	 */
	public function getNumberWithinSeries()
	{
		return $this->numberWithinSeries;
	}

	private function cleanValue()
	{
		if (!$this->titleOfSeries) {
			return;
		}

		$this->titleOfSeries = str_replace("<![CDATA[", "", $this->titleOfSeries);
		$this->titleOfSeries = preg_replace("/\]\]>*$/", "", $this->titleOfSeries);
	}
};
