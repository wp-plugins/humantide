(function(){
	var Humantide = {

		appendMain: function(){
			window.onload = function(){
				Humantide.appendChannel("http://www.humantide.com");
			}
		},

		appendChannel: function(src){
			var channelIframe = document.createElement("iframe");
		    channelIframe.id = "channelIframe";
		    channelIframe.height = "720px";
		    channelIframe.width = "300px";
		    channelIframe.src = src;

		    channelIframe.style.position = "fixed";
		    channelIframe.style.right = "0";
		    channelIframe.style.bottom = "0";
		    channelIframe.style.margin = "0";
		    channelIframe.style.padding = "0";

		    document.body.appendChild(channelIframe);
		}
	}

//	if(showHumantideMain)
//		Humantide.appendMain();

//	if(isAdmin)
	{
		// Create IE + others compatible event handler
		var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
		var eventer = window[eventMethod];
		var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

		// Listen to message from child window
		eventer(messageEvent,function(e) {
			postTide(e.data.title, e.data.url);
		},false);
	}

})();


/** /
var Humantide = {
	createTide: function(tideTitle, tideUrl){
		var tide = {
			title: tideTitle,
			url: tideUrl
		}

		var parentUrl = decodeURIComponent(window.location.search.split("?url=")[1]);


		parent.postMessage(tide, parentUrl);
	}
}

/**/