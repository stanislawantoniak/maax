define("dojox/app/controllers/HistoryHash", ["dojo/_base/lang", "dojo/_base/declare", "dojo/topic", "dojo/on", "../Controller", "../utils/hash", "dojo/hash"],
	function(lang, declare, topic, on, Controller, hash){
	// module:
	//		dojox/app/controllers/HistoryHash
	// summary:
	//		Bind "app-domNode" event on dojox/app application instance,
	//		Bind "startTransition" event on dojox/app application domNode,
	//		Bind "/dojo/hashchange" event on window object.
	//		Maintain history by history hash.

	return declare("dojox.app.controllers.HistoryHash", Controller, {

		constructor: function(app){
			// summary:
			//		Bind "app-domNode" event on dojox/app application instance,
			//		Bind "startTransition" event on dojox/app application domNode,
			//		subscribe "/dojo/hashchange" event.
			//
			// app:
			//		dojox/app application instance.
			this.events = {
				"app-domNode": this.onDomNodeChange
			};
			if(this.app.domNode){
				this.onDomNodeChange({oldNode: null, newNode: this.app.domNode});
			}
			topic.subscribe("/dojo/hashchange", lang.hitch(this, function(newhash){
				this._onHashChange(newhash);
			}));

			this._historyStack = []; // application history stack
			this._historyLen = 0;	// current window.history length
			this._historyDiff = 0; // the diff of window.history stack and application history stack 
			this._current = null;	// current history item in application history stack
			this._next = null;		// next history item in application history stack
			this._previous = null;	// previous history item in application history stack
			this._index = 0;		// identify current history item's index in application history stack
			this._oldHistoryLen = 0;// window.history stack length before hash change
			this._newHistoryLen = 0;// window.history stack length after hash change
			this._addToHistoryStack = false;
			this._startTransitionEvent = false;

			// push the default page to the history stack
			var currentHash = window.location.hash;
			if(currentHash && (currentHash.length > 1)){
				currentHash = currentHash.substr(1);
			}
			this._historyStack.push({
				"hash": currentHash,
				"url": window.location.href,
				"detail": {target:currentHash}
			});
			this._historyLen = window.history.length;
			this._index = this._historyStack.length - 1;
			this._current = currentHash;

			// get the diff of window.history and application history
			this._historyDiff = window.history.length - this._historyStack.length;
		},

		onDomNodeChange: function(evt){
			if(evt.oldNode != null){
				this.unbind(evt.oldNode, "startTransition");
			}
			this.bind(evt.newNode, "startTransition", lang.hitch(this, this.onStartTransition));
		},

		onStartTransition: function(evt){
			// summary:
			//		Response to dojox/app "startTransition" event.
			//
			// example:
			//		Use "dojox/mobile/TransitionEvent" to trigger "startTransition" event, and this function will response the event. For example:
			//		|	var transOpts = {
			//		|		title:"List",
			//		|		target:"items,list",
			//		|		url: "#items,list"
			//		|		params: {"param1":"p1value"}
			//		|	};
			//		|	new TransitionEvent(domNode, transOpts, e).dispatch();
			//
			// evt: Object
			//		transition options parameter

			var target = evt.detail.target;
			var regex = /#(.+)/;
			if(!target && regex.test(evt.detail.href)){
				target = evt.detail.href.match(regex)[1];
			}

			var currentHash = evt.detail.url || "#" + target;

			if(evt.detail.params){
				currentHash = hash.buildWithParams(currentHash, evt.detail.params);
			}

			this._oldHistoryLen = window.history.length;
			// pushState on iOS will not change location bar hash because of security.
			// window.history.pushState(evt.detail, title, currentHash);

			// history.length will be changed by set location hash
			// change url hash, to workaround iOS pushState not change address bar issue.
			window.location.hash = currentHash;

			// The operation above will trigger hashchange.
			// Use _addToHistoryStack flag to indicate the _onHashChange method should add this hash to history stack.
			// When add hash to history stack, this flag should be set to false, we do this in _addHistory.
			this._addToHistoryStack = true;
			//set startTransition event flag to true if the hash change from startTransition event.
			this._startTransitionEvent = true;
		},

		_addHistory: function(hash){
			// summary:
			//		Add hash to application history stack, update history management flags.
			//
			// hash:
			//		new hash should be added to _historyStack.
			this._historyStack.push({
				"hash": hash,
				"url": window.location.href,
				"detail": {target:hash}
			});

			this._historyLen = window.history.length;
			this._index = this._historyStack.length - 1;

			this._previous = this._current;
			this._current = hash;
			this._next = null;

			this._historyDiff = window.history.length - this._historyStack.length;

			// In order to make sure _addToHistoryStack flag invalid after add hash to history stack,
			// we set this flag to false in every addHistory operation even if it's already false.
			this._addToHistoryStack = false;
		},

		_onHashChange: function(currentHash){
			// summary:
			//		subscribe /dojo/hashchange and do add history, back, forward and go operation.
			//
			// currentHash:
			//		the new url hash when /dojo/hashchange is triggered.

			if(this._index < 0 || this._index > (window.history.length - 1)){
				throw Error("Application history out of management.");
			}

			this._newHistoryLen = window.history.length;

			// Application history stack asynchronized with window.history, refresh application history stack.
			if(this._oldHistoryLen > this._newHistoryLen){
				//console.log("need to refresh _historyStack, oldLen:"+this._oldHistoryLen+", newLen: "+this._newHistoryLen+", diff:"+this._historyDiff);
				this._historyStack.splice((this._newHistoryLen - this._historyDiff - 1), (this._historyStack.length - 1));

				// Reset _historyLen to make sure this._historyLen<window.history.length, so it will push this hash to history stack.
				this._historyLen = this._historyStack.length;

				// Reset this._oldHistoryLen, so it can avoid refresh history stack again in some situation,
				// because by doing this, this._oldHistoryLen !== this._newHistoryLen
				this._oldHistoryLen = 0;
			}

			// this._oldHistoryLen === this._newHistoryLen, it maybe need to refresh history stack or do history go, back and forward,
			// so we use _addToHistoryStack to identify the refresh operation.
			if(this._addToHistoryStack && (this._oldHistoryLen === this._newHistoryLen)){
				this._historyStack.splice((this._newHistoryLen - this._historyDiff - 1), (this._historyStack.length - 1));
				this._addHistory(currentHash);

				// It's a refresh operation, so that's no need to check history go, back or forward, just return.
				return;
			}

			//window.history.length increase, add hash to application history stack.
			if(this._historyLen < window.history.length){
				this._addHistory(currentHash);
				if(!this._startTransitionEvent){
					// transition to the target view
					this.app.emit("app-transition", {
						viewId: hash.getTarget(currentHash),
						opts: { params: hash.getParams(currentHash) || {} }
					});
				}
			}else{
				if(currentHash == this._current){
					// console.log("do nothing.");
				}else if(currentHash === this._previous){ // back
					this._back(currentHash, this._historyStack[this._index]["detail"]);
				}else if(currentHash === this._next){ //forward
					this._forward(currentHash, this._historyStack[this._index]["detail"]);
				}else{ // go
					//search in "back" first, then "forward"
					var index = -1;
					for(var i = this._index; i > 0; i--){
						if(currentHash === this._historyStack[i]["hash"]){
							index = i;
							break;
						}
					}

					//search in "forward"
					if(-1 === index){
						for(var i = this._index; i < this._historyStack.length; i++){
							if(currentHash === this._historyStack[i]["hash"]){
								index = i;
								break;
							}
						}
					}

					if(0 < index < this._historyStack.length){
						this._go(index, (index - this._index));
					}else{
						this.app.log("go error. index out of history stack.");
					}
				}
			}
			// set startTransition event flag to false
			this._startTransitionEvent = false;
		},

		_back: function(currentHash, detail){
			this.app.log("back");
			this._next = this._historyStack[this._index]["hash"];
			this._index--;
			if(this._index > 0){
				this._previous = this._historyStack[this._index - 1]["hash"];
			}else{
				this._previous = null;
			}
			this._current = currentHash;

			var target = hash.getTarget(currentHash, this.app.defaultView);

			// publish history back event
			topic.publish("/app/history/back", {"viewId": target, "detail": detail});

			// transition to the target view
			this.app.emit("app-transition", {
				viewId: target,
 				opts: lang.mixin({reverse: true}, detail, {"params": hash.getParams(currentHash)})
			});
		},

		_forward: function(currentHash, detail){
			this.app.log("forward");
			this._previous = this._historyStack[this._index]["hash"];
			this._index++;
			if(this._index < this._historyStack.length - 1){
				this._next = this._historyStack[this._index + 1]["hash"];
			}else{
				this._next = null;
			}
			this._current = currentHash;

			var target = hash.getTarget(currentHash, this.app.defaultView);

			// publish history forward event
			topic.publish("/app/history/forward", {"viewId": target, "detail": detail});

			// transition to the target view
			this.app.emit("app-transition", {
				viewId: target,
 				opts: lang.mixin({reverse: false}, detail, {"params": hash.getParams(currentHash)})
			});
		},

		_go: function(index, step){
			if(index < 0 || (index > window.history.length - 1)){
				throw Error("Application history.go steps out of management, index: "+index+" length: "+window.history.length);
			}

			this._index = index;
			this._current = this._historyStack[index]["hash"];
			this._previous = this._historyStack[index - 1] ? this._historyStack[index - 1]["hash"] : null;
			this._next = this._historyStack[index + 1] ? this._historyStack[index + 1]["hash"] : null;

			var target = hash.getTarget(this._current, this.app.defaultView);

			// publish history go event
			topic.publish("/app/history/go", {"viewId": target, "step": step, "detail": this._historyStack[index]["detail"]});

			// transition to the target view
			this.app.emit("app-transition", {
				viewId: target,
				opts: lang.mixin({reverse: (step <= 0)}, this._historyStack[index]["detail"], {"params": hash.getParams(this._current)})
			});
		}
	});
});
