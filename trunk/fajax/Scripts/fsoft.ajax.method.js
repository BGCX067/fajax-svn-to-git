if(typeof(window.AjaxServiceNameSpace) == "undefined")
	window.AjaxServiceNameSpace = {};

window.AjaxServiceNameSpace.AjaxMethod = function(id) {
	this.id = id;
	this.servicePath = location.href;
	this.urlDelimiter = null;
	this.onComplete = null;
	this.onError = null;
	this.debug = false;
	this.calleeMethod = null;
	this.isIE = navigator.userAgent.toLowerCase().indexOf("msie") > -1;
	this.isFirefox = navigator.userAgent.toLowerCase().indexOf("firefox/") > -1;
}
var AjaxNS = window.AjaxServiceNameSpace.AjaxMethod;


AjaxNS.prototype.serviceRequest = function(methodName, args, bArgs) {
	var xmlRequest = null, responseContent = null, oThis = this;
	var postData = "AjaxMethod=" + methodName;
    if (bArgs || args instanceof Array) {
        for (var i = 0; i < args.length; i++)
			postData += "&AjaxMethod_Param" + encodeURIComponent("[]") + "=" + encodeURIComponent(args[i]);
    }
    else {
		postData += "&AjaxMethod_Param=" + encodeURIComponent(args);
    }

	this.calleeMethod = methodName;

	try
	{
		xmlRequest = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

		if (xmlRequest == null) {
			if (document.implementation && document.implementation.createDocument) {
				xmlRequest = document.implementation.createDocument("", "", null);
				xmlRequest.async = true;
				xmlRequest.onload = function() {
					responseContent = oThis.handleAsyncServiceResponse(xmlRequest);
				};
			}
			else if (document.all) {
				var islandId = this.id + "_island";
				var islandEl = document.getElementById(islandId);
				if (!islandEl) {
					islandEl = document.createElement("xml");
					islandEl.id = islandId;
					document.body.appendChild(islandEl);
				}
				if (islandEl.XMLDocument) {
					xmlRequest = islandEl.XMLDocument;
					xmlRequest.async = true;
					xmlRequest.onreadystatechange = function() {
						responseContent = oThis.handleAsyncServiceResponse(xmlRequest);
					};
				}
			}

			this.urlDelimiter = this.servicePath.indexOf("?") > 0 ? "&" : "?";
			xmlRequest.load(this.servicePath + this.urlDelimiter + postData);
		}
		else {
			xmlRequest.open("POST", this.servicePath, true);
			xmlRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			if (this.isFirefox)
				xmlRequest.setRequestHeader("Content-Length", postData.length);
			/* Force "Connection: close" for Mozilla browsers to work around
			 * a bug where XMLHttpReqeuest sends an incorrect Content-length
			 * header. See Mozilla Bugzilla #246651.
			 */
			if(this.isFirefox && xmlRequest.overrideMimeType)
				xmlRequest.setRequestHeader("Connection", "close");
			xmlRequest.onreadystatechange = function() {
				responseContent = oThis.handleAsyncServiceResponse(xmlRequest);
			};
			xmlRequest.send(postData);
		}
	}
	catch(ex)
	{
		if(typeof(this.onError) == "function")
		{
		    var e =
		    {
				"calleeMethod": methodName,
		        "errorCode" : "",
		        "errorText" : ex.message,
		        "message" : ex.message, //adding for consistency
		        "text" : "",
		        "xml" : ""
		    };

			this.onError(e);
			if(this.debug) {
				var message = "Data not loaded: " + (ex.message ? ex.message : ex) + "\n\n";
				if(xmlRequest) {
					message += "readyState: " + xmlRequest.readyState + "\n";
					message += "status: " + xmlRequest.status + "\n";
					message += "statusText: " + xmlRequest.statusText + "\n";
				}
				message += "responseText:\n" + xmlRequest.responseText;
				this.showDebug("about:blank", 25, 25, 800, 600, message);
			}
		}
	}

	return responseContent;
};

