(function($) {
	$(function() {
		$('.GeocodableField').livequery(function() {

			$(this).each(function() {

				var $this = $(this);

				google.load("maps", "3", {other_params:'sensor=false', callback: function(){
				  	var LatField = $this.find(".fieldgroupFieldGeocodableLat input");
					var LngField = $this.find(".fieldgroupFieldGeocodableLng input");
					var ManuallySetField = $this.find(".fieldgroupFieldGeocodableIsManuallySet input");
					
					var DefaultLat = $this.find(".GeocodableFieldOptionsDefaultLat").text();
					var DefaultLng = $this.find(".GeocodableFieldOptionsDefaultLon").text();
					var ShouldUseStartLatLng = $this.find(".GeocodableFieldOptionsShouldUseStartLatLng").text();
					var ZoomVal = $this.find(".GeocodableFieldOptionsZoom").text();

					if( parseInt(ZoomVal) ) {
						ZoomVal = parseInt(ZoomVal);
					} else {
						ZoomVal = 1;
					}

					var Lat = LatField.val();
					var Lng = LngField.val();
					if( !Lat && !Lng ) {
						Lat = DefaultLat;
						Lng = DefaultLng;
					}

					var LatLng = new google.maps.LatLng(Lat, Lng);

					var options = {
						zoom: ZoomVal,
						center: LatLng,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						streetViewControl: false
					};

					var map = new google.maps.Map($this.find('.fieldgroupFieldGeocodableMap')[0], options);

					var marker = new google.maps.Marker({
						map: map,
						draggable: true,
						position: LatLng
					});

					google.maps.event.addListener(marker, 'dragend', function() {
						var position = marker.getPosition();
						LatField.val(position.lat());
						LngField.val(position.lng());
	 				});

					LatField.blur(function() {
						var NewLatLng = new google.maps.LatLng( LatField.val(), LngField.val());
						map.setCenter(NewLatLng);
						marker.setPosition(NewLatLng);
					});
					LngField.blur(function() {
						var NewLatLng = new google.maps.LatLng( LatField.val(), LngField.val());
						map.setCenter(NewLatLng);
						marker.setPosition(NewLatLng);
					});

					ManuallySetField.click(function() {
						if(jQuery(this).is(':checked')) {
							jQuery(this).closest('.GeocodableField').find('.fieldgroupFieldGeocodableIsManuallySetOptions').removeClass('GeocodableFieldInactive');
						} else {
							jQuery(this).closest('.GeocodableField').find('.fieldgroupFieldGeocodableIsManuallySetOptions').addClass('GeocodableFieldInactive');
						}
						marker.setDraggable(jQuery(this).is(':checked'));
					}).each(function() {
						if(jQuery(this).is(':checked')) {
							jQuery(this).closest('.GeocodableField').find('.fieldgroupFieldGeocodableIsManuallySetOptions').removeClass('GeocodableFieldInactive');
						} else {
							jQuery(this).closest('.GeocodableField').find('.fieldgroupFieldGeocodableIsManuallySetOptions').addClass('GeocodableFieldInactive');
						}
						marker.setDraggable(jQuery(this).is(':checked'));
					});


					jQuery(".tabstrip").click(function() {
						google.maps.event.trigger(map, 'resize');
						map.setZoom( map.getZoom() );
						map.setCenter( marker.getPosition() );
					});

					jQuery(document).ready(function() {
						google.maps.event.trigger(map, 'resize');
						map.setZoom( map.getZoom() );
						map.setCenter( marker.getPosition() );
					});
				}});

			});
		});
	});
})(jQuery);