if (!window._AJAX_LOADED) {
	window.$ajax = fsoft.utils;

	$ajax.Browser = fsoft.utils.Browser;
	$ajax.Size = fsoft.utils.Size;
	$ajax.Offset = fsoft.utils.Offset;
	$ajax.SlidePortion = fsoft.utils.SlidePortion;
	$ajax.Fader = fsoft.utils.Fader;
	$ajax.Timer = fsoft.utils.Timer;

	fsoft.Ajax = function (id) {
		this.id = id;
		//*******addition features*********/
		this.xHttp = null;
		this.version = "2012.1.1617.2";
		this.method = "POST";
		this.parameters = null;
		this.async = true;
		this.headers = null;
		this.dialog = null;
		this.multiDebug = false;
		this.notifierId = null;
	};

	var Ajax = fsoft.Ajax;

	Ajax.prototype.getProperty = function (name) {
		return this[name];
	};
	Ajax.prototype.setProperty = function (name, value) {
		this[name] = value;
	};
	Ajax.prototype.init = function () {
		this.element = document.getElementById(this.id);
		if (typeof(this.element) == "undefined")
			this.element = null;
		this.notifier = document.getElementById(this.notifierId);
		if (typeof(this.notifier) == "undefined")
			this.notifier = null;
		if (this.notifier == null && this.element != null)
			this.notifier = this.element;
	};

	Ajax.prototype.onRequest = function(e) {
		// Start ajax request
	}
	Ajax.prototype.onLoading = function(e) {
		// Loading
	}
	Ajax.prototype.onLoaded = function(e) {
		// Loaded
	}
	Ajax.prototype.onInteractive = function(e) {
		// Interactive
	}
	Ajax.prototype.onComplete = function(e) {
		// Complete
	}
	Ajax.prototype.onAbort = function(e) {
		// Abort
	}
	Ajax.prototype.onError = function(e) {
		// Error
	}

	Ajax.prototype.abort = function(e) {
		if(this.xHttp != null) {
			try {
				this.xHttp.abort();
			} catch(ex) {
				alert("Cannot cancel the request.\nError: " + ex.message);
			}
		}
	}

	Ajax.prototype.request = function () {
		if (this.isDownLevel) {
			if (!window.AjaxPostingBack && this.postback) {
				setTimeout(this.id + ".postback();", 300);
				window.AjaxPostingBack = true;
			}
			return false;
		}

		var args = arguments;
		if (args == null)
			args = [];
		if (args.length == 0)
			args[0] = null;

		if (this.cache) {
			var content = this.cache[args];
			if (content != null) {
				this.cleanUp(content);
				return true;
			}
		}

		if (this.requestInProgress)
			return false;
		else
			this.requestInProgress = true;
		if (this.onRequest)
			this.onRequest(args);
		var url = this.requestUri, postData = "Ajax_RequestID=" + this.id;
		if (args instanceof Object) {
			for (var i = 0; i < args.length; i++)
				postData += "&Ajax_" + this.id + "_Args" + encodeURIComponent("[]") + "=" + encodeURIComponent(args[i]);
		}

		/*================ Add all ajax request parameters to ajax request form ========================*/

		if(this.parameters) {
			var item = null;
			for (var i = 0; i < this.parameters.length; i++) {
				item = this.parameters[i];
				if(postData.indexOf(item[0]) < 0) {
					if(item[1] instanceof Array) {
						for(var j = 0; j < item[1].length; j++)
							postData += "&Ajax_" + this.id + "_RequestParam_" + item[0] + encodeURIComponent("[]") + "=" + encodeURIComponent(item[1][j]);
					}
					else
						postData += "&Ajax_" + this.id + "_RequestParam_" + item[0] + "=" + encodeURIComponent(item[1]);
				}
			}
		}

		/*======================================================================================*/

		if (this.debug)
			alert("Performing ajax request: " + args);
		if(this.loadingPanelTemplate) {
			// code to display loading notice here
			this.fader = new $ajax.Fader();
			this.fader.template = this.loadingPanelTemplate;
			this.fader.fadeDuration = this.loadingPanelFadeDuration;
			this.fader.fadeMaxOpacity = this.loadingPanelFadeMaxOpacity;
			if (this.notifier) {
				this.fader.fadeElement = this.notifier;
				this.fader.start(true);
			}
		}

		this.doRequest(url, postData, args);
		return true;
	};

	Ajax.prototype.doRequest = function (url, postData, args) {
		var self = this;
		var xRequest = false;
		var xHttp = null;
		xHttp = this.xHttp;

		if (window.XMLHttpRequest) {
			xRequest = true;
			if (xHttp == null)
				this.xHttp = xHttp = new XMLHttpRequest();
			xHttp.onreadystatechange = function() { self.handleServiceResponse(xHttp, xRequest); };
			xHttp.open(this.method, url, this.async);
			this.setRequestHeaders(xHttp);
			xHttp.send(postData);
		} else {
			if (document.implementation && document.implementation.createDocument) {
				if (xHttp == null)
					this.xHttp = xHttp = document.implementation.createDocument("", "", null);
				xHttp.onload = function() { self.handleServiceResponse(xHttp, xRequest); };
			} else {
				if (document.all) {
					if (window.ActiveXObject) {
						if (xHttp == null) {
							var msxml = ["Msxml2.XMLHTTP.7.0", "Msxml2.XMLHTTP.6.0", "Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP"];
							for (var i = 0; i < msxml.length; i++) {
								try {
									this.xHttp = xHttp = new ActiveXObject(msxml[i]);
									break;
								} catch(ex) {
									xHttp = null;
								}
							}
						}

						if (xHttp) {
							xRequest = true;
							xHttp.onreadystatechange = function() { self.handleServiceResponse(xHttp, xRequest); };
							xHttp.open(this.method, url, this.async);
							this.setRequestHeaders(xHttp);
							xHttp.send(postData);
						}
					}

					if (xHttp == null) {
						var islandId = this.Id + "_island";
						var island = document.getElementById(islandId);
						if (!island) {
							island = document.createElement("xml");
							island.id = islandId;
							document.body.appendChild(island);
						}

						if (island.XMLDocument)
							this.xHttp = xHttp = island.XMLDocument;
						else
							return false;
					}

					if (!xRequest)
						xHttp.onreadystatechange = function() { self.handleServiceResponse(xHttp, xRequest); };
				} else {
					if (typeof(this.postback) == "function")
						this.postback();
					return false;
				}
			}
		}

		if (!xRequest) {
			try {
				xHttp.async = this.async;
			} catch(ex) {}
			try {
				xHttp.load(url + this.requestParamDelimiter + postData);
			} catch(ex) {
				cleanUp(null);
				alert("Data not loaded: " + (ex.message ? ex.message : ex));
			}
		}

		return true;
	};

	Ajax.prototype.handleServiceResponse = function(xHttp, xRequest) {
		if(xHttp.readyState == 1 && this.onLoading)
			this.onLoading();
		else if(xHttp.readyState == 2 && this.onLoaded)
			this.onLoaded();
		else if(xHttp.readyState == 3 && this.onInteractive)
			this.onInteractive();
		else if(xHttp.readyState == 4 && xHttp.status == 0)
			this.onAbort();

		if (xHttp.readyState && xHttp.readyState != 4 && xHttp.readyState != "complete")
			return;

		var responseText = xHttp.responseText;

	    if(this.debug) {
			if (responseText) {
				var html = "response status: " + xHttp.status + "\n";
				html += "response status text: " + xHttp.statusText + "\n";
				html += "response text: \n" + responseText;
				this.showDebug("about:blank", 25, 25, 800, 600, "Received content:\n\n" + html);
			}
			else {
				var html = "The data could not be loaded.\n\n";
				html += "xHttp status: " + xHttp.status + "\nxHttp statusText: " + xHttp.statusText;
				alert(html);
			}
		}

		var responseXml = xRequest ? xHttp.responseXML : xHttp;
		var error = null;
		var responseContent = null;
		if (responseXml && responseXml.documentElement) {
			var xmlDoc = responseXml.documentElement;
			var root = $ajax.readData(xmlDoc);
			var nodeName = xmlDoc.name ? xmlDoc.name : xmlDoc.nodeName;
			if (nodeName != "content") {
				if (nodeName == "error")
					error = root.replace(/\$\$\$AJAX_CDATA_CLOSE\$\$\$/g, "]]>");
				else
					error = "Invalid response from server.";
	        }

			if (!error) {
				responseContent = this.processContent(root.replace(/\$\$\$AJAX_CDATA_CLOSE\$\$\$/g, "]]>"));
				try {
					if (this.cache)
						this.cache[args] = responseContent;
				} catch(ex) {
					error = ex.description ? ex.description : "Unknown error.";
				}
			}
		} else {
			error = "Invalid response from server.";
		}

		if (error) {
			if (typeof(this.onError) == "function")
				this.onError({status: xHttp.status, statusText: xHttp.statusText, error: error});
			else
				alert("The data could not be loaded.");
	    }

		/* Avoid memory leak in MSIE: clean up the oncomplete event handler */
		if ($ajax.Browser.ie) {
			if(xHttp != null && xHttp.onreadystatechange)
				xHttp.onreadystatechange = $ajax.emptyFunction;
			else if(xHttp != null && xHttp.onload)
				xHttp.onload = $ajax.emptyFunction;
		}

		this.cleanUp(responseContent);
	}

	Ajax.prototype.cleanUp = function(responseContent) {
		this.requestInProgress = false;
		if (responseContent != null) {
			if (typeof(this.onComplete) == "function")
				this.onComplete(responseContent);
			if (this.element != null)
				this.element.innerHTML = responseContent.content;
			this.loadScripts(responseContent);
			this.loadStyles(responseContent);
		}
	    // code to display loading panel notice here
	    if (this.fader && this.fader.fadePanel) {
			this.fader.fadePanel.fadeStartTime = new Date();
			this.fader.fade(false);
		}
	};

    Ajax.prototype.processContent = function (s) {
	    var html = {text: s};
		var content = {scripts: [], scriptsSrc: null, hrefs: null, content: null, text: s};

		content.scriptsSrc = $ajax.getScriptsSrc(html);
		var scriptsTag = $ajax.getTags(html, "script");
		for(var i = 0, len = scriptsTag.length; i < len; i++) {
			var scriptTag = scriptsTag[i];
			if (scriptTag.inner != "")
				content.scripts[content.scripts.length] = scriptTag.inner;
		}

		content.hrefs = $ajax.getLinkHrefs(html);
		content.content = html.text;

		return content;
    };

    Ajax.prototype.loadScripts = function(responseContent) {
		for(var i = 0, len = responseContent.scriptsSrc.length; i < len; i++)
			$ajax.attachClientScript(responseContent.scriptsSrc[i], true);
		for(var i = 0, len = responseContent.scripts.length; i < len; i++)
			$ajax.evalScriptCode(responseContent.scripts[i], false);
	}

	Ajax.prototype.loadStyles = function(responseContent) {
		var headElement = document.getElementsByTagName("head")[0];
		for (var i = 0, len = responseContent.hrefs.length; i < len; i++) {
			var href = responseContent.hrefs[i];
			var link = document.createElement("link");
			link.setAttribute("rel", "stylesheet");
			link.setAttribute("href", href);
			headElement.appendChild(link);
		}
	}

	/*================================== Timer methods ======================================*/

	// cancelTimer: this function will cancel the timer with RefreshInterval if it is enabled
	Ajax.prototype.cancelTimer = function() {
	    if(this.timerId)
		    this.timerId.stop();
	};

	// startTimer: this function will start the timer with RefreshInterval if it is enabled
	Ajax.prototype.startTimer = function() {
		if (typeof(this.timerId) == "undefined" && typeof(this.onTimer) == "function")
			this.timerId = new $ajax.Timer(this.refreshInterval, this.onTimer);
		if (this.timerId)
			this.timerId.start(arguments);
	};

	// setInterval: this function will set the time interval with RefreshInterval if it is enabled
	Ajax.prototype.setInterval = function(iMilliSecond) {
	    if(this.refreshInterval)
	        this.refreshInterval = iMilliSecond;
	    if (this.timerId)
		    this.timerId.setTick(this.refreshInterval);
	};

	/*======================================================================================*/


	/*=========================================Request Header Utils====================================*/

	Ajax.prototype.getResponseHeader = function(name) {
	    try {
	        if(this.xHttp)
	            return this.xHttp.getResponseHeader(name);
	        else
	            return null;
	    }
	    catch(e) {
	        return null;
	    }
	}

	Ajax.prototype.setRequestHeaders = function(xHttp) {
	    if(!xHttp)
	        return;

	    var requestHeaders = [["X-Requested-With", "XMLHttpRequest"], ["X-Ajax-Version", this.version]];
	    if(this.method != null && this.method.toLowerCase() == "post") {
	        requestHeaders.push(["Content-Type", "application/x-www-form-urlencoded"]);

	        /* Force "Connection: close" for Mozilla browsers to work around
	         * a bug where XMLHttpReqeuest sends an incorrect Content-length
	         * header. See Mozilla Bugzilla #246651.
	         */
	        if($ajax.Browser.firefox && xHttp.overrideMimeType)
	            requestHeaders.push(["Connection", "close"]);
	    }

	    if(this.headers)
	        requestHeaders.push.apply(requestHeaders, this.headers);
	    for(var i = 0; i < requestHeaders.length; i++)
	        xHttp.setRequestHeader(requestHeaders[i][0], requestHeaders[i][1]);
	}

	Ajax.prototype.addHeader = function(name, value) {
	    if(name == null || name.length < 1)
	        return;
	    if(!this.headers)
	        this.headers = [];
	    if(this.headerExisted(name)) {
	        this.replaceHeader(name, value);
	        return;
	    }
	    var header = ["", ""];
	    header[0] = name;
	    header[1] = value;
	    this.headers.push(header);
	}

	Ajax.prototype.clearHeaders = function() {
	    if(this.headers)
	        this.headers = null;
	}

	Ajax.prototype.headerExisted = function(name) {
	    if(!this.headers)
	        return false;
	    for(var i = 0; i < this.headers.length; i++) {
	        if(name.toLowerCase() == this.headers[i][0].toLowerCase())
	            return true;
	    }
	    return false;
	}

	Ajax.prototype.replaceHeader = function(name, value) {
	    if(!this.headers)
	        return;
	    for(var i = 0; i < this.headers.length; i++) {
	        if(name.toLowerCase() == this.headers[i][0].toLowerCase()) {
	            this.headers[i][1] = value;
	            return;
	        }
	    }
	}

	/*=====================================================================================*/

	/*=========================================parameters Utils====================================*/

	Ajax.prototype.addParameter = function(param, value) {
	    if(!this.parameters)
	        this.parameters = [];
	    if(this.parameterExisted(param)) {
	        this.replaceParameter(param, value);
	        return;
	    }
	    var item = ["", ""];
	    item[0] = param;
	    item[1] = value;
	    this.parameters.push(item);
	}

	Ajax.prototype.getParameter = function(param) {
	    if(!this.parameters)
	        return null;
	    if (typeof(this.parameters[param]) == "undefined")
		    return null;
		return this.parameters[param];
	}

	Ajax.prototype.clearParameters = function() {
	    if(this.parameters)
	        this.parameters = null;
	}

	Ajax.prototype.parameterExisted = function(param) {
	    if(!this.parameters)
	        return false;
	    for(var i = 0; i < this.parameters.length; i++) {
	        if(param.toLowerCase() == this.parameters[i][0].toLowerCase())
	            return true;
	    }
	    return false;
	}

	Ajax.prototype.replaceParameter = function(param, value) {
	    if(!this.parameters)
	        return;
	    for(var i = 0; i < this.parameters.length; i++) {
	        if(param.toLowerCase() == this.parameters[i][0].toLowerCase()) {
	            this.parameters[i][1] = value;
	            return;
	        }
	    }
	}

	/*=====================================================================================*/

	Ajax.prototype.showDebug = function(url, top, left, width, height, content) {
	    try {
		    if(!this.multiDebug)
	            this.dialog = window.Ajax_Dialog;
		    if(!this.dialog) {
		        if(document.all)
			        this.dialog = window.open(url, "_blank", "channelmode=no,directories=no,fullscreen=no,titlebar=no,menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=no,status=no,left=" + left + ",top=" + top + ",width=" + width + ",height=" + height);
				else
	                this.dialog = window.open(url, "_blank", "personalbar=no,titlebar=no,menubar=no,toolbar=no,location=no,resizable=yes,scrollbars=no,modal=yes,status=no,status=no,left=" + left + ",top=" + top +",width=" + width + ",height=" + height);
	            if(!this.dialog)
	                return alert("Your browser blocked the debug popup. Please disable popup blocker to enable debugging mode!") === true;
	            window.Ajax_Dialog = this.dialog;
			    this.dialog.document.write("<html>");
			    this.dialog.document.write("	<head>");
			    this.dialog.document.write("		<title>Ajax Debugger</title>");
			    this.dialog.document.write("		<style type=\"text/css\">");
	            this.dialog.document.write("             body {overflow: hidden; margin: 8px 5px 0px 5px;}");
			    this.dialog.document.write("			textarea {background-color: #f7f7f7; font-size: 12px; border: 2px solid #ff9900; overflow: auto; width: 100%; height: 96%; padding: 0px 2px 0px 2px;}");
			    this.dialog.document.write("		</style>");
			    this.dialog.document.write("	<head>");
			    if(this.multiDebug)
				    this.dialog.document.write("	<body onunload=\"opener." + this.id + ".dialog=null;\">");
				else
	    			this.dialog.document.write("	<body onunload=\"opener.window.Ajax_Dialog=null; opener." + this.id + ".dialog=null;\">");
			    this.dialog.document.write("     <div style=\"font-family: Verdana; Color: #606060;\"><span style=\"background-color: #ff9900; padding: 2px;\">Ajax Debugging: # <span id=\"debugID\" style=\"font-weight: bold;\"></span></span></div>");
			    this.dialog.document.write("		<textarea id=\"debugArea\"></textarea>");
			    this.dialog.document.write("	<body>");
			    this.dialog.document.write("</html>");
		    }

			var el = this.dialog.document.getElementById("debugID");
	        if(el)
	            el.innerHTML = "ID = " + this.id;
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

	window._AJAX_LOADED = true;
};