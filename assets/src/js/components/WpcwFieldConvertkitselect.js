export default {
	template: '#wpcw-field-convertkitselect',

	props: {
		id: { default: '' },
		name: { default: '' },
		placeholder: { default: '' },
		objects: { default: '' },
		object_type: { default: '' },
	},

	data() {
		return {
			selectData: '',
			select: '',
		}
	},

	created() {
		WpcwEvents.$on( 'addObject', data => this.addObject( data ) )
		WpcwEvents.$on( 'removeObject', data => this.removeObject( data ) )
	},

	mounted() {
		this.objects = JSON.parse( this.objects )

		this.loadData()
	},

	computed: {
		fieldId() {
			return `wpcw-field-convertkitselect-${ this.object_type }-dropdown`;
		}
	},

	methods: {
		loadData() {
			WpcwApi.get( 'convertkit-' + this.object_type )
			       .then( ( data ) => {
				       this.selectData = this.formatForSelect2( data.objects )
				       this.initSelect()
			       } )
		},
		initSelect() {
			let self = this,
				objectsSelect = $( '#wpcw-field-convertkitselect-' + self.object_type + '-dropdown' ),
				objectsSelectAllowClear = objectsSelect.data( 'allow_clear' ) ? true : false,
				objectsSelectPlaceholder = objectsSelect.data( 'placeholder' );

			self.select = objectsSelect.wpcwselect2( {
				theme: 'wpcw',
				multiple: true,
				allowClear: objectsSelectAllowClear,
				placeholder: objectsSelectPlaceholder,
				width: '100%',
				data: self.selectData
			} ).on( 'wpcwselect2:select', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'addObject', { id: data.id, text: data.text } )
			} ).on( 'wpcwselect2:unselect', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'removeObject', { id: data.id, text: data.text } )
			} )
		},
		formatForSelect2( data ) {
			let self = this,
				formattedData = [];

			$.each( data, function ( id, name ) {
				let itemFound = self.objects.find( ( item ) => item === id )
				formattedData.push( { id: id, text: name, selected: itemFound ? true : false } );
			} );

			return formattedData
		},
		addObject( data ) {
			this.objects.push( data )
		},
		removeObject( data ) {
			this.objects = this.objects.filter( item => parseInt( item.id ) !== parseInt( data.id ) )
		},
	}
}