AjaxNS.prototype.syncServiceRequest = function(methodName, args, bArgs)
{
	var xmlRequest = null, responseContent = null, oThis = this;
	var postData = "AjaxMethod=" + methodName;
    if (bArgs || args instanceof Array) {
        for (var i = 0; i < args.length; i++)
			postData += "&AjaxMethod_Param" + encodeURIComponent("[]") + "=" + encodeURIComponent(args[i]);
    }
    else {
		postData += "&AjaxMethod_Param=" + encodeURIComponent(args);
    }

	this.calleeMethod = methodName;

	try
	{
		xmlRequest = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");

		if (xmlRequest == null) {
			if (document.implementation && document.implementation.createDocument) {
				xmlRequest = document.implementation.createDocument("", "", null);
				xmlRequest.async = false;
				xmlRequest.onload = function() {
					responseContent = oThis.handleSyncServiceResponse(xmlRequest);
				};
			}
			else if(document.all) {
				var islandId = this.id + "_island";
				var islandEl = document.getElementById(islandId);
				if (!islandEl) {
					islandEl = document.createElement("xml");
					islandEl.id = islandId;
					document.body.appendChild(islandEl);
				}
				if (islandEl.XMLDocument) {
					xmlRequest = islandEl.XMLDocument;
					xmlRequest.async = false;
					xmlRequest.onreadystatechange = function() {
						responseContent = oThis.handleSyncServiceResponse(xmlRequest);
					};
				}
			}

			this.urlDelimiter = this.servicePath.indexOf("?") > 0 ? "&" : "?";
			xmlRequest.load(this.servicePath + this.urlDelimiter + postData);
		}
		else {
			xmlRequest.open("POST", this.servicePath, false);
			xmlRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			if (this.isFirefox)
				xmlRequest.setRequestHeader("Content-Length", postData.length);
			/* Force "Connection: close" for Mozilla browsers to work around
			 * a bug where XMLHttpReqeuest sends an incorrect Content-length
			 * header. See Mozilla Bugzilla #246651.
			 */
			if(this.isFirefox && xmlRequest.overrideMimeType)
				xmlRequest.setRequestHeader("Connection", "close");
			xmlRequest.send(postData);
			responseContent = this.handleSyncServiceResponse(xmlRequest);
		}
	}
	catch(ex)
	{
		if(typeof(this.onError) == "function")
		{
		    var e =
		    {
				"calleeMethod": methodName,
		        "errorCode" : "",
		        "errorText" : ex.message,
		        "message" : ex.message, //adding for consistency
		        "text" : "",
		        "xml" : ""
		    };

			this.onError(e);
			if(this.debug) {
				var message = "Data not loaded: " + (ex.message ? ex.message : ex) + "\n\n";
				if(xmlRequest) {
					message += "readyState: " + xmlRequest.readyState + "\n";
					message += "status: " + xmlRequest.status + "\n";
					message += "statusText: " + xmlRequest.statusText + "\n";
				}
				message += "responseText:\n" + xmlRequest.responseText;
				this.showDebug("about:blank", 25, 25, 800, 600, message);
			}
		}
	}

	return responseContent;
};

AjaxNS.prototype.check404Status = function(xHttp)
{
    try
    {
	    if (xHttp && xHttp.status == 404)
	    {
		    var errorText;
		    errorText = "Ajax callback error: source url not found! \n\r\n\rPlease verify if you are using any URL-rewriting code and set the AjaxUrl property to match the URL you need.";

		    var error = new Error(errorText);
		    throw(error);

		    return;
	    }
	}
	catch(ex)
	{	}
}

