( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var createElement = element.createElement;
	var __ = i18n.__;
	var defaultLanguage = ( window.itmmsBlockData && window.itmmsBlockData.defaultLanguage ) || 'en';

	var languageOptions = [
		{ label: __( 'English', 'masjidos' ), value: 'en' },
		{ label: __( 'Bangla', 'masjidos' ), value: 'bn' },
		{ label: __( 'Arabic', 'masjidos' ), value: 'ar' }
	];

	function previewCard( icon, title, rows ) {
		return createElement( 'div', {
			key: 'preview',
			style: {
				padding: '20px',
				border: '1px solid #1a6b5a',
				borderRadius: '8px',
				background: '#f4f7f6',
				color: '#1a6b5a',
				fontFamily: 'sans-serif'
			}
		},
			createElement( 'div', {
				style: {
					display: 'flex',
					alignItems: 'center',
					gap: '10px',
					marginBottom: '10px',
					borderBottom: '1px solid rgba(26, 107, 90, 0.2)',
					paddingBottom: '8px'
				}
			},
				createElement( 'span', { className: 'dashicons dashicons-' + icon } ),
				createElement( 'h3', { style: { margin: 0, color: '#1a6b5a' } }, title )
			),
			createElement( 'div', { style: { fontSize: '13px', color: '#333' } },
				rows.map( function ( row, index ) {
					return createElement( 'p', { key: index, style: { margin: '4px 0' } },
						createElement( 'strong', null, row.label ),
						row.value
					);
				} ).concat( [
					createElement( 'p', {
						key: 'note',
						style: { margin: '10px 0 0 0', fontStyle: 'italic', color: '#666', fontSize: '11px' }
					}, __( '[Renders live widget on frontend]', 'masjidos' ) )
				] )
			)
		);
	}

	function yesNoToggle( label, value, onChange ) {
		return createElement( ToggleControl, {
			label: label,
			checked: value === 'yes',
			onChange: function ( checked ) {
				onChange( checked ? 'yes' : 'no' );
			}
		} );
	}

	function registerMasjidBlock( config ) {
		registerBlockType( config.name, {
			title: config.title,
			description: config.description,
			icon: config.icon,
			category: 'widgets',
			keywords: config.keywords || [ 'masjid', 'mosque', 'islam' ],
			attributes: config.attributes,
			edit: function ( props ) {
				var attributes = props.attributes;
				var setAttributes = props.setAttributes;
				var controls = config.inspector( attributes, setAttributes );
				var rows = config.previewRows( attributes );

				return [
					createElement( InspectorControls, { key: 'inspector' },
						createElement( PanelBody, { title: config.panelTitle || __( 'Widget Settings', 'masjidos' ), initialOpen: true },
							controls
						)
					),
					previewCard( config.icon, config.title, rows )
				];
			},
			save: function () {
				return null;
			}
		} );
	}

	registerMasjidBlock( {
		name: 'masjidos/prayer-times',
		title: __( 'MasjidOS Prayer Times', 'masjidos' ),
		description: __( 'Display daily prayer times, countdown, Qibla compass, and timezone details.', 'masjidos' ),
		icon: 'clock',
		keywords: [ 'prayer', 'mosque', 'masjid', 'namaz' ],
		attributes: {
			title: { type: 'string', default: __( 'Prayer Times', 'masjidos' ) },
			design: { type: 'string', default: 'classic' },
			language: { type: 'string', default: defaultLanguage },
			qibla: { type: 'string', default: 'yes' },
			meta: { type: 'string', default: 'yes' },
			iqamah: { type: 'string', default: 'yes' },
			hijri: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				yesNoToggle( __( 'Show Qibla', 'masjidos' ), attributes.qibla, function ( value ) { setAttributes( { qibla: value } ); } ),
				yesNoToggle( __( 'Show Calculations Meta', 'masjidos' ), attributes.meta, function ( value ) { setAttributes( { meta: value } ); } ),
				yesNoToggle( __( 'Show Iqamah Times', 'masjidos' ), attributes.iqamah, function ( value ) { setAttributes( { iqamah: value } ); } ),
				yesNoToggle( __( 'Show Hijri Date', 'masjidos' ), attributes.hijri, function ( value ) { setAttributes( { hijri: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title || '(' + __( 'None', 'masjidos' ) + ')' },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/islamic-calendar',
		title: __( 'MasjidOS Islamic Calendar', 'masjidos' ),
		description: __( 'Display a dual Gregorian + Hijri calendar with Islamic holy days and events.', 'masjidos' ),
		icon: 'calendar-alt',
		keywords: [ 'calendar', 'hijri', 'islamic', 'events' ],
		panelTitle: __( 'Calendar Settings', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Islamic Calendar', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title || '(' + __( 'None', 'masjidos' ) + ')' },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/monthly-prayer-times',
		title: __( 'MasjidOS Monthly Timetable', 'masjidos' ),
		description: __( 'Show a full-month prayer timetable with navigation and print support.', 'masjidos' ),
		icon: 'calendar',
		attributes: {
			title: { type: 'string', default: __( 'Monthly Prayer Timetable', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'table' },
			iqamah: { type: 'string', default: 'no' },
			navigation: { type: 'string', default: 'yes' },
			extras: { type: 'string', default: 'no' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Table', 'masjidos' ), value: 'table' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				yesNoToggle( __( 'Show Iqamah', 'masjidos' ), attributes.iqamah, function ( value ) { setAttributes( { iqamah: value } ); } ),
				yesNoToggle( __( 'Month Navigation', 'masjidos' ), attributes.navigation, function ( value ) { setAttributes( { navigation: value } ); } ),
				yesNoToggle( __( 'Show Ishraq / Zawal', 'masjidos' ), attributes.extras, function ( value ) { setAttributes( { extras: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/jumuah',
		title: __( 'MasjidOS Jumuah', 'masjidos' ),
		description: __( 'Display Friday prayer sessions, khutbah times, and khatib details.', 'masjidos' ),
		icon: 'groups',
		attributes: {
			title: { type: 'string', default: __( 'Jumuah Prayer', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'classic' },
			meta: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				yesNoToggle( __( 'Show Meta', 'masjidos' ), attributes.meta, function ( value ) { setAttributes( { meta: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/announcements',
		title: __( 'MasjidOS Notices', 'masjidos' ),
		description: __( 'Show active mosque notices and announcements.', 'masjidos' ),
		icon: 'megaphone',
		attributes: {
			title: { type: 'string', default: __( 'Masjid Notices', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'list' },
			type: { type: 'string', default: 'all' },
			limit: { type: 'string', default: '5' },
			show_date: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'List', 'masjidos' ), value: 'list' },
						{ label: __( 'Ticker', 'masjidos' ), value: 'ticker' },
						{ label: __( 'Banner', 'masjidos' ), value: 'banner' },
						{ label: __( 'Popup', 'masjidos' ), value: 'popup' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'type',
					label: __( 'Type filter', 'masjidos' ),
					value: attributes.type || 'all',
					options: [
						{ label: __( 'All types', 'masjidos' ), value: 'all' },
						{ label: __( 'General', 'masjidos' ), value: 'general' },
						{ label: __( 'Urgent', 'masjidos' ), value: 'urgent' },
						{ label: __( 'Jumuah', 'masjidos' ), value: 'jumuah' }
					],
					onChange: function ( value ) { setAttributes( { type: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} ),
				yesNoToggle( __( 'Show Dates', 'masjidos' ), attributes.show_date, function ( value ) { setAttributes( { show_date: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Type: ', 'masjidos' ), value: attributes.type || 'all' },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/events',
		title: __( 'MasjidOS Events', 'masjidos' ),
		description: __( 'List upcoming mosque events and community gatherings.', 'masjidos' ),
		icon: 'tickets-alt',
		attributes: {
			title: { type: 'string', default: __( 'Upcoming Events', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			limit: { type: 'string', default: '5' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/duas-azkar',
		title: __( 'MasjidOS Duas & Azkar', 'masjidos' ),
		description: __( 'Display curated duas and azkar with optional counter and share buttons.', 'masjidos' ),
		icon: 'book-alt',
		attributes: {
			title: { type: 'string', default: __( 'Duas & Azkar', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			category: { type: 'string', default: 'all' },
			limit: { type: 'string', default: '4' },
			design: { type: 'string', default: 'cards' },
			source: { type: 'string', default: 'yes' },
			counter: { type: 'string', default: 'yes' },
			share: { type: 'string', default: 'yes' },
			audio: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Cards', 'masjidos' ), value: 'cards' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'category',
					label: __( 'Category', 'masjidos' ),
					value: attributes.category,
					options: [
						{ label: __( 'All', 'masjidos' ), value: 'all' },
						{ label: __( 'Daily', 'masjidos' ), value: 'daily' },
						{ label: __( 'Morning', 'masjidos' ), value: 'morning' },
						{ label: __( 'Evening', 'masjidos' ), value: 'evening' },
						{ label: __( 'Food', 'masjidos' ), value: 'food' },
						{ label: __( 'Sleep', 'masjidos' ), value: 'sleep' },
						{ label: __( 'Home', 'masjidos' ), value: 'home' },
						{ label: __( 'Masjid', 'masjidos' ), value: 'masjid' },
						{ label: __( 'Travel', 'masjidos' ), value: 'travel' },
						{ label: __( 'Rain', 'masjidos' ), value: 'rain' },
						{ label: __( 'Forgiveness', 'masjidos' ), value: 'forgiveness' },
						{ label: __( 'Quranic', 'masjidos' ), value: 'quran' },
						{ label: __( 'Protection', 'masjidos' ), value: 'protection' }
					],
					onChange: function ( value ) { setAttributes( { category: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} ),
				yesNoToggle( __( 'Show Source', 'masjidos' ), attributes.source, function ( value ) { setAttributes( { source: value } ); } ),
				yesNoToggle( __( 'Show Counter', 'masjidos' ), attributes.counter, function ( value ) { setAttributes( { counter: value } ); } ),
				yesNoToggle( __( 'Show Share', 'masjidos' ), attributes.share, function ( value ) { setAttributes( { share: value } ); } ),
				yesNoToggle( __( 'Show Audio', 'masjidos' ), attributes.audio, function ( value ) { setAttributes( { audio: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Category: ', 'masjidos' ), value: attributes.category }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/khutbah-archive',
		title: __( 'MasjidOS Khutbah Archive', 'masjidos' ),
		description: __( 'Browse published Jumuah khutbah records with search and filters.', 'masjidos' ),
		icon: 'portfolio',
		attributes: {
			title: { type: 'string', default: __( 'Jumuah Khutbah Archive', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			limit: { type: 'string', default: '12' },
			category: { type: 'string', default: '' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} ),
				createElement( TextControl, {
					key: 'category',
					label: __( 'Category', 'masjidos' ),
					value: attributes.category,
					onChange: function ( value ) { setAttributes( { category: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit },
				{ label: __( 'Category: ', 'masjidos' ), value: attributes.category || '(' + __( 'All', 'masjidos' ) + ')' }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/khatib-this-week',
		title: __( 'MasjidOS This Week\'s Khatib', 'masjidos' ),
		description: __( 'Show this Friday\'s scheduled khatib and topic.', 'masjidos' ),
		icon: 'groups',
		attributes: {
			title: { type: 'string', default: __( 'This Week\'s Khatib', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/upcoming-khutbah',
		title: __( 'MasjidOS Upcoming Khutbahs', 'masjidos' ),
		description: __( 'List upcoming scheduled or planned Friday topics.', 'masjidos' ),
		icon: 'calendar-alt',
		attributes: {
			title: { type: 'string', default: __( 'Upcoming Khutbahs', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			limit: { type: 'string', default: '5' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/khutbah-search',
		title: __( 'MasjidOS Khutbah Search', 'masjidos' ),
		description: __( 'Compact public search for the Minbar archive.', 'masjidos' ),
		icon: 'search',
		attributes: {
			title: { type: 'string', default: __( 'Search Khutbah Archive', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			limit: { type: 'string', default: '6' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/quran-verse',
		title: __( 'MasjidOS Quran Verse', 'masjidos' ),
		description: __( 'Show a rotating Quran verse of the day with translation.', 'masjidos' ),
		icon: 'book',
		attributes: {
			title: { type: 'string', default: __( 'Quran Verse of the Day', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'classic' },
			share: { type: 'string', default: 'yes' },
			tafsir: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				yesNoToggle( __( 'Show Tafsir Link', 'masjidos' ), attributes.tafsir, function ( value ) { setAttributes( { tafsir: value } ); } ),
				yesNoToggle( __( 'Show Share', 'masjidos' ), attributes.share, function ( value ) { setAttributes( { share: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/hadith',
		title: __( 'MasjidOS Hadith', 'masjidos' ),
		description: __( 'Show a rotating Hadith of the day with translation.', 'masjidos' ),
		icon: 'editor-quote',
		attributes: {
			title: { type: 'string', default: __( 'Hadith of the Day', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'classic' },
			share: { type: 'string', default: 'yes' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				yesNoToggle( __( 'Show Share', 'masjidos' ), attributes.share, function ( value ) { setAttributes( { share: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/allah-names',
		title: __( 'MasjidOS 99 Names', 'masjidos' ),
		description: __( 'Display the 99 Names of Allah with meanings.', 'masjidos' ),
		icon: 'star-filled',
		attributes: {
			title: { type: 'string', default: __( '99 Names of Allah', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'grid' },
			limit: { type: 'string', default: '99' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Grid', 'masjidos' ), value: 'grid' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/audio-quran',
		title: __( 'MasjidOS Audio Quran', 'masjidos' ),
		description: __( 'Embed an audio Quran player for selected Surahs.', 'masjidos' ),
		icon: 'controls-volumeon',
		attributes: {
			title: { type: 'string', default: __( 'Audio Quran Player', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'classic' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/articles',
		title: __( 'MasjidOS Articles', 'masjidos' ),
		description: __( 'List published Islamic articles from the MasjidOS Articles CPT.', 'masjidos' ),
		icon: 'media-document',
		attributes: {
			title: { type: 'string', default: __( 'Islamic Articles', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			category: { type: 'string', default: '' },
			limit: { type: 'string', default: '6' },
			excerpt: { type: 'string', default: 'yes' },
			design: { type: 'string', default: 'grid' }
		},
		inspector: function ( attributes, setAttributes ) {
			return [
				createElement( TextControl, {
					key: 'title',
					label: __( 'Title', 'masjidos' ),
					value: attributes.title,
					onChange: function ( value ) { setAttributes( { title: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'language',
					label: __( 'Language', 'masjidos' ),
					value: attributes.language,
					options: languageOptions,
					onChange: function ( value ) { setAttributes( { language: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'design',
					label: __( 'Design', 'masjidos' ),
					value: attributes.design,
					options: [
						{ label: __( 'Grid', 'masjidos' ), value: 'grid' },
						{ label: __( 'List', 'masjidos' ), value: 'list' }
					],
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( TextControl, {
					key: 'category',
					label: __( 'Category slug', 'masjidos' ),
					help: __( 'Optional. Example: fiqh, aqeedah, seerah', 'masjidos' ),
					value: attributes.category,
					onChange: function ( value ) { setAttributes( { category: value } ); }
				} ),
				createElement( TextControl, {
					key: 'limit',
					label: __( 'Limit', 'masjidos' ),
					type: 'number',
					value: attributes.limit,
					onChange: function ( value ) { setAttributes( { limit: value } ); }
				} ),
				yesNoToggle( __( 'Show Excerpt', 'masjidos' ), attributes.excerpt, function ( value ) { setAttributes( { excerpt: value } ); } )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Design: ', 'masjidos' ), value: attributes.design },
				{ label: __( 'Category: ', 'masjidos' ), value: attributes.category || '(' + __( 'All', 'masjidos' ) + ')' },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor || window.wp.editor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n
);
