/* global screenReaderText */
/**
 * Theme functions file.
 *
 * Contains handlers for navigation and widget area.
 */

( function( $ ) {

	$( document ).ready( function() {
				 
		 $('.chekalert').click(function(e) {
			
			var idnob = this.id;
			if(this.checked)  
			{
				
				$('.'+idnob).css('display','block');
			}else
			{
				$('.'+idnob).css('display','none');
			}
        });
	} );
} )( jQuery );
