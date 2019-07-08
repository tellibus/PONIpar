<?php

namespace PONIpar;

use PONIpar\ProductSubitem\Subject;
use PONIpar\Exceptions\XMLException;
use PONIpar\ProductSubitem\Language;
use PONIpar\ProductSubitem\Audience;
use PONIpar\ProductSubitem\AudienceRange;
use PONIpar\ProductSubitem\OtherText;
use PONIpar\ProductSubitem\MediaFile;
use PONIpar\ProductSubitem\Measure;
use PONIpar\ProductSubitem\Series;
use PONIpar\Exceptions\ElementNotFoundException;

/*
   This file is part of the PONIpar PHP Onix Parser Library.
   Copyright (c) 2012, [di] digitale informationssysteme gmbh
   All rights reserved.

   The software is provided under the terms of the new (3-clause) BSD license.
   Please see the file LICENSE for details.
*/

/**
 * Represents a single product in the ONIX data, provides raw access as well as
 * convenience methods.
 */
class Product {

	/**
	 * Cardinality restrictions for subitems. Default cardinality values are
	 * min=0 and max=0 (meaning "unlimited").
	 */
	protected static $allowedSubitems = array(
		'ProductIdentifier' => array('min' => 1),
	);

	protected static $productStatus = array(
		"00" => "Unspecified",
		"01" => "Cancelled",
		"02" => "Forthcoming",
		"03" => "Postponed indefinitely",
		"04" => "Active",
		"05" => "No longer our product",
		"06" => "Out of stock indefinitely",
		"07" => "Out of print",
		"08" => "Inactive",
		"09" => "Unknown",
		"10" => "Remaindered",
		"11" => "Withdrawn from sale",
		"12" => "Not available in this market",
		"13" => "Active, but not sold separately",
		"14" => "Active, with market restrictions",
		"15" => "Recalled",
		"16" => "Temporarily withdrawn from sale"
	);

	/**
	 * The version of ONIX we are parsing
	 */
	protected $version = null;

	/**
	 * Holds the DOM of our <Product>, initialized in the constructor.
	 */
	protected $dom = null;

	/**
	 * Holds the XPath instance for the DOM.
	 */
	protected $xpath = null;

	/**
	 * Holds the subitem instances in our Product.
	 */
	protected $subitems = array();

	/**
	 * Create a new instance based on a <Product> DOM document.
	 *
	 * The DOM document needs to have its elements converted to reference names.
	 *
	 * @param DOMDocument $dom The DOM with <Product> as its root.
	 */
	public function __construct(\DOMDocument $dom, $version) {

		$this->version = $version;

		// Save the DOM.
		$this->dom = $dom;
		// Get an XPath instance for that DOM.
		$this->xpath = new \DOMXPath($dom);
		// Check the cardinalities of the subitems.
		foreach (self::$allowedSubitems as $name => $opts) {
			$min = isset($opts['min']) ? (int)$opts['min'] : 0;
			$max = isset($opts['max']) ? (int)$opts['max'] : 0;
			if ($min || $max) {
				$elements = $this->xpath->query("/Product/$name");
				$count = $elements->length;
			}
			if ($min && ($count < $min)) {
				throw new XMLException(
					"expecting at least $min <$name> childs, but $count found"
				);
			}
			if ($max && ($count > $max)) {
				throw new XMLException(
					"expecting at most $max <$name> childs, but $count found"
				);
			}
		}
	}

	/**
	 * Get all child elements with the specified name as an array of either
	 * Subitem subclass objects or DOMElements.
	 *
	 * If there is a matching subclass, that will be (created and) returned,
	 * else raw DOMElements.
	 *
	 * @param  string $name The element name to retrieve.
	 * @return array  A (possibly empty) array of Subitem subclass objects or
	 *                DOMElement objects.
	 */
	public function get($name, $classname=null) {

		$classname = $classname ? $classname : $name;

		// If we don’t already have the items in the cache, create them.
		if (!isset($this->subitems[$name])) {
			$subitems = array();
			// Retrieve all matching children.
			$elements = $this->xpath->query("/Product/$name");

			// If we have a Subitem subclass for that element, create instances
			// and return them.
			$subitemclass = __NAMESPACE__ . "\\ProductSubitem\\{$classname}";
			if (class_exists($subitemclass)) {
				foreach ($elements as $element) {
					$subitems[] = new $subitemclass($element);
				}
			} else {
				// Else, return clones of the matched nodes.
				foreach ($elements as $element) {
					$subitems[] = $element->cloneNode(true);
				}
			}
			$this->subitems[$name] = $subitems;
		}
		return $this->subitems[$name];
	}

