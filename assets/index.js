/**
 * PMPro RH Admin JS
 *
 * @package pmprorha
 */
( function( $ ) {
	$( '#field_type_select' ).change( function() {
		var $thisVal = $( this ).val();
		var typesWithOptions = [
			'select',
			'select2',
			'multiselect',
			'checkbox_grouped',
			'radio'
		];
		var showOptions = false;
		var i;
		for ( i = 0; i < typesWithOptions.length; i++ ) {
			if ( typesWithOptions[i] === $thisVal ) {
				showOptions = true;
			}
		}
		if ( showOptions ) {
			$( '#field_select_options_wrapper' ).show();
		} else {
			$( '#field_select_options_wrapper' ).hide();
		}
	} );
	$( '#field_text_label' ).keyup( function() {
		var $thisVal = $( this )
			.val()
			.toLowerCase()
			.replace( /( |\.|-)/gim, '_' )
			.replace( /(,|!|\?)/gim, '' );
		$( '#field_text_id' ).val( $thisVal );
	} );
}( jQuery ) );
