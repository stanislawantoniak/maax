/*
 * Globalize Culture smn-FI
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

Globalize.addCultureInfo( "smn-FI", "default", {
	name: "smn-FI",
	englishName: "Sami, Inari (Finland)",
	nativeName: "sämikielâ (Suomâ)",
	language: "smn",
	numberFormat: {
		",": " ",
		".": ",",
		percent: {
			",": " ",
			".": ","
		},
		currency: {
			pattern: ["-n $","n $"],
			",": " ",
			".": ",",
			symbol: "€"
		}
	},
	calendars: {
		standard: {
			"/": ".",
			firstDay: 1,
			days: {
				names: ["pasepeivi","vuossargâ","majebargâ","koskokko","tuorâstâh","vástuppeivi","lávárdâh"],
				namesAbbr: ["pa","vu","ma","ko","tu","vá","lá"],
				namesShort: ["p","v","m","k","t","v","l"]
			},
			months: {
				names: ["uđđâivemáánu","kuovâmáánu","njuhčâmáánu","cuáŋuimáánu","vyesimáánu","kesimáánu","syeinimáánu","porgemáánu","čohčâmáánu","roovvâdmáánu","skammâmáánu","juovlâmáánu",""],
				namesAbbr: ["uđiv","kuov","njuh","cuoŋ","vyes","kesi","syei","porg","čoh","roov","ska","juov",""]
			},
			AM: null,
			PM: null,
			patterns: {
				d: "d.M.yyyy",
				D: "MMMM d'. p. 'yyyy",
				t: "H:mm",
				T: "H:mm:ss",
				f: "MMMM d'. p. 'yyyy H:mm",
				F: "MMMM d'. p. 'yyyy H:mm:ss",
				M: "MMMM d'. p. '",
				Y: "MMMM yyyy"
			}
		}
	}
});

}( this ));