AjaxNS.prototype.handleAsyncServiceResponse = function (xHttp)
{
	var content = null, oThis = this;

	try
	{
		if(xHttp.readyState == 1 && typeof(oThis.onLoading) == "function")
		    oThis.onLoading();
		else if(xHttp.readyState == 2 && typeof(oThis.onLoaded) == "function")
		    oThis.onLoaded();
		else if(xHttp.readyState == 3 && typeof(oThis.onInteractive) == "function")
		    oThis.onInteractive();
		else if(xHttp.readyState == 4 || xHttp.readyState == "complete") {
		    if(xHttp.status == 0 && typeof(oThis.onAbort) == "function")
		        oThis.onAbort();
		}

		if(xHttp.readyState && xHttp.readyState != 4 && xHttp.readyState != "complete")
		    return;

		oThis.check404Status(xHttp);

		content = xHttp.responseXML;

		if(content && content.documentElement)
		{
			var e =
			{
				"calleeMethod": oThis.calleeMethod,
				"text" : xHttp.responseText,
				"xml" : xHttp.responseXML,
				"content": oThis.parseNode(content)
			};
			oThis.onComplete(e);
		}
		else if(typeof(oThis.onError) == "function")
		{

			var e =
			{
				"calleeMethod": oThis.calleeMethod,
				"errorCode" : xHttp.status,
				"errorText" : xHttp.statusText,
				"message" : xHttp.statusText, //adding for consistency
				"text" : xHttp.responseText,
				"xml" : xHttp.responseXml
			};
			oThis.onError(e);
		}

		if(oThis.debug) {
			var message = "";
			if(xHttp) {
				message += "readyState: " + (xHttp.readyState ? xHttp.readyState : "undefined") + "\n";
				message += "status: " + (xHttp.status ? xHttp.status : "undefined") + "\n";
				message += "statusText: " + (xHttp.statusText ? xHttp.statusText : "undefined") + "\n";
			}
			message += "responseText:\n" + (xHttp.responseText ? xHttp.responseText : "undefined");
			oThis.showDebug("about:blank", 25, 25, 800, 600, message);
		}
	}
	catch(ex)
	{
		if(typeof(oThis.onError) == "function")
		{
		    var e =
		    {
				"calleeMethod": oThis.calleeMethod,
		        "errorCode" : "",
		        "errorText" : ex.message,
		        "message" : ex.message, //adding for consistency
		        "text" : "",
		        "xml" : ""
		    };
			oThis.onError(e);
		}
	}

    /* Avoid memory leak in MSIE: clean up the oncomplete event handler */
	if (xHttp != null && this.isIE) {
		if (xHttp.onreadystatechange)
			xHttp.onreadystatechange = oThis.emptyFunction;
		else
			xHttp.onload = oThis.emptyFunction;
	}

	content = oThis.parseNode(content);
	return content;
};

AjaxNS.prototype.handleSyncServiceResponse = function (xHttp)
{
	var content = null, oThis = this;

	try
	{
		if (xHttp == null)
			return null;

		oThis.check404Status(xHttp);

		if(xHttp.status != 200 && typeof(oThis.onError) == "function")
		{
		    var e =
		    {
				"calleeMethod": oThis.calleeMethod,
		        "errorCode" : xHttp.status,
		        "errorText" : xHttp.statusText,
		        "message" : xHttp.statusText, //adding for consistency
		        "text" : xHttp.responseText,
		        "xml" : xHttp.responseXml
		    };
			oThis.onError(e)
		}
		else if(typeof(oThis.onComplete) == "function")
		{
		    var e =
		    {
				"calleeMethod": oThis.calleeMethod,
		        "text" : xHttp.responseText,
		        "xml" : xHttp.responseXML,
				"content": oThis.parseNode(xHttp.responseXML)
		    };

			oThis.onComplete(e);
		}

		content = xHttp.responseXML;

		if(oThis.debug) {
			var message = "";
			if(xHttp) {
				message += "readyState: " + (xHttp.readyState ? xHttp.readyState : "undefined")+ "\n";
				message += "status: " + (xHttp.status ? xHttp.status : "undefined") + "\n";
				message += "statusText: " + (xHttp.statusText ? xHttp.statusTex : "undefined")+ "\n";
			}
			message += "responseText:\n" + (xHttp.responseText ? xHttp.responseText : "undefined");
			oThis.showDebug("about:blank", 25, 25, 800, 600, message);
		}
	}
	catch(ex)
	{
		if(typeof(oThis.onError) == "function")
		{
		    var e =
		    {
				"calleeMethod": oThis.calleeMethod,
		        "errorCode" : "",
		        "errorText" : ex.message,
		        "message" : ex.message, //adding for consistency
		        "text" : "",
		        "xml" : ""
		    };
			oThis.onError(e);
		}
	}

    /* Avoid memory leak in MSIE: clean up the oncomplete event handler */
	if(xHttp != null && this.isIE) {
		if(xHttp.onreadystatechange)
			xHttp.onreadystatechange = AjaxNS.emptyFunction;
		else
			xHttp.onload = AjaxNS.emptyFunction;
	}

	content = oThis.parseNode(content);
	return content;
};

