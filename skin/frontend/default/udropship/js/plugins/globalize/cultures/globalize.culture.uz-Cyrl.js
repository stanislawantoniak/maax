/*
 * Globalize Culture uz-Cyrl
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

Globalize.addCultureInfo( "uz-Cyrl", "default", {
	name: "uz-Cyrl",
	englishName: "Uzbek (Cyrillic)",
	nativeName: "Ўзбек",
	language: "uz-Cyrl",
	numberFormat: {
		",": " ",
		".": ",",
		percent: {
			pattern: ["-n%","n%"],
			",": " ",
			".": ","
		},
		currency: {
			pattern: ["-n $","n $"],
			",": " ",
			".": ",",
			symbol: "сўм"
		}
	},
	calendars: {
		standard: {
			"/": ".",
			firstDay: 1,
			days: {
				names: ["якшанба","душанба","сешанба","чоршанба","пайшанба","жума","шанба"],
				namesAbbr: ["якш","дш","сш","чш","пш","ж","ш"],
				namesShort: ["я","д","с","ч","п","ж","ш"]
			},
			months: {
				names: ["Январ","Феврал","Март","Апрел","Май","Июн","Июл","Август","Сентябр","Октябр","Ноябр","Декабр",""],
				namesAbbr: ["Янв","Фев","Мар","Апр","Май","Июн","Июл","Авг","Сен","Окт","Ноя","Дек",""]
			},
			monthsGenitive: {
				names: ["январ","феврал","март","апрел","май","июн","июл","август","сентябр","октябр","ноябр","декабр",""],
				namesAbbr: ["Янв","Фев","Мар","Апр","мая","Июн","Июл","Авг","Сен","Окт","Ноя","Дек",""]
			},
			AM: null,
			PM: null,
			patterns: {
				d: "dd.MM.yyyy",
				D: "yyyy 'йил' d-MMMM",
				t: "HH:mm",
				T: "HH:mm:ss",
				f: "yyyy 'йил' d-MMMM HH:mm",
				F: "yyyy 'йил' d-MMMM HH:mm:ss",
				M: "d-MMMM",
				Y: "MMMM yyyy"
			}
		}
	}
});

}( this ));
