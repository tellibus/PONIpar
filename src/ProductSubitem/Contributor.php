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
 * A <Contributor> subitem.
 */
class Contributor extends Subitem {

	// Mapping of constants to roles.
	const ROLE_AUTHOR  		= 'A01';
	const ROLE_NARRATOR     = 'E03';
	const ROLE_READBY       = 'E07';
	const ROLE_PERFORMER    = 'E99';

	/**
	 * The role of this contributor.
	 */
	protected $role = null;

	/**
	 * The sequence number of this contributor.
	 */
	protected $sequenceNumber = null;

	/**
	 * The optional name of this contributor.
	 */
	protected $name = null;

	/**
	 * The optional corporate name of this contributor.
	 */
	protected $corporateName = null;

	/**
	 * The identifier’s value.
	 */
	protected $value = null;

	/**
	 * Create a new Contributor.
	 *
	 * @param mixed $in The <Contributor> DOMDocument or DOMElement.
	 */
	public function __construct($in) {

		parent::__construct($in);

		// Retrieve and check the role.
		$this->role = $this->_getSingleChildElementText('ContributorRole');

		// Get the value.
		$this->value = array();

		$this->value['ContributorRole'] = $this->role;

		try {$this->value['PersonName'] = $this->_getSingleChildElementText('PersonName');} catch(\Exception $e) { }
		try {$this->value['PersonNameInverted'] = $this->_getSingleChildElementText('PersonNameInverted');} catch(\Exception $e) { }
		try {$this->value['SequenceNumber'] = $this->_getSingleChildElementText('SequenceNumber');} catch(\Exception $e) { }
		try {$this->value['NamesBeforeKey'] = $this->_getSingleChildElementText('NamesBeforeKey');} catch(\Exception $e) { }
		try {$this->value['KeyNames'] = $this->_getSingleChildElementText('KeyNames');} catch(\Exception $e) { }
		try {$this->value['CorporateName'] = $this->_getSingleChildElementText('CorporateName');} catch(\Exception $e) { }
		try {$this->value['Bio'] = $this->_getSingleChildElementText('BiographicalNote');} catch(\Exception $e) { }

		if(isset($this->value['Bio']))
			$this->value['Bio'] = $this->clean($this->value['Bio']);

		// Save memory.
		$this->_forgetSource();
	}

	/*
		Get Name
	*/
	public function getName(){

		// already found
		if( $this->name )
			return $this->name();

		if( isset($this->getValue()['PersonName']) )
			return $this->name = $this->getValue()['PersonName'];

		if( isset($this->getValue()['PersonNameInverted']) ){
			return $this->name = preg_replace("/^(.+), (.+)$/", "$2 $1", $this->getValue()['PersonNameInverted']);
		}

		return $this->name;
	}

	/**
	 * Retrieve the role of this contributor.
	 *
	 * @return string The contents of <ContributorRole>.
	 */
	public function getRole() {
		return $this->role;
	}

	/**
	 * Retrieve the SequenceNumber of this contributor.
	 *
	 * @return string The contents of <SequenceNumber>.
	 */
	public function getSequenceNumber()
	{
		// already found
		if ($this->sequenceNumber) {
			return $this->sequenceNumber();
		}

		if (isset($this->getValue()['SequenceNumber'])) {
			return $this->sequenceNumber = $this->getValue()['SequenceNumber'];
		}
	}

	/**
	 * Retrieve the optional Corporate Name of this contributor.
	 *
	 * @return string The contents of <CorporateName>.
	 */
	public function getCorporateName()
	{
		// already found
		if ($this->corporateName) {
			return $this->corporateName();
		}

		if (isset($this->getValue()['CorporateName'])) {
			return $this->corporateName = $this->getValue()['CorporateName'];
		}
	}

	/**
	 * Retrieve the actual value of this identifier.
	 *
	 * @return string The contents of <IDValue>.
	 */
	public function getValue() {
		return $this->value;
	}

	private function clean($str){
		$str = str_replace("<![CDATA[","",$str);
		$str = preg_replace("/\]\]>*$/","",$str);
		return $str;
	}

}