AjaxNS.prototype.parseNode = function (xmlDoc) {
	var sContent = null;
	if (xmlDoc && xmlDoc.documentElement) {
		sContent = xmlDoc.documentElement.firstChild.nodeValue;
		sContent = sContent.replace(/\$\$\$AJAX_CDATA_CLOSE\$\$\$/g, "]]>");
	}

	return sContent;
};

AjaxNS.prototype.emptyFunction = function(){};

AjaxNS.prototype.showDebug = function(url, top, left, width, height, content) {
	try {
		this.dialog = window.AjaxMethod_Dialog;
		if(!this.dialog) {
			if(document.all)
				this.dialog = window.open(url, "_blank", "channelmode=no,directories=no,fullscreen=no,titlebar=no,menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=no,status=no,left=" + left + ",top=" + top + ",width=" + width + ",height=" + height);
			else
				this.dialog = window.open(url, "_blank", "personalbar=no,titlebar=no,menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=no,modal=yes,status=no,left=" + left + ",top=" + top +",width=" + width + ",height=" + height);
			if(!this.dialog) {
				alert("Your browser blocked the debug popup. Please disable popup blocker to enable debugging mode!");
				return false;
			}
			window.AjaxMethod_Dialog = this.dialog;
			this.dialog.document.write("<html>");
			this.dialog.document.write("	<head>");
			this.dialog.document.write("		<title>AjaxMethod Debugger</title>");
			this.dialog.document.write("		<style type=\"text/css\">");
			this.dialog.document.write("			body {overflow: hidden; margin: 8px 5px 0px 5px;}");
			this.dialog.document.write("			textarea {background-color: #f7f7f7; font-size: 12px; border: 2px solid #ff9900; overflow: auto; width: 100%; height: 96%; padding: 0px 2px 2px 2px;}");
			this.dialog.document.write("		</style>");
			this.dialog.document.write("	<head>");
			this.dialog.document.write("	<body onunload=\"if (opener && opener.window) opener.window.AjaxMethod_Dialog = null;\">");
			this.dialog.document.write("		<div style=\"font-family: Verdana; Color: #606060;\"><span style=\"background-color: #ff9900; padding: 2px;\">AjaxMethod Debugging: # <span id=\"debugID\" style=\"font-weight: bold;\"></span></span></div>");
			this.dialog.document.write("		<textarea id=\"debugArea\"></textarea>");
			this.dialog.document.write("	<body>");
			this.dialog.document.write("</html>");
		}
		var el = this.dialog.document.getElementById("debugID");
		if(el)
			el.innerHTML = "Method = " + this.calleeMethod;
		el = this.dialog.document.getElementById("debugArea");
		if(el)
			el.value = content;
		this.dialog.document.close();
		if(window.focus)
			this.dialog.focus();
		return true;
	}
	catch(e) {
		alert(e.message);
	}
	return false;
};