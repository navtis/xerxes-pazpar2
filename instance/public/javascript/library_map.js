/**
 * leaflet library map
 *
 * @author David Walker
 * @copyright 2011 California State University
 * @link http://xerxes.calstate.edu
 * @license http://www.gnu.org/licenses/
 * @version
 * @package Xerxes
 */

$(document).ready(function(){
	
	var map = new L.Map('map', {attributionControl: false}); 
	
	var osmUrl='http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
	var osm = new L.TileLayer(osmUrl, {minZoom: 5, maxZoom: 18});	

	if ( $(".library-data").length > 1 ){

		// calculate zoom level to cover all libraries
		var points = new Array();
		$(".library-data").each(function(){
			var markerLocation = new L.LatLng($(this).attr("x"), $(this).attr("y"));
			points.push(markerLocation);
		});
		var bounds = new L.LatLngBounds(points);
		map.addLayer(osm);
		// scale to fit markers
		map.fitBounds(bounds); 

	} else {
		// only one library - start close up
		var centre = new L.LatLng($(".library-data").attr("x"), $(".library-data").attr("y"));
		map.setView(centre, 12);
		map.addLayer(osm);
	}

	
	// add a marker for each library
	$(".library-data").each(function(){
		var name = $(this).attr("name");
		var markerLocation = new L.LatLng($(this).attr("x"), $(this).attr("y"));
		var marker = new L.Marker(markerLocation, {title: name}); 
		map.addLayer(marker); 
		marker.bindPopup(name);
	});

});  
 