	/**
	* Gets version of ONIX being parsed
	*/
	public function getVersion(){
		return $this->version;
	}

	/**
	 * Get a copy of the original <Product> DOM.
	 *
	 * Useful for retrieving information we don’t have any convenience methods
	 * and classes for.
	 *
	 * @return DOMDocument A copy of the DOM passed to the constructor.
	 */
	public function getDOM() {
		return $this->dom->cloneNode(true);
	}

	/**
	 * Get a product identifier of the given type.
	 *
	 * ONIX allows for multiple identifiers per product. This method retrieves
	 * all <ProductIdentifier> subitems and returns the one with the given type.
	 * If there is no identifier with that type, an ElementNotFoundException
	 * will be thrown.
	 *
	 * @todo   Support passing a name for proprietary identifiers.
	 * @param  string $type The type of identifier to search for. Using one of
	 *                      ProductIdentifierProductSubitem’s TYPE_* constants
	 *                      is recommended.
	 * @return string The found product identifier.
	 */
	public function getIdentifier($type) {
		$ids = $this->get('ProductIdentifier');
		foreach ($ids as $id) {
			if ($id->getType() == $type) {
				return $id->getValue();
			}
		}
		throw new ElementNotFoundException("no identifier of type $type found");
	}


	/**
	* Get Edition
	*
	* See list 64 for status codes
	*
	* @return string
	*/
	public function getPublishingStatus(){
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/PublishingStatus')[0]->nodeValue;
		else
			return isset($this->get('PublishingStatus')[0]) ? $this->get('PublishingStatus')[0]->nodeValue : null;
	}

	public function getPublishingStatusString(){
		$status = $this->PublishingStatus();
		return isset(self::$productStatus[$status]) ? self::$productStatus[$status] : 'Unknown';
	}

	public function isActive(){
		return in_array($this->publishingStatus(),['04','02']); // 'Active' and `Forthcoming` (list 64)
	}

	/**
	* Get Product Form
	*
	* See list 150 for form codes
	*
	* @return string
	*/
	public function getProductForm(){
		if( $this->version >= '3.0' )
			return $this->get('DescriptiveDetail/ProductForm')[0]->nodeValue;
		else {
			return $this->get('ProductForm')[0]->nodeValue;
		}
	}

	/**
	 * Get ProductFormDetail
	 *
	 * List 175
	 *
	 * @return string
	 */
	public function getProductFormDetail()
	{
		if($this->version >= '3.0') {
			return $this->get('DescriptiveDetail/ProductFormDetail')[0]->nodeValue;
		} else {
			return $this->get('ProductFormDetail')[0]->nodeValue;
		}
	}

	public function getProductFormFeature()
	{
	    if($this->version >= '3.0') {
			return $this->get('DescriptiveDetail/ProductFormFeature', 'ProductFormFeature');
		}
		return null;
	}

	/**
	 * Get EpubTechnicalProtection
	 *
	 * List 144
	 *
	 * @return string
	 */
	public function getEpubTechnicalProtection()
	{
		if($this->version >= '3.0') {
			return $this->get('DescriptiveDetail/EpubTechnicalProtection')[0]->nodeValue;
		} else {
			return $this->get('EpubTechnicalProtection')[0]->nodeValue;
		}
	}

	/**
	* Get Languages
	*
	* @return array of Language objects
	*/
	public function getLanguages()
	{
		if($this->version >= '3.0') {
			return $this->get('DescriptiveDetail/Language', 'Language')[0];
		} else {
			return $this->get('Language');
		}
	}

	/**
	* Get Language of Text
	*
	* @return string Language code
	*/
	public function getLanguageOfText()
	{
		if ($this->version >= '3.0') {
			// unknown implementation
		} else {
			$languages = $this->getLanguages();

			foreach ($languages as $language) {
				if ($language->getRole() === Language::ROLE_TEXT) {
					return $language->getCode();
				}
			}
		}

		return null;
	}

