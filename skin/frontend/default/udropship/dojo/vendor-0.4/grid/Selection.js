define([
	'dojo/_base/declare',
	'dojo/query',
	"dgrid/Selection"
], function (declare, query, Selection) {

	return declare([Selection], {
		getSelectedIds: function(){
			var selected = [],
				k;
			if(!this.selection){
				return [];
			}
			for(k in this.selection){
				if(this.selection[k]){
					selected.push(k);
				}
			}
			return selected;
		},
		getCheckAll: function(){
			return !!query("th.dgrid-selector input[aria-checked='true']", this.domNode).length;
		}
	});
});
