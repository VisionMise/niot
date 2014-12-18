var niotStore = function(tableName, url) {

	var storage 	= tableName;

	this.record 		= function(id, callback) {

		var param 	= {
			'storage': 		this.storage,
			'procedure': 	'record',
			'param': 		[id]
		};

		var request = new niotRequest(param, callback, url);

	};

};

var niotError = function() {

};

var niotRequest = function(param, callback, postUrl) {

	var arguments 	= param;
	var url 		= 'ajax.php';
	var self 		= this;
	var resp 		= callback;

	if (postUrl !== undefined && postUrl !== false) {
		url 	= postUrl;
	}

	var post 		= $.post(url, arguments, function(result) {
		resp(result);
	}, "json");

	post.fail(function() {
		niotError();
	});

	return this;
};