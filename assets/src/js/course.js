import WpcwFieldConvertkitselect from './components/WpcwFieldConvertkitselect'
import WpcwFieldConvertkitwebhooks from './components/WpcwFieldConvertkitwebhooks'

if ( window.WpcwVue ) {
	document.addEventListener( 'DOMContentLoaded', function ( event ) {
		// Select Field Component.
		WpcwVue.component( 'wpcw-field-convertkitselect', WpcwFieldConvertkitselect );

		// Webhooks Field Component.
		WpcwVue.component( 'wpcw-field-convertkitwebhooks', WpcwFieldConvertkitwebhooks );

		// Forms.
		const convertkitForms = new WpcwVue( {
			el: '#convertkit-forms'
		} );

		// Sequences.
		const convertkitSequences = new WpcwVue( {
			el: '#convertkit-sequences'
		} );

		// Tags.
		const convertkitTags = new WpcwVue( {
			el: '#convertkit-tags'
		} );

		// Webhooks.
		const convertkitWebhooks = new WpcwVue( {
			el: '#convertkit-webhooks'
		} );
	} );
}