	/**
	* Get Audience Codes
	*
	* @return array of Audience Codes
	*/
	public function getAudienceCodes()
	{
		if ($this->version >= '3.0') {
			// unknown implementation
		} else {
			$audienceCodes = [];
			$codes = $this->get('AudienceCode');
			
			if (count($codes)) {
				// With 'AudienceCode' tags
				foreach ($codes as $code) {
					$audienceCodes[] = $code->nodeValue;
				}
			} else {
				// With 'Audience' short tags
				$codes = $this->get('Audience');

				foreach ($codes as $code) {
					if ($code->getType() === Audience::ONIX_AUDIENCE_CODE) {
						$audienceCodes[] = $code->getValue();
					}
				}
			}

			return $audienceCodes;
		}
	}

	/**
	* Get Audience Ranges
	*
	* @return array of Audience Ranges
	*/
	public function getAudienceRanges()
	{
		if ($this->version >= '3.0') {
			// unknown implementation
		} else {
			$audienceRanges = [];
			$ranges = $this->get('AudienceRange');
			
			foreach ($ranges as $range) {
				$audienceRanges[] = [
					'qualifier' => $range->getQualifier(),
					'precisions' => $range->getPrecisions(),
					'values' => $range->getValues()
				];
			}

			return $audienceRanges;
		}
	}

	/**
	* Get Cover image link & date
	*
	* @return object of Cover Image 
	*/
	public function getCoverImage()
	{
		if ($this->version >= '3.0') {
			// implementation unknown
		} else {
			$mediaFile = $this->get('MediaFile');

			if (count($mediaFile)) {
				return [
					'url' => $mediaFile[0]->getUrl(),
					'date' => $mediaFile[0]->getDate(),
				];
			}
		}
	}

	/**
	* Get Measures
	*
	* @return array of Measure objects
	*/
	public function getMeasures()
	{
		if ($this->version >= '3.0') {
			// implementation unknown
		} else {
			$measures = null;
			$measureNodes = $this->get('Measure');

			if (count($measureNodes)) {
				$height = null;
				$width = null;
				$thickness = null;
				$weight = null;
				
				foreach ($measureNodes as $measureNode) {
					$measureType = $measureNode->getType();
					$measureUnit = $measureNode->getUnit();
					$measureValue = $measureNode->getValue();
					switch ($measureType) {
						case Measure::TYPE_HEIGHT:
							$height = [
								'value' => $measureValue,
								'unit' => $measureUnit,
							];

							break;
						case Measure::TYPE_WIDTH:
							$width = [
								'value' => $measureValue,
								'unit' => $measureUnit,
							];

							break;
						case Measure::TYPE_THICKNESS:
							$thickness = [
								'value' => $measureValue,
								'unit' => $measureUnit,
							];

							break;
						case Measure::TYPE_WEIGHT:
							$weight = [
								'value' => $measureValue,
								'unit' => $measureUnit,
							];
					}
				}

				$measures = [
					'height' => $height,
					'width' => $width,
					'thickness' => $thickness,
					'weight' => $weight,
				];
			}

			return $measures;
		}
	}

	/**
	* Get Titles
	*
	* @return array of Title objects
	*/
	public function getTitles(){
		if( $this->version >= '3.0' )
			return $this->get('DescriptiveDetail/TitleDetail', 'Title');
		else
			return $this->get('Title');
	}

	/**
	* Is Part of Series?
	*
	 * Retrieve whether the product is part of a series.
	*/
	public function isNotPartOfSeries() {
		if ($this->version >= '3.0') {
			// unknown implementation
		} else {
			return $this->get('NoSeries');
		}
	}

	/**
	* Get Series
	*
	* @return array of Series objects
	*/
	public function getSeries() {
		if ($this->isNotPartOfSeries()) {
			return null;
		} else {
			if ($this->version >= '3.0') {
				// unknown implementation
			} else {
				$series = $this->get('Series')[0];

				return [
					'titleOfSeries' => $series->getTitleOfSeries(),
					'numberWithinSeries' => $series->getNumberWithinSeries(),
				];
			}
		}
	}

	public function getPublisher()
	{
		if($this->version >= '3.0') {
			return $this->get('PublishingDetail/Publisher', 'Publisher');
		}
		return null;
	}

	/**
	* Get Contributors
	*
	* @return array of Contributor objects
	*/
	public function getContributors(){
		if( $this->version >= '3.0' )
			return $this->get('DescriptiveDetail/Contributor', 'Contributor');
		else
			return $this->get('Contributor');
	}

