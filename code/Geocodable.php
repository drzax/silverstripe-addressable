<?php
/**
 * Adds automatic geocoding to a {@link Addressable} object. Uses the Google
 * Maps API to save latitude and longitude on write.
 *
 * @package silverstripe-addressable
 */
class Geocodable extends DataObjectDecorator {

	public function extraStatics() {
		return array('db' => array(
			'Location' => 'LatLng'
		));
	}

	public function onBeforeWrite() {

		if (!$this->owner->isAddressChanged()) {
			return;
		}

		if ($this->owner->Location->IsManuallySet) {
			return;
		}

		$address = $this->owner->getFullAddress();
		$region  = strtolower($this->owner->Country);

		if(!$point = GoogleGeocoding::address_to_point($address, $region)) {
			return;
		}

		$val = new LatLng('Location');
		$val->Lat = $point['lat'];
		$val->Lng = $point['lng'];
		$val->IsManuallySet = 0;
		$this->owner->Location->setValue($val);

	}

	public function updateCMSFields($fields) {
		if ($fields->fieldByName('Root.Content')) {
			$tab = 'Root.Content.Address';
		} else {
			$tab = 'Root.Address';
		}

		$fields->addFieldsToTab($tab, $this->getGeocoderFields());
	}

	public function getGeocoderFields() {

		$fields = array(
			new GeocodableField( 'Location', 'Location' )
		);
		return $fields;
	}

	public function updateFrontEndFields($fields) {
		$this->updateCMSFields($fields);
	}

}