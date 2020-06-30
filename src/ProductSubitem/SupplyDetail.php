<?php

namespace PONIpar\ProductSubitem;

/*
   This file is part of the PONIpar PHP Onix Parser Library.
   Copyright (c) 2012, [di] digitale informationssysteme gmbh
   All rights reserved.

   The software is provided under the terms of the new (3-clause) BSD license.
   Please see the file LICENSE for details.
*/

/**
 * A <ProductIdentifier> subitem.
 */
class SupplyDetail extends Subitem {

	// ONIX List 54
	const AVAILABILITY_CANCELLED = 'AB';
	const AVAILABILITY_UNCERTAIN = 'CS';
	const AVAILABILITY_AVAILABLE = 'IP';
	const AVAILABILITY_NOT_YET_PUBLISHED = 'NP';
	const AVAILABILITY_OUT_OF_STOCK_INDEFINITELY = 'OI';
	const AVAILABILITY_OUT_OF_PRINT = 'OP';
	const AVAILABILITY_REPLACED_BY_NEW_EDITION = 'OR';
	const AVAILABILITY_PUBLICATION_POSTPONED_INDEFINITELY = 'PP';

	// ONIX List 54
	const PRICE_TYPE_RRP_EXCLUDING_TAX = '01';

	protected $availability_codes = array(
		'IP' => 'Available',
		'NP' => 'Not yet available',
		'OP' => 'Terminated',
		'OR' => 'Replaced',
		'AB' => 'Cancelled',
		'CS' => 'Contact supplier'
	);

	protected $product_availabilities = array(
		'20' => 'Available',
		'10' => 'Not yet available',
		'11' => 'Awaiting stock',
		'21' => 'In stock',
		'40' => 'Not available',
		'41' => 'Replaced',
		'43' => 'No longer supplied',
		'51' => 'Terminated',
		'01' => 'Cancelled',
		'99' => 'Contact supplier'
	);

	/**
	 * Status of the availability
	 */
	protected $availability_code = null;
	protected $product_availability = null; // preferred by ONIX 2.1

	protected $on_sale_date = null;
	protected $warehouse_location_name = null;

	/**
	 * The identifier’s value.
	 */
	protected $prices = array();

	/**
	 * Create a new ProductIdentifier.
	 *
	 * @param mixed $in The <ProductIdentifier> DOMDocument or DOMElement.
	 */
	public function __construct($in) {

		parent::__construct($in);

		// Retrieve and check the type.
		try{ $this->availability_code = $this->_getSingleChildElementText('AvailabilityCode'); } catch(\Exception $e) { }
		try{ $this->product_availability = $this->_getSingleChildElementText('ProductAvailability'); } catch(\Exception $e) { }

		try{ $this->on_sale_date = $this->_getSingleChildElementText('OnSaleDate');} catch(\Exception $e) { }

		if( !$this->on_sale_date ){
			try{ $this->on_sale_date = $this->_getSingleChildElementText('SupplyDate/Date');} catch(\Exception $e) { }
		}

		try {
			$this->warehouse_location_name = $this->_getSingleChildElementText('Stock/LocationName');
		} catch (\Exception $e) { }
 
		// Get the prices.
		$this->prices = array();

		$prices = $this->xpath->query("/*/Price");

		foreach($prices as $price){
			//error_log(print_r($price, true));

			$this->prices[] = array(
				'PriceTypeCode' => $this->_getPriceData($price, 'PriceTypeCode'),
				'PriceAmount' => $this->_getPriceData($price, 'PriceAmount'),
				'CurrencyCode' => $this->_getPriceData($price, 'CurrencyCode'),
				'PriceEffectiveFrom' => $this->_getPriceData($price, 'PriceEffectiveFrom')
			);
		}

		// Save memory.
		$this->_forgetSource();
	}

	protected function _getPriceData($node, $key, $default=null){
		$list = $node->getElementsByTagName($key);

		if( $list->length > 0 )
			return $list->item(0)->textContent;

		return $default;
	}

	/**
	 * Retrieve the availability code of this supply detail
	 *
	 * @return string The contents of <AvailabilityCode>.
	 */
	public function getAvailabilityCode()
	{
		return $this->availability_code;
	}

	/**
	 * Retrieve the availability of this supply detail
	 *
	 * @return string The contents of <ProductIDType>.
	 */
	public function getAvailability() {

		if( $this->product_availability  )
			return isset($this->product_availabilities[$this->product_availability])
					? $this->product_availabilities[$this->product_availability]
					: 'Unknown';

		if( $this->availability_code  )
			return isset($this->availability_codes[$this->availability_code])
					? $this->availability_codes[$this->availability_code]
					: 'Unknown';

		return null;
	}

	/**
	 * Retrieve the actual value of this identifier.
	 *
	 * @return string The contents of <IDValue>.
	 */
	public function getOnSaleDate() {
		return $this->on_sale_date;
	}

	/**
	 * Retrieve the actual value of this identifier.
	 *
	 * @return string The contents of <IDValue>.
	 */
	public function getPrices() {
		return $this->prices;
	}

	/**
	* Get Warehouse Location Name
	*
	* @return string
	*/
	public function getWarehouseLocationName()
	{
		return $this->warehouse_location_name;
	}
}

?>
