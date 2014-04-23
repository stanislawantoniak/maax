/*
 * Globalize Culture ne
 *
 * http://github.com/jquery/globalize
 *
 * Copyright Software Freedom Conservancy, Inc.
 * Dual licensed under the MIT or GPL Version 2 licenses.
 * http://jquery.org/license
 *
 * This file was generated by the Globalize Culture Generator
 * Translation: bugs found in this file need to be fixed in the generator
 */

(function( window, undefined ) {

var Globalize;

if ( typeof require !== "undefined" &&
	typeof exports !== "undefined" &&
	typeof module !== "undefined" ) {
	// Assume CommonJS
	Globalize = require( "globalize" );
} else {
	// Global variable
	Globalize = window.Globalize;
}

Globalize.addCultureInfo( "ne", "default", {
	name: "ne",
	englishName: "Nepali",
	nativeName: "नेपाली",
	language: "ne",
	numberFormat: {
		groupSizes: [3,2],
		"NaN": "nan",
		negativeInfinity: "-infinity",
		positiveInfinity: "infinity",
		percent: {
			pattern: ["-n%","n%"],
			groupSizes: [3,2]
		},
		currency: {
			pattern: ["-$n","$n"],
			symbol: "रु"
		}
	},
	calendars: {
		standard: {
			days: {
				names: ["आइतवार","सोमवार","मङ्गलवार","बुधवार","बिहीवार","शुक्रवार","शनिवार"],
				namesAbbr: ["आइत","सोम","मङ्गल","बुध","बिही","शुक्र","शनि"],
				namesShort: ["आ","सो","म","बु","बि","शु","श"]
			},
			months: {
				names: ["जनवरी","फेब्रुअरी","मार्च","अप्रिल","मे","जून","जुलाई","अगस्त","सेप्टेम्बर","अक्टोबर","नोभेम्बर","डिसेम्बर",""],
				namesAbbr: ["जन","फेब","मार्च","अप्रिल","मे","जून","जुलाई","अग","सेप्ट","अक्ट","नोभ","डिस",""]
			},
			AM: ["विहानी","विहानी","विहानी"],
			PM: ["बेलुकी","बेलुकी","बेलुकी"],
			eras: [{"name":"a.d.","start":null,"offset":0}],
			patterns: {
				Y: "MMMM,yyyy"
			}
		}
	}
});

}( this ));