	/**
	* Get Supply Details
	*
	* @return array of SupplyDetail objects
	*/
	public function getSupplyDetails(){
		if( $this->version >= '3.0' )
			return $this->get('ProductSupply/SupplyDetail', 'SupplyDetail');
		else
			return $this->get('SupplyDetail');
	}

	/**
	* Get Sales Rights
	*
	* @return array of SalesRights objects
	*/
	public function getSalesRights(){
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/SalesRights', 'SalesRights');
		else
			return $this->get('SalesRights');
	}

	/**
	* Get For Sale Rights
	*
	* @return string Region of list of countries this product is for sale in
	*/
	public function getForSaleRights(){

		$sales_rights = $this->getSalesRights();
		$rights = '';

		if( count($sales_rights) == 1 ){
			$rights = $sales_rights[0]->getValue();
		}else{

			foreach($sales_rights as $sr){
				if( $sr->isForSale() )
					$rights .= ' '.$sr->getValue();
			}

			$rights = trim($rights);
			$rights = explode(' ', $rights);
			sort($rights);
			$rights = implode(' ', $rights);
		}

		return $rights;
	}

	/**
	* Get Texts
	*
	* @return array of OtherText objects
	*/
	public function getTexts(){
		if( $this->version >= '3.0' )
			return $this->get('CollateralDetail/TextContent', 'OtherText');
		else
			return $this->get('OtherText');
	}

