if (!window._FSOFT_UTILS_LOADED) {
	window.fsoft = {};

	fsoft.utils = {
		isScriptRegistered: function (src, container) {
			if (!src)
				return 0;
			if (!container)
				container = document;
			if (fsoft.utils._uniqueScripts == null)
				fsoft.utils._uniqueScripts = {};
			var nestedLevel = 0;
			var startPos = src.indexOf("?d=");
			var endPos = src.indexOf("&");
			var scriptSrc = startPos > 0 && endPos > startPos ? src.substring(startPos + 3, endPos) : src;
			if (fsoft.utils._uniqueScripts[scriptSrc] != null)
				return 2;
			var srcExisted = false;
			var scriptElements = document.getElementsByTagName("script");
			for (var i = 0, len = scriptElements.length; i < len; i++) {
				var scriptElement = scriptElements[i];
				if (scriptElement.src) {
					if (scriptElement.getAttribute("src", 2).toLowerCase().indexOf(scriptSrc.toLowerCase()) != -1) {
						fsoft.utils._uniqueScripts[scriptSrc] = true;
						if (!fsoft.utils.isDescendant(container, scriptElement))
							nestedLevel++;
						srcExisted = true;
					}
				}
			}

			if (!srcExisted) {
				fsoft.utils._uniqueScripts[scriptSrc] = true;
				nestedLevel++;
			}
			return nestedLevel;
		},
		isDescendant: function (ancestor, node) {
			try {
				for (var parent = node.parentNode; parent != null; parent = parent.parentNode) {
					if (parent == ancestor)
						return true;
				}
			} catch (e) {}
			return false;
		},
		loadClientScript: function (src, global) {
			if (!this.shouldIncludeClientScript(src))
				return;
			var xhttp = (window.XMLHttpRequest) ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			xhttp.open("GET", src, false);
			xhttp.send(null);
			if (xhttp.status == 200) {
				var content = xhttp.responseText;
				this.evalScriptCode(content, global);
			}
		},
		attachClientScript: function (src, global) {
			if (!this.shouldIncludeClientScript(src))
				return;
			var container = null;
			if (global)
				container = document.getElementsByTagName("head").item(0);
			else
				container = document.body;
			var scriptElement = document.createElement("script");
			scriptElement.setAttribute("type", "text/javascript");
			scriptElement.setAttribute("src", src);
			container.appendChild(scriptElement);
		},
		shouldIncludeClientScript: function (src) {
			var included = this.isScriptRegistered(src);
			if (included == 0 || included > 1)
				return false;
			return true;
		},
		evalScriptCode: function (src, global) {
			if (this.Browser.safari)
				src = src.replace(/^\s*<!--((.|\n)*)-->\s*$/mi, "$1");
			var scriptElement = document.createElement("script");
			scriptElement.setAttribute("type", "text/javascript");
			if (this.Browser.safari)
				scriptElement.appendChild(document.createTextNode(src));
			else
				scriptElement.text = src;
			var container = null;
			if (global)
				container = document.getElementsByTagName("head")[0];
			else
				container = document.body;
			container.appendChild(scriptElement);
			if (this.Browser.safari)
				scriptElement.innerHTML = "";
			else
				scriptElement.parentNode.removeChild(scriptElement);
		},
		getTags: function (html, tagName) {
			var text = typeof(html.text) == "string" ? html.text : html;
			var content = "";
			var matched = false;
			var tags = [];
			while (true) {
				var tag = this.getTag(text, tagName);
				if (tag.index == -1) {
					content += text;
					break;
				}
				tags[tags.length] = tag;
				var startPos = tag.index + tag.outer.length;
				content += tag.index > 0 ? text.substring(0, tag.index) : "";
				text = text.substring(startPos, text.length);
				matched = true;
			}

			if (typeof(html.text) != "undefined")
				html.text = content;
			return tags;
		},
		getTag: function (html, tagName, def) {
			if (typeof(def) == "undefined")
				def = "";
			var regex = new RegExp("<" + tagName + "[^>]*>((.|\n|\r)*?)</" + tagName + ">", "i");
			var match = html.match(regex);
			if (match != null && match.length >= 2) {
				return {
					outer: match[0],
					inner: match[1],
					index: match.index
				};
			} else {
				return {
					outer: def,
					inner: def,
					index: -1
				};
			}
		},
		getLinkHrefs: function (html) {
			var text = typeof(html.text) == "string" ? html.text : html;
			var content = "";
			var matched = false;
			var hrefs = [];
			while (true) {
				var match = text.match(/<link[^>]*href\s*=\s*('|")?([^'"]*)('|")?([^>]*)>.*?(<\/link>)?/i);
				if (match == null || match.length < 3) {
					content += text;
					break;
				}
				hrefs[hrefs.length] = match[2];
				var startPos = match.index + match[0].length;
				content += match.index > 0 ? text.substring(0, match.index) : "";
				text = text.substring(startPos, text.length);
				matched = true;
			}

			if (typeof(html.text) != "undefined")
				html.text = text;
			return hrefs;
		},
		getScriptsSrc: function (html) {
			var text = typeof(html.text) == "string" ? html.text : html;
			var content = "";
			var matched = false;
			var scriptsSrc = [];
			while (1) {
				var match = text.match(/<script[^>]*src\s*=\s*('|")?([^'"]*)('|")?([^>]*)>.*?(<\/script>)?/i);
				if (match == null || match.length < 3) {
					content += text;
					break;
				}
				scriptsSrc[scriptsSrc.length] = match[2];
				var startPos = match.index + match[0].length;
				content += match.index > 0 ? text.substring(0, match.index) : "";
				text = text.substring(startPos, text.length);
			}
			return scriptsSrc;
		},
		readData: function(node) {
			var child = this.readNodeByName(node);
			if (child != null) {
				if (child.firstChild != null)
					child = child.firstChild;
				return child.value ? child.value : child.nodeValue;
			}
			return null;
		},
		readNodeByName: function(node, nodeName) {
			var name = node.name ? node.name : node.nodeName;
			if (typeof(nodeName) == "undefined" || name == nodeName)
				return node;
			for(var i = 0; i < node.childNodes.length; i++) {
				name = node.childNodes[i].name ? node.childNodes[i].name : node.childNodes[i].nodeName;
				if (name == nodeName)
					return node.childNodes[i];
			}

			return null;
		},
		removeNode: function (element) {
	        if (element) {
	            if (document.all) {
	                element.removeNode(true);
	            } else {
					if (element.parentNode)
						element.parentNode.removeChild(element);
	            }
	        }
	    },
		emptyFunction: function() {}
	};

    fsoft.utils.Browser = new function() {
		this.searchString = function (data) {
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1)
						return data[i].identity;
				}
				else if (dataProp)
					return data[i].identity;
			}
		};

		this.searchVersion = function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1)
				return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		};

		var dataBrowser = [
			{
				string: navigator.userAgent,
				subString: "Chrome",
				identity: "Chrome"
			},
			{
				string: navigator.userAgent,
				subString: "OmniWeb",
				versionSearch: "OmniWeb/",
				identity: "OmniWeb"
			},
			{
				string: navigator.vendor,
				subString: "Apple",
				identity: "Safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera",
				versionSearch: "Version"
			},
			{
				string: navigator.vendor,
				subString: "iCab",
				identity: "iCab"
			},
			{
				string: navigator.vendor,
				subString: "KDE",
				identity: "Konqueror"
			},
			{
				string: navigator.userAgent,
				subString: "Firefox",
				identity: "Firefox"
			},
			{
				string: navigator.vendor,
				subString: "Camino",
				identity: "Camino"
			},
			{		// for newer Netscapes (6+)
				string: navigator.userAgent,
				subString: "Netscape",
				identity: "Netscape"
			},
			{
				string: navigator.userAgent,
				subString: "MSIE",
				identity: "Explorer",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent,
				subString: "Gecko",
				identity: "Mozilla",
				versionSearch: "rv"
			},
			{ 		// for older Netscapes (4-)
				string: navigator.userAgent,
				subString: "Mozilla",
				identity: "Netscape",
				versionSearch: "Mozilla"
			}];

		var dataOS = [
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			{
				   string: navigator.userAgent,
				   subString: "iPhone",
				   identity: "iPhone/iPod"
		    },
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}];

		this.browser = this.searchString(dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || "an unknown version";
		this.os = this.searchString(dataOS) || "an unknown OS";

		var agent = (navigator == null || navigator.userAgent == null) ? "" : navigator.userAgent.toLowerCase();
		this.opera = this.browser == "Opera";
		this.mac = this.os == "Mac";
		this.ie = this.browser == "Explorer";
		this.iemac = this.ie && this.mac;
		this.safari = this.browser == "Safari";
		this.konqueror = this.browser == "Konqueror";
		this.firefox = this.browser == "Firefox";
		this.ie3 = this.ie && (this.version < 4);
		this.ie4 = this.ie && (this.version == 4) && (agent.indexOf("msie 4") != -1);
		this.ie5point5 = this.ie && (this.version == 4) && (agent.indexOf("msie 5.5") != -1);
		this.ie5 = this.ie && (this.version == 4) && (agent.indexOf("msie 5") != -1) && !this.ie5point5;
		this.ie6plus = this.ie && !this.ie3 && !this.ie4 && !this.ie5 && !this.ie5point5;
		this.ie7plus = this.ie6plus && (agent.indexOf("msie 6") == -1);
		this.ie8 = this.ie7plus && (agent.indexOf("msie 8") != -1);
	};

    fsoft.utils.Size = new function() {
	    this.getAdjustedHeight = function (el, def) {
	        if (!el)
	            return (def ? def : 0);
	        if (!def && def != 0)
	            def = el.offsetHeight;
	        if (fsoft.utils.Browser.ie && el.currentStyle) {
	            var style = el.currentStyle;
	            if (style) {
	                var h = 0;
	                h += isNaN(parseInt(style.borderTopWidth)) ? 0 : parseInt(style.borderTopWidth);
	                h += isNaN(parseInt(style.borderBottomWidth)) ? 0 : parseInt(style.borderBottomWidth);
	                h += isNaN(parseInt(style.paddingTop)) ? 0 : parseInt(style.paddingTop);
	                h += isNaN(parseInt(style.paddingBottom)) ? 0 : parseInt(style.paddingBottom);
	                def -= h;
	            }
	        } else {
				if (document.defaultView && document.defaultView.getComputedStyle) {
	                var style = document.defaultView.getComputedStyle(el, "");
	                if (style) {
	                    var h = 0;
	                    h += style.getPropertyValue("border-top-width") ? parseInt(style.getPropertyValue("border-top-width")) : 0;
	                    h += style.getPropertyValue("border-bottom-width") ? parseInt(style.getPropertyValue("border-bottom-width")) : 0;
	                    h += style.getPropertyValue("padding-top") ? parseInt(style.getPropertyValue("padding-top")) : 0;
	                    h += style.getPropertyValue("padding-bottom") ? parseInt(style.getPropertyValue("padding-bottom")) : 0;
	                    def -= h;
	                }
	            }
	        }
	        return def;
	    };

	    this.getAdjustedWidth = function (el, def) {
	        if (!el)
	            return (def ? def : 0);
	        if (!def && def != 0)
	            def = el.offsetWidth;
	        if (fsoft.utils.Browser.ie && el.currentStyle) {
	            var style = el.currentStyle;
	            if (style) {
	                var w = 0;
	                w += isNaN(parseInt(style.borderLeftWidth)) ? 0 : parseInt(style.borderLeftWidth);
	                w += isNaN(parseInt(style.borderRightWidth)) ? 0 : parseInt(style.borderRightWidth);
	                w += isNaN(parseInt(style.paddingLeft)) ? 0 : parseInt(style.paddingLeft);
	                w += isNaN(parseInt(style.paddingRight)) ? 0 : parseInt(style.paddingRight);
	                def -= w;
	            }
	        } else {
				if (document.defaultView && document.defaultView.getComputedStyle) {
	                var style = document.defaultView.getComputedStyle(el, "");
	                if (style) {
	                    var w = 0;
	                    w += style.getPropertyValue("border-left-width") ? parseInt(style.getPropertyValue("border-left-width")) : 0;
	                    w += style.getPropertyValue("border-right-width") ? parseInt(style.getPropertyValue("border-right-width")) : 0;
	                    w += style.getPropertyValue("padding-left") ? parseInt(style.getPropertyValue("padding-left")) : 0;
	                    w += style.getPropertyValue("padding-right") ? parseInt(style.getPropertyValue("padding-right")) : 0;
	                    def -= w;
	                }
	            }
	        }
	        return def;
	    };
	};

	fsoft.utils.Offset = new function() {
	    this.getAdjustedOffsetLeft = function (o, rel, abs) {
	        var x = fsoft.utils.Browser.ie ? this.get_ie_adjusted_offsetleft(o) : this.get_nonie_adjusted_offsetleft(o);
	        if (rel) {
	            var offsetLeft = this.cart_getoffset_x(o, "relative");
	            x -= offsetLeft;
	        }
	        if (abs) {
	            var offsetLeft = this.cart_getoffset_x(o, "absolute");
	            x -= offsetLeft;
	        }

	        if (rel || abs)
	            x = x < 0 ? 0 : x;
	        return x;
	    };

	    this.getAdjustedOffsetTop = function (o, rel, abs) {
	        var y = fsoft.utils.Browser.ie ? this.get_ie_adjusted_offsettop(o) : this.get_nonie_adjusted_offsettop(o);
	        if (rel) {
	            var offsetTop = this.cart_getoffset_y(o, "relative");
	            y -= offsetTop;
	        }
	        if (abs) {
	            var offsetTop = this.cart_getoffset_y(o, "absolute");
	            y -= offsetTop;
	        }

	        if (rel || abs)
	            y = y < 0 ? 0 : y;
	        return y;
	    };

		this.cart_getoffset_x = function (o, position) {
	        while (o.parentNode && o.parentNode != document.body) {
	            if (fsoft.utils.Browser.ie && o.currentStyle) {
	                if (o.currentStyle.position == position)
	                    return position == "absolute" ? parseInt(o.currentStyle.left) : this.getAdjustedOffsetLeft(o);
	            } else {
					if (document.defaultView && document.defaultView.getComputedStyle) {
	                    var style = document.defaultView.getComputedStyle(o, "");
	                    if (style.position == position)
	                        return position == "absolute" ? parseInt(style.getPropertyValue("left")) : this.getAdjustedOffsetLeft(o);
	                }
	            }
	            o = o.parentNode;
	        }
	        return 0;
	    };

		this.cart_getoffset_y = function (o, position) {
	        while (o.parentNode && o.parentNode != document.body) {
	            if (fsoft.utils.Browser.ie && o.currentStyle) {
	                if (o.currentStyle.position == position)
	                    return position == "absolute" ? parseInt(o.currentStyle.top) : this.getAdjustedOffsetTop(o);
	            } else {
					if (document.defaultView && document.defaultView.getComputedStyle) {
	                    var style = document.defaultView.getComputedStyle(o, "");
	                    if (style.position == position)
	                        return position == "absolute" ? parseInt(style.getPropertyValue("top")) : this.getAdjustedOffsetTop(o);
	                }
	            }
	            o = o.parentNode;
	        }
	        return 0;
	    };

		this.get_ie_adjusted_offsetleft = function (o) {
	        return (fsoft.utils.Browser.iemac ? this.get_iemac_offsetleft(o) : fsoft.utils.Browser.ie4 ? this.get_ie4_offsetleft(o) : this.get_ie4plus_offsetleft(o));
	    };

		this.get_ie_adjusted_offsettop = function (o) {
	        return (fsoft.utils.Browser.iemac ? this.get_iemac_offsettop(o) : fsoft.utils.Browser.ie4 ? this.get_ie4_offsettop(o) : this.get_ie4plus_offsettop(o));
	    };

		this.get_ie4plus_offsetleft = function (o) {
	        var x = 0;
	        while (typeof(o) != "undefined" && typeof(o.offsetParent) != "unknown" && o.offsetParent != null) {
	            x += o.offsetLeft;
	            if (o.offsetParent.tagName != "TABLE" && o.offsetParent.tagName != "TD" && o.offsetParent.tagName != "TR" && o.offsetParent.currentStyle != null) {
	                var borderLeftWidth = parseInt(o.offsetParent.currentStyle.borderLeftWidth);
	                if (!isNaN(borderLeftWidth))
	                    x += borderLeftWidth;
	            }
	            if (o.offsetParent.tagName == "TABLE" && o.offsetParent.border > 0)
	                x += 1;
	            o = o.offsetParent;
	        }
	        if (!fsoft.utils.Browser.ie8 && document.compatMode == "CSS1Compat" && o == document.body) {
	            var marginLeft = parseInt(o.currentStyle.marginLeft);
	            if (!isNaN(marginLeft))
	                x += marginLeft;
	        }
	        return x;
	    };

		this.get_ie4plus_offsettop = function (o) {
	        var y = 0;
	        while (typeof(o) != "undefined" && typeof(o.offsetParent) != "unknown" && o.offsetParent != null) {
	            y += o.offsetTop;
	            if (o.offsetParent.tagName != "TABLE" && o.offsetParent.tagName != "TD" && o.offsetParent.tagName != "TR" && o.offsetParent.currentStyle != null) {
	                var borderTopWidth = parseInt(o.offsetParent.currentStyle.borderTopWidth);
	                if (!isNaN(borderTopWidth))
	                    y += borderTopWidth;
	            }
	            if (o.offsetParent.tagName == "TABLE" && o.offsetParent.border > 0)
	                y += 1;
	            o = o.offsetParent;
	        }
	        if (!fsoft.utils.Browser.ie8 && document.compatMode == "CSS1Compat" && o == document.body) {
	            var marginTop = parseInt(o.currentStyle.marginTop);
	            if (!isNaN(marginTop))
	                y += marginTop;
	        }
	        return y;
	    };

		this.get_ie4_offsetleft = function (o) {
	        var x = 0;
	        while (o != document.body) {
	            x += o.offsetLeft;
	            o = o.offsetParent;
	        }
	        return x;
	    };

		this.get_ie4_offsettop = function (o) {
	        var y = 0;
	        while (o != document.body) {
	            y += o.offsetTop;
	            o = o.offsetParent;
	        }
	        return y;
	    };

		this.get_iemac_offsetleft = function (o) {
	        var x = 0;
	        while (o.offsetParent != document.body) {
	            x += o.offsetLeft;
	            o = o.offsetParent;
	        }
	        x += (o.offsetLeft + this.getVirtualOffsetLef());
	        return x;
	    };

		this.get_iemac_offsettop = function (o) {
	        var y = 0;
	        while (o.offsetParent != document.body) {
	            y += o.offsetTop;
	            o = o.offsetParent;
	        }
	        y += (o.offsetTop + this.getVirtualOffsetTop());
	        return y;
	    };

		this.getVirtualOffsetLef = function () {
	        if (this.virtualOffsetLeft == null) {
	            if (!document.all["runtime_virtual_offset"])
	                this.createVirtualPositionElement();
	            this.virtualOffsetLeft = -document.all["runtime_virtual_offset"].offsetLeft;
	        }
	        return this.virtualOffsetLeft;
	    };

		this.getVirtualOffsetTop = function () {
	        if (this.virtualOffsetTop == null) {
	            if (!document.all["runtime_virtual_offset"])
	                this.createVirtualPositionElement();
	            this.virtualOffsetTop = -document.all["runtime_virtual_offset"].offsetTop;
	        }
	        return this.virtualOffsetTop;
	    };

		this.createVirtualPositionElement = function () {
	        document.body.insertAdjacentHTML("beforeEnd", "<div id=\"runtime_virtual_offset\" style=\"position:absolute;left:0;top:0;z-index:-1000;visibility:hidden\">*</div>");
	    };

		this.get_nonie_adjusted_offsetleft = function (offsetElement) {
	        var x = 0;
	        do {
	            x += offsetElement.offsetLeft;
	            if (offsetElement.offsetParent) {
	                if (offsetElement.offsetParent.tagName == "TABLE" && !fsoft.utils.Browser.safari && !fsoft.utils.Browser.konqueror) {
	                    if (parseInt(offsetElement.offsetParent.border) > 0)
	                        x += 1;
	                }
	            }
	        }
			while ((offsetElement = offsetElement.offsetParent));

			return (fsoft.utils.Browser.konqueror ? x + this.getMarginLeft() : x);
	    };

		this.get_nonie_adjusted_offsettop = function (offsetElement) {
	        var y = 0;
	        do {
	            y += offsetElement.offsetTop;
	            if (offsetElement.offsetParent) {
	                if (offsetElement.offsetParent.tagName == "TABLE" && !fsoft.utils.Browser.safari && !fsoft.utils.Browser.konqueror) {
	                    if (parseInt(offsetElement.offsetParent.border) > 0)
	                        y += 1;
	                }
	            }
	        }
			while ((offsetElement = offsetElement.offsetParent));

	        return (fsoft.utils.Browser.konqueror ? y + this.getMarginTop() : y);
	    };

		this.getMarginLeft = function () {
	        if (this.virtualOffsetLeft == null)
	            this.virtualOffsetLeft = this.getDocumentMarginLeft();
	        return this.virtualOffsetLeft;
	    };

		this.getMarginTop = function () {
	        if (this.virtualOffsetTop == null)
	            this.virtualOffsetTop = this.getDocumentMarginTop();
	        return this.virtualOffsetTop;
	    };

		this.getDocumentMarginLeft = function () {
	        if (!isNaN(parseInt(document.body.style.marginLeft)))
	            return parseInt(document.body.style.marginLeft);
	        if (!isNaN(parseInt(document.body.style.margin)))
	            return parseInt(document.body.style.margin);
	        if (!isNaN(parseInt(document.body.leftMargin)))
	            return parseInt(document.body.leftMargin);
	        return 10;
	    };

		this.getDocumentMarginTop = function () {
	        if (!isNaN(parseInt(document.body.style.marginTop)))
	            return parseInt(document.body.style.marginTop);
	        if (!isNaN(parseInt(document.body.style.margin)))
	            return parseInt(document.body.style.margin);
	        if (!isNaN(parseInt(document.body.topMargin)))
	            return parseInt(document.body.topMargin);
	        return 10;
	    };

		this.virtualOffsetLeft = null;
	    this.virtualOffsetTop = null;
	};

	fsoft.utils.SlideType = {NONE: 0, EXPONENTIAL_ACCELERATE: 1, EXPONENTIAL_DECELERATE: 2, FADE: 3, QUADRATIC_ACCELERATE: 4, QUADRATIC_DECELERATE: 5};
	fsoft.utils.SlidePortion = new function() {
		this.completed = function (startTime, fadeDuration, slideType) {
			if (slideType == fsoft.utils.SlideType.NONE || startTime >= fadeDuration)
				return 1;
			var isAccelerate = (slideType == fsoft.utils.SlideType.EXPONENTIAL_ACCELERATE) || (slideType == fsoft.utils.SlideType.QUADRATIC_ACCELERATE);
			if (isAccelerate)
				startTime = fadeDuration - startTime;
			var slideRate = startTime / fadeDuration;
			var slideStep;
			switch (slideType) {
				case fsoft.utils.SlideType.FADE:
					slideStep = slideRate;
					break;
				case fsoft.utils.SlideType.EXPONENTIAL_ACCELERATE:
				case fsoft.utils.SlideType.EXPONENTIAL_DECELERATE:
					slideStep = 1 - Math.pow(1 / 300, slideRate);
					break;
				case fsoft.utils.SlideType.QUADRATIC_ACCELERATE:
				case fsoft.utils.SlideType.QUADRATIC_DECELERATE:
					slideStep = Math.pow(slideRate, 2);
					break;
			}
			if (isAccelerate)
				slideStep = 1 - slideStep;
			return Math.min(Math.max(0, slideStep), 1);
		};
	};

    fsoft.utils.Fader = function () {
	}

    fsoft.utils.Fader.prototype.start = function() {
        if (this.template) {
            if (this.fadeDuration) {
                if (this.fadePanel)
                    fsoft.utils.removeNode(this.fadePanel);
                this.fadePanel = document.createElement("div");
                this.fadePanel.style.zIndex = 90210;
                this.fadePanel.style.position = "absolute";
                this.fadePanel.style.width = fsoft.utils.Size.getAdjustedWidth(this.fadeElement) + "px";
                this.fadePanel.style.height = fsoft.utils.Size.getAdjustedHeight(this.fadeElement) + "px";
                this.fadePanel.style.left = fsoft.utils.Offset.getAdjustedOffsetLeft(this.fadeElement) + "px";
                this.fadePanel.style.top = fsoft.utils.Offset.getAdjustedOffsetTop(this.fadeElement) + "px";
                this.fadePanel.innerHTML = this.template;
                this.fadePanel.fadeStartTime = (new Date());
                if (fsoft.utils.Browser.ie) {
                    this.fadePanel.style.filter = "alpha(opacity=0)";
                } else {
                    this.fadePanel.style.opacity = 0;
                    this.fadePanel.style.setProperty("-moz-opacity", 0, "");
                }
                document.body.insertBefore(this.fadePanel, document.body.firstChild);
                if (arguments != null && arguments.length == 1 && arguments[0] === true)
					this.fade(true);
				else
					this.fade(false);
            } else {
                this.fadeElement.innerHTML = this.template;
            }
        }
    };

    fsoft.utils.Fader.prototype.fade = function (fadein) {
        if (this.fadePanel) {
            var fadeStartTime = (new Date()).getTime() - this.fadePanel.fadeStartTime;
            var duration = fsoft.utils.SlidePortion.completed(fadeStartTime, this.fadeDuration, 2);
            var opacity = fadein ? duration : (1 - duration);
            opacity = (opacity * Math.max(0, Math.min(100, this.fadeMaxOpacity))) / 100;
            if (fsoft.utils.Browser.ie) {
                this.fadePanel.style.filter = "alpha(opacity=" + (opacity * 100) + ")";
            } else {
                this.fadePanel.style.opacity = opacity;
                this.fadePanel.style.setProperty("-moz-opacity", opacity, "");
            }
            if (opacity == 0 || opacity == 1) {
				if (typeof(this.onComplete) == "function")
					this.onComplete(fadein);
            }
            if (duration == 1) {
                if (!fadein) {
                    fsoft.utils.removeNode(this.fadePanel);
                    this.fadePanel = null;
                }
            } else {
				if (this.fadeTimeout)
                    clearTimeout(this.fadeTimeout);
                var self = this;
                var f = function() {
					self.fade(fadein);
				};

                this.fadeTimeout = setTimeout(f, 20);
            }
        }
    };

	fsoft.utils.Timer = function(tick, callback) {
		this.id = null;
		this.tick = tick;
		this.callback = callback;
		this.status = fsoft.utils.TimerStatus.CLEAR;
		this.args = null;
		this.timeElapsed = 0;
		this.maxTime = 0;
	}

	fsoft.utils.TimerStatus = {
		CLEAR: 0,
		STARTED: 1,
		STOPPED: 2
	};

	fsoft.utils.Timer.prototype.start = function() {
		var args = this.args = arguments;
		var self = this;
		if (this.status == fsoft.utils.TimerStatus.CLEAR || this.status == fsoft.utils.TimerStatus.STOPPED) {
			var cb = function() {
				if (typeof(self.callback) == "function")
					self.callback(args);
				if (self.maxTime > 0) {
					self.timeElapsed += self.tick;

					if (self.timeElapsed > self.maxTime)
						self.stop();
				}
			};
			this.id = setInterval(cb, this.tick);
			this.status = fsoft.utils.TimerStatus.STARTED;
		}
	}

	fsoft.utils.Timer.prototype.stop = function() {
		if (this.status == fsoft.utils.TimerStatus.STARTED) {
			window.clearInterval(this.id);
			this.status = fsoft.utils.TimerStatus.STOPPED;
			this.timeElapsed = 0;
		}
	}

	fsoft.utils.Timer.prototype.setTick = function(tick) {
		this.tick = tick;
		if (this.status == fsoft.utils.TimerStatus.STARTED) {
			this.stop();
			this.start(this.args);
		}
	}

	window._FSOFT_UTILS_LOADED = true;
}