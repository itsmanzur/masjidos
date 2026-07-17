/**
 * MasjidOS Admin - Khutbah Archive Module (compat shim → Minbar).
 */
( function () {
	'use strict';
	// Real UI lives in minbar.js; this file remains for enqueue order safety.
	window.itmms = window.itmms || {};
	window.itmms.khutbah = window.itmms.khutbah || {
		khutbahHtml: function () {
			return window.itmms.minbar && window.itmms.minbar.minbarHtml
				? window.itmms.minbar.minbarHtml()
				: '';
		}
	};
} )();
