export default {
	template: '#wpcw-field-convertkitwebhooks',
	props: {
		course_id: { default: 0 },
	},
	data() {
		return {
			name: '',
			type: '',
			tag: '',
			form: '',
			sequence: '',
			typeSelect: '',
			objectSelect: '',
			selectType: '',
			selectData: '',
			args: {},
			webhooks: [],
			create: false,
			creating: false,
			saving: false,
		}
	},
	created() {
		WpcwEvents.$on( 'modalOpened', data => this.modalActivated() )
		WpcwEvents.$on( 'modalClose', data => this.modalDeactivated() )
		WpcwEvents.$on( 'typeSelected', ( { type } ) => this.typeSelected( type ) )
		WpcwEvents.$on( 'typeUnselected', ( { type } ) => this.typeUnSelected( type ) )
		WpcwEvents.$on( 'dataSelected', data => this.dataSelected( data ) )
		WpcwEvents.$on( 'dataUnSelected', data => this.dataUnSelected( data ) )
	},
	mounted() {
		this.getWebhooks()
	},
	computed: {
		isTagsNeeded() {
			return this.selectType === 'tags' ? true : false;
		},
		isFormsNeeded() {
			return this.selectType === 'forms' ? true : false;
		},
		isSequencesNeeded() {
			return this.selectType === 'sequences' ? true : false;
		}
	},
	methods: {
		getWebhooks() {
			WpcwApi.get( 'convertkit-webhooks', {
				course_id: this.course_id
			} ).then( ( data ) => {
				this.webhooks = data.webhooks
			} )
		},
		createWebhook() {
			WpcwModal.open( '#wpcw-field-convertkitwebhooks-modal', {}, true )
		},
		initTypeSelect() {
			let self = this,
				objectsSelect = $( "#wpcw-field-convertkitselect-type-dropdown" ),
				objectsSelectAllowClear = objectsSelect.data( 'allow_clear' ) ? true : false,
				objectsSelectPlaceholder = objectsSelect.data( 'placeholder' );

			this.typeSelect = objectsSelect.wpcwselect2( {
				theme: 'wpcw',
				multiple: false,
				allowClear: objectsSelectAllowClear,
				placeholder: objectsSelectPlaceholder,
				width: '100%',
			} ).on( 'wpcwselect2:select', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'typeSelected', { type: data.id } )
			} ).on( 'wpcwselect2:unselect', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'typeUnselected', { type: data.id } )
			} )
		},
		typeSelected( type ) {
			this.type = type

			switch ( type ) {
				case 'tag_add' :
				case 'tag_remove' :
					this.selectType = 'tags';
					break;
				case 'form' :
					this.selectType = 'forms';
					break;
				case 'sequence' :
				case 'sequence_complete' :
					this.selectType = 'sequences';
					break;
				default :
					this.selectType = ''
					break;
			}

			this.loadObjectsData();
		},
		typeUnSelected( type ) {
			this.reset()
		},
		loadObjectsData() {
			if ( '' === this.selectType ) {
				return;
			}

			WpcwApi.get( 'convertkit-' + this.selectType )
			       .then( ( data ) => {
				       this.selectData = this.formatForSelect2( data.objects )
				       this.initObjectsSelect()
			       } )
		},
		initObjectsSelect() {
			let self = this,
				objectsSelect = $( '#wpcw-field-convertkitselect-' + self.selectType + '-dropdown' ),
				objectsSelectAllowClear = objectsSelect.data( 'allow_clear' ) ? true : false,
				objectsSelectPlaceholder = objectsSelect.data( 'placeholder' );

			this.objectSelect = objectsSelect.wpcwselect2( {
				theme: 'wpcw',
				multiple: false,
				allowClear: objectsSelectAllowClear,
				placeholder: objectsSelectPlaceholder,
				width: '100%',
				data: self.selectData
			} ).on( 'wpcwselect2:select', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'dataSelected', { id: data.id, type: self.selectType } )
			} ).on( 'wpcwselect2:unselect', function ( event ) {
				var data = event.params.data;
				WpcwEvents.$emit( 'dataUnselected', { id: data.id, type: self.selectType } )
			} )
		},
		formatForSelect2( data ) {
			let self = this,
				formattedData = [];

			$.each( data, function ( id, name ) {
				formattedData.push( { id: id, text: name, selected: false } );
			} );

			return formattedData
		},
		dataSelected( data ) {
			if ( !data.id || !data.type ) {
				return;
			}

			switch ( data.type ) {
				case 'forms' :
					this.form = data.id;
					break;
				case 'tags' :
					this.tag = data.id;
					break;
				case 'sequences' :
					this.sequence = data.id;
					break;
			}
		},
		dataUnSelected( data ) {
		},
		modalActivated() {
			this.create = true
			this.initTypeSelect()
		},
		modalDeactivated() {
			this.create = false
			this.reset()
		},
		reset() {
			this.type = ''
			this.tag = ''
			this.form = ''
			this.sequence = ''
			this.selectType = ''
			this.selectData = ''
			if ( this.typeSelect ) {
				this.typeSelect.val( '' ).trigger( 'change' )
			}
			if ( this.objectSelect ) {
				this.objectSelect.val( '' ).trigger( 'change' )
			}
		},
		addWebhook() {
			this.creating = true

			WpcwApi.post( 'convertkit-create-webhook', {
				course_id: this.course_id,
				name: this.name,
				type: this.type,
				tag: this.tag,
				form: this.form,
				sequence: this.sequence
			} ).then( ( data ) => {
				this.creating = false
				if ( data.webhooks ) {
					this.webhooks = data.webhooks
					this.name = ''
					this.reset()
					WpcwModal.close();
				}
			} )
		},
		deleteWebhook( webhook ) {
			webhook.delete = true

			WpcwApi.post( 'convertkit-delete-webhook', {
				course_id: this.course_id,
				webhook_id: webhook.id,
			} ).then( ( data ) => {
				if ( data.webhooks ) {
					this.webhooks = data.webhooks
				} else {
					webhook.delete = false
				}
			} )
		}
	}
}
