<?php
/**
 * Adds simple address fields to an object, as well as fields to manage them.
 *
 * This extensions also integrates with the {@link Geocoding} extension to
 * save co-ordinates on object write.
 *
 * @package silverstripe-addressable
 */
class Addressable extends DataObjectDecorator {

	protected static $allowed_states;
	protected static $allowed_countries;
	protected static $postcode_regex= '/^[0-9]+$/';

	protected $allowedStates;
	protected $allowedCountries;
	protected $postcodeRegex;

	/**
	 * Sets the default allowed states for new instances.
	 *
	 * @param null|string|array $states
	 * @see   Addressable::setAllowedStates
	 */
	public static function set_allowed_states($states) {
		self::$allowed_states = $states;
	}

	/**
	 * Sets the default allowed countries for new instances.
	 *
	 * @param null|string|array $countries
	 * @see   Addressable::setAllowedCountries
	 */
	public static function set_allowed_countries($countries) {
		self::$allowed_countries = $countries;
	}

	/**
	 * Sets the default postcode regex for new instances.
	 *
	 * @param string $regex
	 */
	public static function set_postcode_regex($regex) {
		self::$postcode_regex = $regex;
	}

	public function __construct() {
		$this->allowedStates    = self::$allowed_states;
		$this->allowedCountries = self::$allowed_countries;
		$this->postcodeRegex    = self::$postcode_regex;

		parent::__construct();
	}

	public function extraStatics() {
		return array('db' => array(
			'Address1'  => 'Varchar(255)',
			'Address2'  => 'Varchar(255)',
			'City'      => 'Varchar(64)',
			'Region'    => 'Varchar(64)',
			'Postcode'  => 'Varchar(16)',
			'Country'   => 'Varchar(2)'
		));
	}

	public function updateCMSFields($fields) {
		if ($fields->fieldByName('Root.Content')) {
			$tab = 'Root.Content.Address';
		} else {
			$tab = 'Root.Address';
		}

		$fields->addFieldsToTab($tab, $this->getAddressFields());
	}

	public function updateFrontEndFields($fields) {
		foreach ($this->getAddressFields() as $field) $fields->push($field);
	}

	public function populateDefaults() {
		if (is_string($this->allowedStates)) {
			$this->owner->State = $this->allowedStates;
		}

		if (is_string($this->allowedCountries)) {
			$this->owner->Country = $this->allowedCountries;
		}
	}

	/**
	 * @return array
	 */
	protected function getAddressFields() {

		$fields = array(
			new TextField('Address1', _t('Addressable.ADDRESS1', 'Address Line 1')),
			new TextField('Address2', _t('Addressable.ADDRESS2', 'Address Line 2')),
			new TextField('City', _t('Addressable.CITY', 'Town/City'))
		);
	
		$label = _t('Addressable.STATE', 'County/State');
		if (is_array($this->allowedStates)) {
			$fields[] = new DropdownField('Region', $label, $this->allowedStates);
		} elseif (!is_string($this->allowedStates)) {
			$fields[] = new TextField('Region', $label);
		}

		$fields[] = new TextField(
			'Postcode', _t('Addressable.POSTCODE', 'Postcode')
		);

		$label = _t('Addressable.COUNTRY', 'Country');
		if (is_array($this->allowedCountries)) {
			$fields[] = new DropdownField('Country', $label, $this->allowedCountries);
		} elseif (!is_string($this->allowedCountries)) {
			$fields[] = new CountryDropdownField('Country', $label);
		}

		return $fields;
	}

	/**
	 * @return bool
	 */
	public function hasAddress() {

		return (
			$this->owner->Address1
			&& $this->owner->Country
		);

	}

	/**
	 * Returns the full address as a simple string.
	 *
	 * @return string
	 */
	public function getFullAddress() {
		
		$output = array();
		if( $this->owner->Address1 ) {
			$output []= $this->owner->Address1;
		}
		if( $this->owner->Address2 ) {
			$output []= $this->owner->Address2;
		}
		if( $this->owner->City ) {
			$output []= $this->owner->City;
		}
		if( $this->owner->Region ) {
			$output []= $this->owner->Region;
		}
		if( $this->owner->Postcode ) {
			$output []= $this->owner->Postcode;
		}
		if( $this->owner->Country ) {
			$output []= $this->getCountryName();
		}
		return implode(', ', $output);

	}

	public function Address() {
		return $this->getLocalisedFullAddressHTML();
	}

	/**
	 * Returns the full address in a simple HTML template.
	 *
	 * @return string
	 */
	public function getLocalisedFullAddressHTML() {
		return $this->owner->renderWith(array('Address_'.$this->owner->Country,'Address'));
	}

	/**
	 * Returns the full address in a simple HTML template.
	 *
	 * @return string
	 */
	public function getFullAddressHTML() {
		return $this->owner->renderWith('Address');
	}

	/**
	 * Returns a static google map of the address, linking out to the address.
	 *
	 * @param  int $width
	 * @param  int $height
	 * @return string
	 */
	public function AddressMap($width, $height) {
		$data = $this->owner->customise(array(
			'Width'    => $width,
			'Height'   => $height,
			'Address' => rawurlencode($this->getFullAddress())
		));
		return $data->renderWith('AddressMap');
	}

	/**
	 * Returns the country name (not the 2 character code).
	 *
	 * @return string
	 */
	public function getCountryName() {
		return Geoip::countrycode2name($this->owner->Country);
	}

	/**
	 * Returns TRUE if any of the address fields have changed.
	 *
	 * @param  int $level
	 * @return bool
	 */
	public function isAddressChanged($level = 1) {

		$fields  = array('Address1','Address2','City','Region','Postcode','Country');
		$changed = $this->owner->getChangedFields(false, $level);

		foreach ($fields as $field) {
			if (array_key_exists($field, $changed)) return true;
		}

		return false;
	}

	/**
	 * Sets the states that a user can select. By default they can input any
	 * state into a text field, but if you set an array it will be replaced with
	 * a dropdown field.
	 *
	 * @param array $states
	 */
	public function setAllowedStates($states) {
		$this->allowedStates = $states;
	}

	/**
	 * Sets the countries that a user can select. There are three possible
	 * values:
	 *
	 * <ul>
	 *   <li>null: Present a text box to the user.</li>
	 *   <li>string: Set the country to the two letter country code passed, and
	 *       do not allow users to select a country.</li>
	 *   <li>array: Allow users to select from the list of passed countries.</li>
	 * </ul>
	 *
	 * @param null|string|array $states
	 */
	public function setAllowedCountries($countries) {
		$this->allowedCountries = $countries;
	}

	/**
	 * Sets a regex that an entered postcode must match to be accepted. This can
	 * be set to NULL to disable postcode validation and allow any value.
	 *
	 * The postcode regex defaults to only accepting numerical postcodes.
	 *
	 * @param string $regex
	 */
	public function setPostcodeRegex($regex) {
		$this->postcodeRegex = $regex;
	}

}