	/**
	* Get Main Description
	*
	* If no main description is found, it will return the first in the list,
	* unless `$strict` is set to `true`
	*
	* @return string
	*/
	public function getMainDescription($strict=false){

		$texts = $this->getTexts();
		$description = '';

		foreach($texts as $text){

			if( $text->getType() == OtherText::TYPE_MAIN_DESCRIPTION )
				$description = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];

			elseif( !$description && $strict !== true )
				$description = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];
		}

		return $description;
	}

	/**
	* Get Review Quotes
	*
	* @return array
	*/
	public function getReviewQuotes(){

		$texts = $this->getTexts();
		$quotes = [];

		foreach($texts as $text){
			if( $text->getType() == OtherText::TYPE_REVIEW_QUOTE )
				$quotes[] = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
					'author' => $text->getAuthor(),
					'sourceTitle' => $text->getSourceTitle()
				];
		}

		return $quotes;
	}

	/**
	* Get Promotional Headline
	*
	* @return array
	*/
	public function getPromotionalHeadline()
	{

		$texts = $this->getTexts();
		$promotionalHeadline = null;

		foreach ($texts as $text) {
			if ($text->getType() == OtherText::TYPE_PROMOTIONAL_HEADLINE) {
				$promotionalHeadline = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];
			}
		}

		return $promotionalHeadline;
	}

	/**
	* Get Bio Notes
	*
	* @return array
	*/
	public function getBiographicalNotes()
	{

		$texts = $this->getTexts();
		$notes = null;

		foreach ($texts as $text) {
			if ($text->getType() == OtherText::TYPE_BIOGRAPHICAL_NOTE) {
				$notes = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];
			}
		}

		return $notes;
	}

	/**
	* Get Back Cover Copy
	*
	* @return array
	*/
	public function getBackCoverCopy()
	{

		$texts = $this->getTexts();
		$backCoverCopy = null;

		foreach ($texts as $text) {
			if ($text->getType() == OtherText::TYPE_BACK_COVER_COPY) {
				$backCoverCopy = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];
			}
		}

		return $backCoverCopy;
	}

	/**
	* Get Excerpt
	*
	* @return array
	*/
	public function getExcerpt()
	{

		$texts = $this->getTexts();
		$excerpt = null;

		foreach ($texts as $text) {
			if ($text->getType() == OtherText::TYPE_EXCERPT) {
				$excerpt = [
					'text' => $text->getValue(),
					'format' => $text->getFormat(),
				];
			}
		}

		return $excerpt;
	}

	public function getPrizes(){
		if( $this->version >= '3.0' )
			return $this->get('CollateralDetail/Prize', 'Prize');
		else
			return $this->get('Prize');
	}

	public function getPrizesMinimalData() {
		return array_map(function($award) {
			return $award->getMinimalData();
		}, $this->getPrizes());
	}

	public function getPrizesData(){
		return array_map(function($award){ return $award->getData(); }, $this->getPrizes());
	}


	/**
	* Get Edition
	*
	* @return string
	*/
	public function getEdition(){
		if( $this->version >= '3.0' )
			return $this->get('DescriptiveDetail/EditionType')[0]->nodeValue;
		else
			return $this->get('EditionTypeCode')[0]->nodeValue;
	}

	/**
	* Get Publish Date
	*
	* @return string
	*/
	public function getPublishDate(){
		// @TODO: 3.0 has more data such as `PublishingDateRole` that may need fleshed out
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/PublishingDate/Date')[0]->nodeValue;
		else {
			$publicationDate = $this->get('PublicationDate');

			if (count($publicationDate)) {
				return $publicationDate[0]->nodeValue;
			} else {
				return null;
			}
		}
	}

	/**
	* Get Publish Date
	*
	* @return string
	*/
	public function getFirstImprintName(){
		// @TODO: many imprints can be set, we should support grabbing them all
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/Imprint/ImprintName')[0]->nodeValue;
		else
			return $this->get('Imprint/ImprintName')[0]->nodeValue;
	}

	/**
	* Get First Publisher Name
	*
	* @return string
	*/
	public function getFirstPublisherName(){
		// @TODO: many publishers can be set, we should support grabbing them all
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/Publisher/PublisherName')[0]->nodeValue;
		else
			return $this->get('Publisher/PublisherName')[0]->nodeValue;
	}

	/**
	* Get Copyright Year
	*
	* @return string
	*/
	public function getCopyrightYear(){
		// @TODO: 3.0 has a more robust out `CopyrightStatement` that should probably be used
		if( $this->version >= '3.0' )
			return $this->get('PublishingDetail/CopyrightStatement/CopyrightYear')[0]->nodeValue;
		else{
			$year = $this->get('CopyrightYear')[0]->nodeValue;
			if( !$year ) $year = $this->get('CopyrightStatement/CopyrightYear')[0]->nodeValue;
			return $year;
		}
	}


	/**
	* Get Copyright Statement
	*
	* @return string
	*/
	public function getCopyrightStatement(){

		$prefix = $this->version >= '3.0' ? 'PublishingDetail/CopyrightStatement' : 'CopyrightStatement';

		$name = $this->get($prefix.'/CopyrightOwner/CorporateName')[0]->nodeValue;

		if( !$name )
			$name = $this->get($prefix.'/CopyrightOwner/PersonName')[0]->nodeValue;

		$year = $this->getCopyrightYear();

		return $year.($name?' '.$name:'');
	}

	/**
	 * Get Main Subject BISAC
	 *
	 * @return string Returns the main subject category code
	 */
	public function getMainSubjectBISAC(){
		if( $this->version >= '3.0'){
			$subjects = $this->get('DescriptiveDetail/Subject', 'Subject');
			foreach($subjects as $subject){
				if( $subject->getScheme() == Subject::SCHEME_BISAC_SUBJECT_HEADING ){
					if( $subject->isMainSubject() )
						return $subject->getValue();
				}
			}
		}else{
			return count($this->get('BASICMainSubject')) ? $this->get('BASICMainSubject')[0]->nodeValue : null;
		}
	}

	/**
	 * Get Other Subject BISACs
	 *
	 * @return array Returns array of other "non-main" subject category
	 */
	public function getOtherSubjectBISACs(){
		if ($this->version >= '3.0')
			$subjects = $this->get('DescriptiveDetail/Subject', 'Subject');
		else
			$subjects = $this->get('Subject', 'Subject');

		$others = [];

		foreach($subjects as $subject){
			if( $subject->getScheme() == Subject::SCHEME_BISAC_SUBJECT_HEADING ){
				if( !$subject->isMainSubject() )
					$others[] = $subject->getValue();
			}
		}

		return $others;
	}

	/**
	 * Get Keywords
	 *
	 * @return string Returns the keywords
	 */
	public function getKeywords()
	{
		if ($this->version >= '3.0')
			$subjects = $this->get('DescriptiveDetail/Subject', 'Subject');
		else
			$subjects = $this->get('Subject', 'Subject');

		foreach ($subjects as $subject) {
			if ($subject->getScheme() == Subject::SCHEME_KEYWORDS)
				return $subject->getText();
		}
		return "";
	}

	/**
	 * Get Number of pages
	 *
	 * @return string Returns the number of pages
	 */
	public function getNumberOfPages()
	{
		if ($this->version >= '3.0') {
			// Implementation unknown
		} else {
			return count($this->get('NumberOfPages')) ? $this->get('NumberOfPages')[0]->nodeValue : null;
		}
	}
}

?>
