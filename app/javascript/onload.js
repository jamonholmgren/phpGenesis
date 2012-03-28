(function($){
	// jQuery code here









})(window.jQuery);



window.log = function(){
  log.history = log.history || [];
  log.history.push(arguments);
  if(this.console){
    console.log( Array.prototype.slice.call(arguments) );
  }
};

jQuery.fn.exists = function(){return jQuery(this).length>0;} // Usage: if($(".element").exists()) { /* do something */ }