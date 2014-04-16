/*
 * Globalize Culture bo
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

Globalize.addCultureInfo( "bo", "default", {
	name: "bo",
	englishName: "Tibetan",
	nativeName: "བོད་ཡིག",
	language: "bo",
	numberFormat: {
		groupSizes: [3,0],
		"NaN": "ཨང་ཀི་མིན་པ།",
		negativeInfinity: "མོ་གྲངས་ཚད་མེད་ཆུང་བ།",
		positiveInfinity: "ཕོ་གྲངས་ཚད་མེད་ཆེ་བ།",
		percent: {
			pattern: ["-n%","n%"],
			groupSizes: [3,0]
		},
		currency: {
			pattern: ["$-n","$n"],
			groupSizes: [3,0],
			symbol: "¥"
		}
	},
	calendars: {
		standard: {
			firstDay: 1,
			days: {
				names: ["གཟའ་ཉི་མ།","གཟའ་ཟླ་བ།","གཟའ་མིག་དམར།","གཟའ་ལྷག་པ།","གཟའ་ཕུར་བུ།","གཟའ་པ་སངས།","གཟའ་སྤེན་པ།"],
				namesAbbr: ["ཉི་མ།","ཟླ་བ།","མིག་དམར།","ལྷག་པ།","ཕུར་བུ།","པ་སངས།","སྤེན་པ།"],
				namesShort: ["༧","༡","༢","༣","༤","༥","༦"]
			},
			months: {
				names: ["སྤྱི་ཟླ་དང་པོ།","སྤྱི་ཟླ་གཉིས་པ།","སྤྱི་ཟླ་གསུམ་པ།","སྤྱི་ཟླ་བཞི་པ།","སྤྱི་ཟླ་ལྔ་པ།","སྤྱི་ཟླ་དྲུག་པ།","སྤྱི་ཟླ་བདུན་པ།","སྤྱི་ཟླ་བརྒྱད་པ།","སྤྱི་ཟླ་དགུ་པ།","སྤྱི་ཟླ་བཅུ་པོ།","སྤྱི་ཟླ་བཅུ་གཅིག་པ།","སྤྱི་ཟླ་བཅུ་གཉིས་པ།",""],
				namesAbbr: ["ཟླ་ ༡","ཟླ་ ༢","ཟླ་ ༣","ཟླ་ ༤","ཟླ་ ༥","ཟླ་ ༦","ཟླ་ ༧","ཟླ་ ༨","ཟླ་ ༩","ཟླ་ ༡༠","ཟླ་ ༡༡","ཟླ་ ༡༢",""]
			},
			AM: ["སྔ་དྲོ","སྔ་དྲོ","སྔ་དྲོ"],
			PM: ["ཕྱི་དྲོ","ཕྱི་དྲོ","ཕྱི་དྲོ"],
			eras: [{"name":"སྤྱི་ལོ","start":null,"offset":0}],
			patterns: {
				d: "yyyy/M/d",
				D: "yyyy'ལོའི་ཟླ' M'ཚེས' d",
				t: "HH:mm",
				T: "HH:mm:ss",
				f: "yyyy'ལོའི་ཟླ' M'ཚེས' d HH:mm",
				F: "yyyy'ལོའི་ཟླ' M'ཚེས' d HH:mm:ss",
				M: "'ཟླ་' M'ཚེས'd",
				Y: "yyyy.M"
			}
		}
	}
});

}( this ));
