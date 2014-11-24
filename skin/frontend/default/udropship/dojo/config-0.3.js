var dojoConfig;
(function () {
	var baseUrl = "/skin/frontend/default/udropship/dojo/";//location.pathname.replace(/\/[^/]*$/, '/../../dojo/');
	dojoConfig = {
		async: 1,
		isDebug: true,
		cacheBust: '1.9.3-0.3.15',
		// Load dgrid and its dependencies from a local copy.
		// If we were loading everything locally, this would not
		// be necessary, since Dojo would automatically pick up
		// dgrid, xstyle, and put-selector as siblings of the dojo folder.
		packages: [
			{ name: 'dgrid', location: baseUrl + 'dgrid-0.3' },
			{ name: 'xstyle', location: baseUrl + 'xstyle' },
			{ name: 'put-selector', location: baseUrl + 'put-selector' },
			{ name: 'vendor', location: baseUrl + 'vendor-0.3' }
		]
	};
}());