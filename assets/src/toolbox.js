/**
 * TODO: write doc
 *
 * @param angle
 * @returns {number}
 */
var SEXtoDEC = function(angle) {
	var deg = parseInt(angle);
	var min = parseInt((angle-deg)*100);
	var sec = (((angle-deg)*100) - min) * 100;

	// Result in degrees sex (dd.mmss)
	return deg + (sec/60 + min)/60;
};

/**
 * TODO: write doc
 *
 * @param angle
 * @returns {number}
 */
var DECtoSEX = function(angle) {
	var deg = parseInt(angle);
	var min = parseInt((angle-deg)*60);
	var sec =  (((angle-deg)*60)-min)*60;

	// Result in degrees sex (dd.mmss)
	return deg + min/100 + sec/10000;
};

/**
 * TODO: write doc
 *
 * @param angle
 * @returns {number}
 */
var DEGtoSEC = function(angle) {
	var deg = parseInt(angle);
	var min = parseInt((angle-deg)*100);
	var sec = (((angle-deg)*100) - min) * 100;

	// Result in degrees sex (dd.mmss)
	return sec + min*60 + deg*3600;
};

/**
 * Takes either a lat or long and converts it to human readable
 * DMS-format. This method is for internal use only. Use `WGSDecToDmsLat`
 * or `WGSDecToDmsLng`.
 *
 * @param {number} latOrLng the lat or long
 * @returns {string} the dms-string
 */
var WGSDecToDmsValue = function(latOrLng) {
	var deg = Math.floor(Math.abs(latOrLng));
	var min = (Math.abs(latOrLng) - deg) * 60;
	var sec = (min - Math.floor(min)) * 60;

	return deg + '° ' + Math.floor(min) + '\' ' + sec.toFixed(2) + '"';
};

/**
 * Converts a decimal WGS-84-coordinate into DMS-format
 *
 * @param {number} lat the lat value
 * @param {number} lng the lng value
 * @returns {{lat: string, lng: string}}
 */
var WGSDecToDms = function(lat, lng) {
	return {
		lat: (lat > 0 ? 'N' : 'S') +  ' ' + WGSDecToDmsValue(lat),
		lng: (lng > 0 ? 'E' : 'W') + ' ' + WGSDecToDmsValue(lng)
	};
};

/**
 * Converts a WGS84 coordinate into Swissgrid CH1903
 *
 * @param {number} lat
 * @param {number} lng
 * @returns {{x: number, y: number}}
 */
var WGStoCH1903 = function(lat, lng) {
	lat = DECtoSEX(lat);
	lng = DECtoSEX(lng);

	var lat_aux = (lat - 169028.66)/10000;
	var lng_aux = (lng - 26782.5)/10000;

	var y = 600072.37
			+ 211455.93 * lng_aux
			-  10938.51 * lng_aux * lat_aux
			-      0.36 * lng_aux * Math.pow(lat_aux,2)
			-     44.54 * Math.pow(lng_aux,3);

	var x = 200147.07
			+ 308807.95 * lat_aux
			+   3745.25 * Math.pow(lng_aux,2)
			+     76.63 * Math.pow(lat_aux,2)
			-    194.56 * Math.pow(lng_aux,2) * lat_aux
			+    119.79 * Math.pow(lat_aux,3);

	return {
		y: y,
		x: x
	};
};

/**
 * Converts a Swissgrid CH1903-coordinate into WGS84
 *
 * @param {number} y
 * @param {number} x
 * @returns {{lat: number, lng: number}}
 */
var CH1903toWGS = function(y, x) {
	var y_aux = (y - 600000)/1000000;
	var x_aux = (x - 200000)/1000000;

	var lat = 16.9023892
			+  3.238272 * x_aux
			-  0.270978 * Math.pow(y_aux,2)
			-  0.002528 * Math.pow(x_aux,2)
			-  0.0447   * Math.pow(y_aux,2) * x_aux
			-  0.0140   * Math.pow(x_aux,3);

	var lng = 2.6779094
			+ 4.728982 * y_aux
			+ 0.791484 * y_aux * x_aux
			+ 0.1306   * y_aux * Math.pow(x_aux,2)
			- 0.0436   * Math.pow(y_aux,3);

	return {
		lat: lat * 100/36,
		lng: lng * 100/36
	};
};
