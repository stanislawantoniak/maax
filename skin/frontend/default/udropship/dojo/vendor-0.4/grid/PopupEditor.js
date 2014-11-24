define([
	"dojo/_base/declare",
	"put-selector/put",
	"dojo/on",
	"dojo/query",
    "dojo/_base/lang",
	"dojo/dom-class"
], function(declare, put, on, query, lang, domClass){
	
	return declare(null, {
		/**
		 * Column instance
		 */
		column: null,
		/**
		 * Parent column if any
		 */
		parentColumn: null,
		/**
		 * Content of widget
		 */
		content: null,
		/**
		 * Grid object
		 */
		gird: null,
		/**
		 * @param {Object} column
		 * @returns {void}
		 */
		constructor: function(column){
			var close, form, self = this;
			this.column = column;
			if(column.parentColumn){
				this.parentColumn = column.parentColumn;
			}else{
				this.parentColumn = column;
			}
			this.content = put("div.editor.hidden");
			
			close = put("a.close", "×");
			on(close, "click", function(){self.close();})
			
			form = put("from");
			on(close, "click", function(){self.close();})
			
			put(this.content, put("h4", {innerHTML: this.parentColumn.label}));
			put(this.content, close);
			put(this.content, form);
			
			
			this.grid = column.grid;
			
			if(!jQuery(this.grid.domNode).find(".dgrid-editors").length){
				jQuery(this.grid.domNode).find(".dgrid-scroller").append(jQuery("<div>").addClass("dgrid-editors"));
			}
			
			jQuery(this.grid.domNode).find(".dgrid-editors").append(this.content);
		},
		/**
		 * 
		 * @param {Object} cell
		 * @returns {void}
		 */
		open: function(cell){
			var cellPos = jQuery(cell.element).position();
			var rowPos = jQuery(cell.element).parents(".dgrid-row").position();
			var setPos = jQuery(cell.element).parents(".dgrid-column-set").position();
			
			jQuery(this.content).css({
				left: cellPos.left + setPos.left + jQuery(cell.element).outerWidth(),
				top: rowPos.top
			})
			domClass.remove(this.content, "hidden");
		},
		/**
		 * @returns {deffred}
		 */
		save: function(){
			// Make save
			this.cancel();
		},
		/**
		 * @returns {void}
		 */
		cancel: function(){
			this.close();
		},
		/**
		 * @returns {void}
		 */
		close: function(){
			domClass.add(this.content, "hidden");
		},
		/**
		 * @returns {undefined}
		 */
		isOpen: function(){
			return !domClass.contains(this.content, "hidden");
		},
		/**
		 * @returns {void}
		 */
		_registerEvents: function(){
			
		},
		/**
		 * @returns {string}
		 */
		_generateSelect: function(){
			
		},
		/**
		 * @returns {string}
		 */
		_generateMultiselect: function(){
			
		},
		/**
		 * @returns {string}
		 */
		_generateText: function(){
			
		}
	});
	
});

