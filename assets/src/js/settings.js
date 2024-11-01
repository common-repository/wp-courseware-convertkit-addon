document.addEventListener( 'DOMContentLoaded', function () {
	let button = document.getElementById( 'wpcw-clear-convertkit-cache' );
	if ( button ) {
		buttonHtml = button.innerHTML,
			buttonLoadingText = button.getAttribute( 'data-loading' )

		button.onclick = function ( e ) {
			e.preventDefault();

			button.innerHTML = '<i class="wpcw-fas wpcw-fa-spinner wpcw-fa-spin left" style="margin-right: 12px;"></i>' + buttonLoadingText
			button.classList.add( 'disabled' );

			window.WpcwApi.post( 'convertkit-clear-cache' )
			      .then( ( data ) => {
				      setTimeout( () => {
					      button.innerHTML = buttonHtml
					      button.classList.remove( 'disabled' );
				      }, 1000 )
			      } )
			      .catch( error => {
				      setTimeout( () => {
					      button.innerHTML = buttonHtml
					      button.classList.remove( 'disabled' );
				      }, 1000 )
			      } )
		};
	}
} );
