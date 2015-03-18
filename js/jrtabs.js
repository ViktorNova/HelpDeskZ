( function($) {
	$.fn.jrtabs = function(){
			this.each(function(){
				var $this = jQuery(this);
				$this.children("ul").addClass("evotabs");
				$this.children("div").addClass("evotabs_content");
				childtab = 1;
				if(window.location.hash) {
					var hash = window.location.hash.substring(1); //Puts hash in variable, and removes the # character
					var childtab = $this.children("div#"+hash).index();
				}
				if(childtab < 1){
					childtab = 1;	
				}
				$this.jrchangetab(childtab);
				$this.children("ul").children("li").click(function(){
					newchildtab = $(this).index()+1;
					$this.jrchangetab(newchildtab);
				});
			});
	}
	$.fn.jrchangetab = function(tabn){
			this.each(function(){
				var $this = jQuery(this);
				$this.children("ul").children("li").removeClass("active");
				$this.children("div").hide();
				newchildtab = $(this).index()+1;
				$this.children("ul").children("li:nth-child("+tabn+")").addClass('active');
				$this.children("div:nth-of-type("+tabn+")").show();
			});
	}
})(jQuery);