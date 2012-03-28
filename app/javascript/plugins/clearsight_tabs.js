/*
 * jQuery hashchange event - v1.3 - 7/21/2010
 * http://benalman.com/projects/jquery-hashchange-plugin/
 * 
 * Copyright (c) 2010 "Cowboy" Ben Alman
 * Dual licensed under the MIT and GPL licenses.
 * http://benalman.com/about/license/
 */
(function($,e,b){var c="hashchange",h=document,f,g=$.event.special,i=h.documentMode,d="on"+c in e&&(i===b||i>7);function a(j){j=j||location.href;return"#"+j.replace(/^[^#]*#?(.*)$/,"$1")}$.fn[c]=function(j){return j?this.bind(c,j):this.trigger(c)};$.fn[c].delay=50;g[c]=$.extend(g[c],{setup:function(){if(d){return false}$(f.start)},teardown:function(){if(d){return false}$(f.stop)}});f=(function(){var j={},p,m=a(),k=function(q){return q},l=k,o=k;j.start=function(){p||n()};j.stop=function(){p&&clearTimeout(p);p=b};function n(){var r=a(),q=o(m);if(r!==m){l(m=r,q);$(e).trigger(c)}else{if(q!==m){location.href=location.href.replace(/#.*/,"")+q}}p=setTimeout(n,$.fn[c].delay)}$.browser.msie&&!d&&(function(){var q,r;j.start=function(){if(!q){r=$.fn[c].src;r=r&&r+a();q=$('<iframe tabindex="-1" title="empty"/>').hide().one("load",function(){r||l(a());n()}).attr("src",r||"javascript:0").insertAfter("body")[0].contentWindow;h.onpropertychange=function(){try{if(event.propertyName==="title"){q.document.title=h.title}}catch(s){}}}};j.stop=k;o=function(){return a(q.location.href)};l=function(v,s){var u=q.document,t=$.fn[c].domain;if(v!==s){u.title=h.title;u.open();t&&u.write('<script>document.domain="'+t+'"<\/script>');u.close();q.location.hash=v}}})();return j})()})(jQuery,this);

/**
 *	ClearSight Tabs Plugin
 *
 *	Usage (all options are optional and defaults are shown)
 *	
 *	$("#tab-container-element").clearsight_tabs({
 *		'tabClass' : 'tabs',
 *		'panelClass' : 'panel',
 *		'panelSuffix' : '-panel',
 *		'panelShownEvent' : 'panelShown',
 *	});
 *	
 */
(function($){
	$.fn.clearsight_tabs = function(options) {
		var $container = $(this);
		
		var tabClass = ".tabs"; if(options.tabClass) tabClass = "." + options.tabClass;
		var panelClass = ".panel"; if(options.panelClass) panelClass = "." + options.panelClass;
		var panelSuffix = "-panel"; if(options.panelSuffix) panelSuffix = options.panelSuffix;
		var panelShownEvent = "panelShown"; if(options.panelShownEvent) panelShownEvent = options.panelShownEvent;
		var currentLinkClass = "current"; if(options.currentLinkClass) currentLinkClass = options.currentLinkClass;
		
		// Open correct panel when tab is clicked
		$container.delegate(tabClass + " a", "click", function() {
			var anchorId = $(this).attr("href");
			var panelId = anchorId + panelSuffix;
			var $panel = $(panelId);
			
			$container.find(panelClass + ":visible").hide();
			
			$container.find("." + currentLinkClass).removeClass(currentLinkClass);
			$container.find("a[href=" + anchorId + "]").addClass(currentLinkClass);
			
			$panel.show().trigger(panelShownEvent); // for custom events
			$(window).trigger("scroll"); // if the height changed
		});
		
		// Set initial tab
		function set_tab() {
			var myFile = document.location.toString();
			if (myFile.match('#')) {
				var anchorName = myFile.split('#')[1];
				$container.find(".tabs a[href='#" + anchorName + "']").trigger("click");
			} else {
				$container.find(".tabs a:first").trigger("click");
			}
		} set_tab();
		
		// Browser Back Button
		$(window).hashchange(function() {
			set_tab()												
		});
	}
})(jQuery);


