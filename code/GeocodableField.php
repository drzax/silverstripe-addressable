<?php

/**
 * A form field which outputs a google map, input field for an address which moves
 * the map.
 *
 * Currently saves the address of a given point. 
 *
 * @todo Save the Long, Lat fields as well
 * @package googlemapselectionfield
 */

class GeocodableField extends FormField {

	/**
	 * @var Mixed
	 */
	private $startLat, $startLong, $mapWidth, $mapHeight, $zoom;

	protected $LatField = null;
	protected $LngField = null;
	protected $IsManuallySetField = null;

	/**
	 * @param String - Name of Field
	 * @param String - Title for Field
	 * @param Int - Start Latitude
	 * @param Int - Starting Map Longitude
	 * @param String - Width of map (px or % to be included)
	 * @param String - Height of map (px or % to be included)
	 * @param Int - Zoom Level (1 to 12)
	 */
	function __construct($name = "", $title = "", $value=null, $startLat = 0, $startLong = 0, $mapWidth = '400px', $mapHeight = '250px', $zoom = '11') {
		if(strpos($mapWidth, 'px') === false && strpos($mapWidth, '%') === false) $mapWidth .= "px";
		if(strpos($mapHeight, 'px') === false || strpos($mapHeight, '%') !== false){
			$mapHeight = str_replace("%","",$mapHeight);
			$mapHeight .= "px";
		}
		parent::__construct($name, $title, $value);
		$this->startLat = $startLat;
		$this->startLong = $startLong;
		$this->mapWidth = $mapWidth;
		$this->mapHeight = $mapHeight;
		$this->zoom = $zoom;

		$this->LatField = new NumericField("{$name}[Lat]", 'Latitude');
		$this->LngField = new NumericField("{$name}[Lng]", 'Longitude');
		$this->IsManuallySetField = new CheckboxField("{$name}[IsManuallySet]", 'Manually set location' );

	}

	function Field() {

		Requirements::javascript("https://www.google.com/jsapi");
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
		Requirements::javascript("addressable/javascript/GeocodableField.js");
		Requirements::css("addressable/css/GeocodableField.css");

		$shouldUseStartLatLng = !($this->value->Lat || $this->value->Lng);

		return "<div class=\"field fieldgroup GeocodableField\">" .
					"<div style=\"display: none\" class=\"GeocodableFieldOptions\">" . 
						'<span class="GeocodableFieldOptionsDefaultLat">' . $this->startLat . '</span>' . 
						'<span class="GeocodableFieldOptionsDefaultLon">' . $this->startLong . '</span>' . 
						'<span class="GeocodableFieldOptionsShouldUseStartLatLng">' . ($shouldUseStartLatLng ? '1' : '') . '</span>' . 
						'<span class="GeocodableFieldOptionsZoom">' . $this->zoom . '</span>' . 
					"</div>" .
					"<div class=\"fieldgroupField fieldgroupFieldGeocodableIsManuallySet\">" . $this->IsManuallySetField->SmallFieldHolder() . "</div>" . 
					"<div class=\"fieldgroupField fieldgroupFieldGeocodableIsManuallySetHelp\"><p>"._t('GeocodableField.MANUALLYSETHELP', 'By ticking this box, you can update the location by dragging and dropping the marker on the map. While this box is ticked, the location will not update automatically when the address changes.')."</p></div>" . 
					"<div class=\"fieldgroupFieldGeocodableIsManuallySetOptions\" style=\"width: $this->mapWidth;\">".
					"<div class=\"fieldgroupField fieldgroupFieldGeocodableLat\">" . $this->LatField->SmallFieldHolder() . "</div>" . 
					"<div class=\"fieldgroupField fieldgroupFieldGeocodableLng\">" . $this->LngField->SmallFieldHolder() . "</div>" . 
					"<div class=\"fieldgroupFieldGeocodableMap\" id=\"GeocodableMap_{$this->name}\" style=\"width: $this->mapWidth; height: $this->mapHeight;\"></div>" .
					"</div>" .
				"</div>";
	}

	function setValue($val) {
		$this->value = $val;
		if ($val instanceof LatLng) {
			$this->LatField->setValue($val->Lat);
			$this->LngField->setValue($val->Lng);
			$this->IsManuallySetField->setValue($val->IsManuallySet);
		}
		if(is_array($val)) {
			$this->LatField->setValue($val['Lat']);
			$this->LngField->setValue($val['Lng']);
			$this->IsManuallySetField->setValue(isset($val['IsManuallySet']) ? 1 : 0);
		}
	}

	/**
	 * Only save if field was shown on the client,
	 * and is not empty.
	 *
	 * @param DataObject $record
	 * @return bool
	 */
	function saveInto(DataObject $record) {

		$name = $this->name;
		$oldval = $record->$name;

		$setManually = !!$this->IsManuallySetField->dataValue();

		$val = new LatLng('Location');
		$val->Lat = $setManually ? $this->LatField->dataValue() : $oldval->Lat;
		$val->Lng = $setManually ? $this->LngField->dataValue() : $oldval->Lng;
		$val->IsManuallySet = $setManually;
		$record->Location->setValue($val);

		return true;

	}

}