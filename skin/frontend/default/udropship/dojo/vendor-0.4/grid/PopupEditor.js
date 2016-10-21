define([
	"dojo/_base/declare",    // declare
	"put-selector/put",      // put
	"dojo/on",               // on
	"dojo/query",            // query
    "dojo/_base/lang",       // lang
	"dojo/dom-class",        // domClass
	"dojo/dom-style",        // domStyle
	"dojo/NodeList-traverse",// nodeList
	"dojo/Evented",          // Evented
	"dojo/Deferred"          // Deferred
], function(declare, put, on, query, lang, domClass, domStyle, nodeList, Evented, Deferred){
	
	var PLACEMENT_SCROLLER = "dgrid-scroller";
	var EDITOR_CLASS = "dgrid-editors";
	
	return declare([Evented], {
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
		 * Is loading
		 */
		isLoading: false,
		/**
		 * @param {Object} column
		 * @returns {void}
		 */
		constructor: function(column, placement){
			var placementDom,
				self = this,
                parentColumnFieldName;
			
			this.column = column;
			this.grid = column.grid;
			
			if(column.parentColumn){
				this.parentColumn = column.parentColumn;
                parentColumnFieldName = ".editor-field-" + column.parentColumn.field;
			}else{
				this.parentColumn = column;
			}
			
			this.content = put("div.editor.hidden" + parentColumnFieldName);
			
			this._close = put("a.close", "×");
			this._form = this._buildForm();
			this._title = this._buildTitle();
			this._additionalInfo = this._buildAdditionalInfo();
			
			
			put(this.content, this._title);
			put(this.content, this._additionalInfo);
			put(this.content, this._close);
			put(this.content, this._form);
			
			placementDom = query("." + PLACEMENT_SCROLLER + " " + "." + EDITOR_CLASS, this.grid.domNode);
			
			if(!placementDom.length){
				placementDom = put("div."+EDITOR_CLASS);
				put(query("." + PLACEMENT_SCROLLER, this.grid.domNode)[0], placementDom);
			}else{
				placementDom = placementDom[0];
			}
			put(placementDom, this.content);
			this._registerEvents();
		},
		
		/**
		 * @param {Object} cell
		 * @returns {void}
		 */
		open: function(cellObj){
			this.cell = cellObj;

            // Show/hide checkbox 'Apply to selection'
			if(this.canShowUseSelection() && this.grid.isSelected(cellObj.row)){
				domClass.add(this.content, "use-selection");
				this._useSelection.checked = true;
			}else{
				domClass.remove(this.content, "use-selection");
			}

            // Show/hide checkbox 'Save as rule'
            if(this.canShowSaveAsRule() 
				&& this.grid.isSelected(cellObj.row)
				&& (this.parentColumn.type=="multiselect" 
					|| this.parentColumn.type=="options")
				
			){
                domClass.add(this.content, "use-save-as-rule");
            }else{
                domClass.remove(this.content, "use-save-as-rule");
            }


			if(this.parentColumn.type!="multiselect") {
                var value = cellObj.row.data[this.column.field];
                if (this.parentColumn.htmlspecialcharsDecode) {
                    value = jQuery('<textarea>').html(value).text(); // Trick for htmlspecialchars_decode
                }
				this.setValue(value);
			}else{
				this.setValue(null);
			}
			if(this.column.field == "name"){
				this.showSku(cellObj.row.data.skuv);
			}
			domStyle.set(
				this.content, 
				this._getEditorPosition(cellObj, this.content)
			);

			domClass.remove(this.content, "hidden");
			this.grid.focus(this.cell);
			this.getField().focus();
		},
		/**
		 * @param {mixed} value
		 * @returns {this}
		 */
		setValue: function(value){
			var field = this.getField(),
				parentColumn = this.parentColumn;
			
			try{
				if(parentColumn.type=="multiselect" && value !== null){
					value = value.split(",");
					query("option", field).forEach(function(el){
						el.selected = value.indexOf(el.value)>-1;
					});
					return;
				}
				if(value === null){
					value = "";
				}
				field.value = value;
			}catch(e){
					
			}
			
		},

		showSku: function (skuv) {
			var skuObj = put("p", {
				innerHTML: 'SKU: ' + skuv
			});
			try {
				jQuery(this._additionalInfo).html(skuObj);
			} catch (e) {

			}
		},
		/**
		 * @returns {String}
		 */
		getValue: function(){
			var value = this.getField().value;
			if(this.parentColumn.type=="multiselect"){
				value = [];
				query("option", this.getField()).forEach(function(el){
					if(el.selected){
						value.push(el.value);
					}
				});
				value = value.join(",");
			}
			return value;
		},
		/**
		 * @returns {String}
		 */
		getMode: function(){
			var checked = query("[name='mode']:checked", this.content);
			if(checked.length){
				return checked[0].value;
			}
			return '';
		},
		/**
		 * @returns {Object}
		 */
		getField: function(){
			return query("[name='attribute_value']", this.content)[0];
		},
		/**
		 * @returns {deffred}
		 */
		save: function(){
			// Make save
			var self = this;
			var deferred = new Deferred();
			
			deferred.then(function(){
				self.close(true);
				self._stopLoading();
			}, function(){
				self._stopLoading();
			});
			
			this._startLoading();
			
			this.emit("save", {
				value: this.getValue(),
				mode: this.getMode(),
				useSelection: this.getUseSelection(),
                useSaveAsRule: this.getUseSaveAsRule(),
				field: this.column.field,
				column: this.column,
				cell: this.cell,
				row: this.cell.row,
				id: this.cell.row.id,
				deferred: deferred
			});
		},
		/**
		 * @returns {void}
		 */
		cancel: function(){
			this.close(true);
		},
		/**
		 * @returns {void}
		 */
		close: function(doFocus){
			domClass.add(this.content, "hidden");
			if(doFocus){
				this.grid.focus(this.cell);
			}
		},
		/**
		 * @returns {undefined}
		 */
		isOpen: function(){
			return !domClass.contains(this.content, "hidden");
		},

		/**
		 * @returns {Boolean}
		 */
		getCheckAll: function(){
			return !!query("th.dgrid-selector input[aria-checked='true']", this.grid.domNode).length;
		},

        /**
         * Check if check-all is selected (so ids should be)
         *
         * @returns {Boolean|boolean}
         * @private
         */
        _canShowByCheckAllAndSelectedIds: function() {
            return this.getCheckAll() || (
            typeof this.grid.getSelectedIds == "function" &&
            this.grid.getSelectedIds().length > 1
            );
        },

        /**
         * Return array of blocked elements for mass actions
         * Common use in use-selection or use-save-as-rule
         *
         * @returns {Array}
         */
        getBlockedFieldForMass: function() {
            var attr = [];
            attr.push('name');// Product name attr
            return attr;
        },

        /**
         * Return true if attr is blocked for mass actions
         * @returns {boolean}
         */
        isAttrNotBlockedForMass: function() {
            return this.getBlockedFieldForMass().indexOf(this.column.parentColumn.field) != -1;
        },

        /**
         * @returns {Boolean|boolean}
         */
		canShowUseSelection: function(){
			return this._canShowByCheckAllAndSelectedIds() && !this.isAttrNotBlockedForMass();
		},

        /**
         * @returns {Boolean|boolean}
         */
        canShowSaveAsRule: function() {
            return this._canShowByCheckAllAndSelectedIds() && !this.isAttrNotBlockedForMass();
        },

		/**
		 * @returns {Bool}
		 */
		getUseSelection: function(){
			return this.canShowUseSelection() && 
				domClass.contains(this.content, "use-selection") && 
				this._useSelection.checked;
		},

        /**
         * @returns {Boolean|boolean|*}
         */
        getUseSaveAsRule: function() {
            return this.canShowSaveAsRule() &&
                domClass.contains(this.content, "use-save-as-rule") &&
                this._saveAsRule.checked;
        },

		/**
		 * @returns {void}
		 */
		_registerEvents: function(){
			var self = this;
			
			on(this._close, "click", function(){
				if(!self.isLoading){
					self.close(true);
				}
			});
			
			on(this._form, "submit", function(e){
				self.save(); 
				e.preventDefault();
			});
		},
		/**
		 * @returns {string}
		 */
		_generateText: function(form, column, parentColumn){
			var section = put("div.form-group");
			var text = put("input", {
				type: "text", 
				name: "attribute_value", 
				className: "form-control"
			});
			put(section, text);
			put(form, section);
		},
		/**
		 * @returns {string}
		 */
		_generateTextarea: function(form, column, parentColumn){
			var section = put("div.form-group");
			var text = put("textarea", {
				name: "attribute_value", 
				className: "form-control"
			});
			put(section, text);
			put(form, section);
		},
		/**
		 * @returns {string}
		 */
		_generateSelect: function(form, column, parentColumn){
			var section = put("div.form-group");
			var select = this._generateBasicSelect(column, parentColumn);
			domClass.add(select, "form-control");
			put(section, select);
			put(form, section);
		},
		/**
		 * @returns {string}
		 */
		_generateMultiselect: function(form, column, parentColumn){
			var sectionSelect = put("div.form-group");
			var sectionMode = put("div.form-group");
			
			// Select
			var select = this._generateBasicSelect(column, parentColumn);
			put(select, {"multiple": true});
			domClass.add(select, "multiple");
			put(sectionSelect, select);
			
			// Mode
			put(sectionMode, this._generateMode("Add", "add", parentColumn.field, true));
			put(sectionMode, this._generateMode("Set", "set", parentColumn.field));
			put(sectionMode, this._generateMode("Substract", "sub", parentColumn.field));
			
			// Append
			put(form, sectionSelect);
			put(form, sectionMode);
		},
		/**
		 * 
		 * @param {Object} column
		 * @returns {unresolved}
		 */
		_generateBasicSelect: function(column, parentColumn){
			var options = column.editOptions || [],
				select;
			
			select = put("select", {
				name: "attribute_value", 
				className: "select"
			});
			
			if(!parentColumn.required && parentColumn.type!="multiselect"){
				put(select, put("option", {
					value: "",
					innerHTML: ""
				}));
			}
			
			options.forEach(function(opt){
				put(select, put("option", {
					value: opt.value,
					innerHTML: opt.label
				}));
			});
			
			return select;
		},
		
		/**
		 * @param {string} label
		 * @param {string} mode
		 * @param {string} field
		 * @returns {Object}
		 */
		_generateMode: function(label, mode, field, checked){
			var id = field + "-" + mode,
				node = put("label.radio", {
				"for": id
			});
			label = Translator.translate(label);
			put(node, put("input", {
				type: "radio",
				name: "mode",
				value: mode,
				id:	id,
				checked: !!checked
			}));
			put(node, put("span", " " +label));
			return node;
		},
		
		/**
		 * @returns {undefined}
		 */
		_buildForm: function(){
			var id,
                checkboxes = put("div.form-group"), // Checkboxes div
                labelForSelection,
                labelForSaveAsRule,
				form = put("form", {action: ""});

			switch(this.parentColumn.type){
				case "select":
				case "options":
					this._generateSelect(form, this.column, this.parentColumn);
				break;
				case "multiselect":
					this._generateMultiselect(form, this.column, this.parentColumn);
				break;
				case "textarea":
					this._generateTextarea(form, this.column, this.parentColumn);
				break;
				default: 
					this._generateText(form, this.column, this.parentColumn);
			}


            // Checkbox for: Apply to selection
            id = this.parentColumn.field + "-selection";
			labelForSelection = put("label", {
				"for": id,
				className: "checkbox selection",
				innerHTML: Translator.translate("Apply to selection")
			});
			this._useSelection = put("input", {
				type: "checkbox", 
				name: "selection", 
				id: id,
				value: 1,
				checked: true
			});
            put(labelForSelection, this._useSelection);
            put(checkboxes, labelForSelection);

            // Checkbox for: Save as rule
            id = this.parentColumn.field + "-saveAsRule";
            labelForSaveAsRule= put("label", {
                "for": id,
                className: "checkbox save-as-rule",
                innerHTML: Translator.translate("Save as rule")
            });
            this._saveAsRule = put("input", {
                type: "checkbox",
                name: "saveAsRule",
                id: id,
                value: 1,
                checked: false
            });
            put(labelForSaveAsRule, this._saveAsRule);
            put(checkboxes, labelForSaveAsRule);

            // Put checkboxes to this new form
			put(form, checkboxes);

			// Submit
			this._formSubmit = put("input", {
				"type": "submit", 
				value: Translator.translate("Submit"), 
				className: "btn btn-primary"
			});
            this._formSubmit.setAttribute("data-loading-text", Translator.translate("Processing..."));
			put(form, this._formSubmit);
			
			return form;
		},
		
		/**
		 * @returns {Object}
		 */
		_buildTitle: function(){
			var title = put("h4");
			put(title, put("span", this.parentColumn.label));
			if(this.parentColumn.required){
				put(title, put("span.required", " *"));
			}
			return title;
		},

		_buildAdditionalInfo: function(){
			var additioalInfoContainer = put("div", {
				id: "additional-info"
			});
			return additioalInfoContainer;
		},
		
		/**
		 * @param {Object} cellObj
		 * @returns {Object}
		 */
		_getEditorPosition: function(cellObj, content){
			
			var cell = cellObj.element;
			var row = query(cell).parents(".dgrid-row")[0];
			var scroller = query(cell).parents(".dgrid-scroller")[0];
			var set = query(cell).parents(".dgrid-column-set")[0];
			var table = query(cell).parents(".dgrid-row-table")[0];
			var left = cell.offsetLeft + set.offsetLeft - set.scrollLeft;
			var top = row.offsetTop;
			
			domClass.remove(content, "hidden");
			
			if((cell.offsetLeft - set.scrollLeft) / set.offsetWidth > 0.5 || 
				left+cell.offsetWidth>set.offsetWidth){
			
				left -= content.offsetWidth;
			}else{
				left += cell.offsetWidth;
			}
			
			if((row.offsetTop - scroller.scrollTop)/scroller.offsetHeight > 0.5){
				
				top -= content.offsetHeight - row.offsetHeight;
			}
			domClass.add(content, "hidden");
			
			return  {
				left: left + "px",
				top: top + "px"
			};
		},
		/**
		 * @returns {void}
		 */
		_startLoading: function(){
			query("input,select,textarea", this._form).forEach(function(el){
				el.disabled = true;
			});
			this.isLoading = true;
			jQuery(this._close).addClass("disabled");
			jQuery(this._formSubmit).button('loading');
		},
		/**
		 * @returns {void}
		 */
		_stopLoading: function(){
			query("input,select,textarea", this._form).forEach(function(el){
				el.disabled = false;
			});
			this.isLoading = false;
			jQuery(this._close).removeClass("disabled");
			jQuery(this._formSubmit).button('reset');
		}
	});
	
});

