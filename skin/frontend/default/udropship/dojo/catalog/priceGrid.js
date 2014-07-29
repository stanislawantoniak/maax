define([
	"dgrid/OnDemandGrid",
	"dgrid/Selection",
	"dgrid/Keyboard",
	"dojo/_base/declare",
	"dojo/dom-construct",
	"dojo/on",
	"dojo/query",
	"dojo/store/Memory",
	"dojo/store/Observable",
	"put-selector/put",
	"dojo/text!./resources/description.html",
	"dojo/store/Cache",
	"dojo/store/JsonRest",
    'dgrid/Selection',
    'dgrid/selector'
], function(Grid, Selection, Keyboard, declare, domConstruct, on, query, Memory, Observable, put, descriptionHtml, Cache, JsonRest, Selection, selector){
	
	// Render DOM
	var containerNode = put(document.body, "div"),
	expandedNode,
		gridNode;
		
	var states = {
		collapsed: {},
		checked: {},
		loaded: {}
	}
	
	
	var grid, store, origRenderRow, expandoListener, expandedNode,
		renderer = function(obj, options){
				var div = put("div" + (states.collapsed[obj.entity_id] ? "" : ".collapsed"), Grid.prototype.renderRow.apply(this, arguments)),
					expando = put(div, "div.expando", obj.created_at);
				return div;
			};
			
			
	var testStore = new Observable( new Cache( new JsonRest({
				target:"/udprod/vendor_price/rest", 
				idProperty: "entity_id",
				
				query: function(query, options){
//					// have to manually adjust the query to get rid of the double ?? that trips php up
//					if(query.parent){
//						query = "parent=" + query.parent;
//					}
					return JsonRest.prototype.query.call(this, query, options);
				},
				put: function(object){
					return object;
				}
			}), new Memory()));
	
var SelectionGrid = declare([Grid, Selection]);
	
	grid = new SelectionGrid({
		columns: {
			selector: selector({ label: ''}),
			expander: {
				label: '',
				get: function(item){return "▸"},
				
			},
			name: {
				label: "Name",
				field: "name"
			},
			entity_id: {
				label: "Product Id",
				field: "entity_id"
			},
			sku: { label: "Sku", field: "sku" },
			type: { label: "Type", field: "type_id" }
		},
        selectionMode: 'none',
		minRowsPerPage: 200,
		renderRow: renderer,
		store: testStore
	}, "grid-holder");
	
	
	// listen for clicks to trigger expand/collapse in table view mode
	expandoListener = on.pausable(grid.domNode, ".dgrid-row td:not(.field-selector) :click", function(evt){
		
		var
			row = grid.row(evt),
			node = row.element;
			states.collapsed[row.id] = !states.collapsed[row.id];
			
		query("td.field-expander", evt.toElement.up()).forEach(function(item){
			item.innerHTML = states.collapsed[row.id] ? "&#9662;" : "&#9656";
		}) 
			
		// toggle state of node which was clicked
		put(node, (states.collapsed[row.id] ? "!" : ".") + "collapsed");
		
	});
	
	return grid;
	
	
});