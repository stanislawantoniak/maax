define([
	'dojo/_base/declare',
	'dojo/_base/lang',
	'dojo/query',
	"dojo/on",
	"dgrid/ColumnSet",
], function (declare, lang, query, on, ColumnSet) {

	return declare([ColumnSet], {
		/**
		 * Adjust all sets
		 * @returns {undefined}
		 */
		adjustScrollLeft: function(){
			this._columnSetScrollLefts = {};
			query(".dgrid-column-set-scroller").forEach(function(el){
				on.emit(el, 'scroll', {target: el});
			});
		},
		/**
		 * Trigger adjust on ontufication
		 * @returns {void}
		 */
		_onNotification: function(){
			var ret =  this.inherited(arguments);
			this.adjustScrollLeft();
			return ret;
		}
		
	});
});
