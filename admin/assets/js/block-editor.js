( function ( blocks, blockEditor, components, element, i18n, serverSideRender ) {
	'use strict';

	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var BlockControls = blockEditor.BlockControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var ToolbarGroup = components.ToolbarGroup;
	var ToolbarButton = components.ToolbarButton;
	var Disabled = components.Disabled;
	var ServerSideRender = serverSideRender || wp.serverSideRender;
	var createElement = element.createElement;
	var useState = element.useState;
	var __ = i18n.__;

	var blockData = window.itmmsBlockData || {};
	var defaultLanguage = blockData.defaultLanguage || 'en';
	var moduleStatus = blockData.moduleStatus || {};
	var adminUrls = blockData.adminUrls || {};

	var languageOptions = [
		{ label: __( 'English', 'masjidos' ), value: 'en' },
		{ label: __( 'Bangla (বাংলা)', 'masjidos' ), value: 'bn' },
		{ label: __( 'Arabic (العربية)', 'masjidos' ), value: 'ar' }
	];

	var monthOptions = [
		{ label: __( 'Current Month (Auto)', 'masjidos' ), value: '' },
		{ label: __( 'January', 'masjidos' ), value: '1' },
		{ label: __( 'February', 'masjidos' ), value: '2' },
		{ label: __( 'March', 'masjidos' ), value: '3' },
		{ label: __( 'April', 'masjidos' ), value: '4' },
		{ label: __( 'May', 'masjidos' ), value: '5' },
		{ label: __( 'June', 'masjidos' ), value: '6' },
		{ label: __( 'July', 'masjidos' ), value: '7' },
		{ label: __( 'August', 'masjidos' ), value: '8' },
		{ label: __( 'September', 'masjidos' ), value: '9' },
		{ label: __( 'October', 'masjidos' ), value: '10' },
		{ label: __( 'November', 'masjidos' ), value: '11' },
		{ label: __( 'December', 'masjidos' ), value: '12' }
	];

	function mapObjectToOptions( mapObj, allLabel, allValue ) {
		var opts = [ { label: allLabel || __( 'All Categories', 'masjidos' ), value: allValue !== undefined ? allValue : '' } ];
		if ( mapObj && typeof mapObj === 'object' ) {
			Object.keys( mapObj ).forEach( function ( slug ) {
				opts.push( { label: mapObj[ slug ], value: slug } );
			} );
		}
		return opts;
	}

	var customDuaCategoryOptions = mapObjectToOptions( blockData.customDuaCategories, __( 'All', 'masjidos' ), 'all' );
	var khutbahCategoryOptions = mapObjectToOptions( blockData.khutbahCategories, __( 'All Categories', 'masjidos' ), '' );
	var articleCategoryOptions = mapObjectToOptions( blockData.articleCategories, __( 'All Categories', 'masjidos' ), '' );
	var qaCategoryOptions = mapObjectToOptions( blockData.qaCategories, __( 'All Categories', 'masjidos' ), '' );

	function previewCard( icon, title, rows ) {
		return createElement( 'div', {
			key: 'preview-card',
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
				createElement( 'h3', { style: { margin: 0, color: '#1a6b5a', fontSize: '16px' } }, title )
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
					}, __( '[Compact summary view — click the eye icon in the block toolbar for live preview]', 'masjidos' ) )
				] )
			)
		);
	}

	function quickManageNotice( config ) {
		var isEnabled = ! config.moduleKey || moduleStatus[ config.moduleKey ] !== false;
		var manageUrl = config.manageUrl || adminUrls.features || '';
		var manageLabel = config.manageLabel || __( 'Manage in MasjidOS', 'masjidos' );
		var children = [];

		if ( ! isEnabled ) {
			children.push(
				createElement( 'div', {
					key: 'disabled-warning',
					style: {
						marginBottom: '8px',
						padding: '8px 10px',
						borderRadius: '4px',
						background: '#fff4e5',
						border: '1px solid #f5c26b',
						color: '#7a4500',
						fontSize: '12px'
					}
				},
					createElement( 'strong', null, __( 'Module Disabled: ', 'masjidos' ) ),
					__( 'This module is currently turned off in MasjidOS.', 'masjidos' ),
					adminUrls.features ? createElement( 'div', { style: { marginTop: '4px' } },
						createElement( 'a', {
							href: adminUrls.features,
							target: '_blank',
							rel: 'noopener noreferrer',
							style: { color: '#7a4500', fontWeight: '600', textDecoration: 'underline' }
						}, __( 'Enable Module →', 'masjidos' ) )
					) : null
				)
			);
		}

		if ( manageUrl ) {
			children.push(
				createElement( 'div', {
					key: 'manage-link',
					style: {
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'space-between',
						gap: '8px',
						flexWrap: 'wrap',
						fontSize: '12px',
						color: '#154c3f'
					}
				},
					createElement( 'span', null, __( 'Module Data:', 'masjidos' ) ),
					createElement( 'a', {
						href: manageUrl,
						target: '_blank',
						rel: 'noopener noreferrer',
						style: { color: '#0f6b56', fontWeight: '600', textDecoration: 'underline' }
					}, manageLabel + ' →' )
				)
			);
		}

		if ( ! children.length ) {
			return null;
		}

		return createElement( 'div', {
			key: 'quick-manage-box',
			style: {
				marginBottom: '16px',
				padding: '10px 12px',
				borderRadius: '6px',
				background: '#f0f7f5',
				border: '1px solid #b7dfd5'
			}
		}, children );
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
			category: 'masjidos',
			keywords: config.keywords || [ 'masjid', 'mosque', 'islam', 'masjidos' ],
			supports: {
				align: [ 'wide', 'full' ],
				html: false
			},
			attributes: config.attributes,
			edit: function ( props ) {
				var attributes = props.attributes;
				var setAttributes = props.setAttributes;
				var state = useState ? useState( true ) : [ true, function () {} ];
				var isLivePreview = state[ 0 ];
				var setIsLivePreview = state[ 1 ];

				var controls = config.inspector( attributes, setAttributes );
				var rows = config.previewRows( attributes );
				var manageBox = quickManageNotice( config );

				var canvasContent;
				if ( isLivePreview && ServerSideRender ) {
					canvasContent = createElement( Disabled, { key: 'live-preview' },
						createElement( ServerSideRender, {
							block: config.name,
							attributes: attributes
						} )
					);
				} else {
					canvasContent = previewCard( config.icon, config.title, rows );
				}

				return [
					BlockControls && ToolbarGroup && ToolbarButton ? createElement( BlockControls, { key: 'toolbar' },
						createElement( ToolbarGroup, null,
							createElement( ToolbarButton, {
								icon: isLivePreview ? 'visibility' : 'exerpt-view',
								label: isLivePreview ? __( 'Switch to Compact Card View', 'masjidos' ) : __( 'Switch to Live Preview', 'masjidos' ),
								isPressed: isLivePreview,
								onClick: function () {
									setIsLivePreview( ! isLivePreview );
								}
							} )
						)
					) : null,
					createElement( InspectorControls, { key: 'inspector' },
						createElement( PanelBody, { title: config.panelTitle || __( 'Widget Settings', 'masjidos' ), initialOpen: true },
							[ manageBox ].concat( controls )
						)
					),
					canvasContent
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
		keywords: [ 'prayer', 'mosque', 'masjid', 'namaz', 'iqamah' ],
		moduleKey: 'prayer_times',
		manageUrl: adminUrls.prayer_times,
		manageLabel: __( 'Configure Prayer Times', 'masjidos' ),
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
						{ label: __( 'Compact', 'masjidos' ), value: 'compact' },
						{ label: __( 'Minimal', 'masjidos' ), value: 'minimal' }
					].concat( blockData.proActive ? [
						{ label: __( 'Pro: Premium Card', 'masjidos' ), value: 'premium-card' },
						{ label: __( 'Pro: Mosque Display', 'masjidos' ), value: 'mosque-display' },
						{ label: __( 'Pro: Ramadan Special', 'masjidos' ), value: 'ramadan-special' }
					] : [] ),
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
		manageUrl: adminUrls.prayer_times,
		manageLabel: __( 'Hijri Adjustment & Settings', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Islamic Calendar', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			navigation: { type: 'string', default: 'yes' },
			month: { type: 'string', default: '' },
			year: { type: 'string', default: '' }
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
				yesNoToggle( __( 'Show Month/Year Navigation', 'masjidos' ), attributes.navigation || 'yes', function ( value ) { setAttributes( { navigation: value } ); } ),
				createElement( SelectControl, {
					key: 'month',
					label: __( 'Initial Month', 'masjidos' ),
					value: attributes.month || '',
					options: monthOptions,
					onChange: function ( value ) { setAttributes( { month: value } ); }
				} ),
				createElement( TextControl, {
					key: 'year',
					label: __( 'Initial Year (Optional)', 'masjidos' ),
					placeholder: __( 'Current Year (Auto)', 'masjidos' ),
					value: attributes.year || '',
					onChange: function ( value ) { setAttributes( { year: value } ); }
				} )
			];
		},
		previewRows: function ( attributes ) {
			return [
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title || '(' + __( 'None', 'masjidos' ) + ')' },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language },
				{ label: __( 'Navigation: ', 'masjidos' ), value: attributes.navigation || 'yes' }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/monthly-prayer-times',
		title: __( 'MasjidOS Monthly Timetable', 'masjidos' ),
		description: __( 'Show a full-month prayer timetable with navigation and print support.', 'masjidos' ),
		icon: 'calendar',
		moduleKey: 'prayer_times',
		manageUrl: adminUrls.prayer_times,
		manageLabel: __( 'Edit Timetable & Overrides', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Monthly Prayer Timetable', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			design: { type: 'string', default: 'table' },
			month: { type: 'string', default: '' },
			year: { type: 'string', default: '' },
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
					].concat( blockData.proActive ? [
						{ label: __( 'Pro: Premium Print', 'masjidos' ), value: 'premium-print' },
						{ label: __( 'Pro: Mosque Board', 'masjidos' ), value: 'mosque-board' },
						{ label: __( 'Pro: Ramadan Monthly', 'masjidos' ), value: 'ramadan-monthly' }
					] : [] ),
					onChange: function ( value ) { setAttributes( { design: value } ); }
				} ),
				createElement( SelectControl, {
					key: 'month',
					label: __( 'Initial Month', 'masjidos' ),
					value: attributes.month || '',
					options: monthOptions,
					onChange: function ( value ) { setAttributes( { month: value } ); }
				} ),
				createElement( TextControl, {
					key: 'year',
					label: __( 'Initial Year (Optional)', 'masjidos' ),
					placeholder: __( 'Current Year (Auto)', 'masjidos' ),
					value: attributes.year || '',
					onChange: function ( value ) { setAttributes( { year: value } ); }
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
		moduleKey: 'jumuah',
		manageUrl: adminUrls.prayer_times,
		manageLabel: __( 'Configure Jumuah Sessions', 'masjidos' ),
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
					].concat( blockData.proActive ? [
						{ label: __( 'Pro: Premium Sermon', 'masjidos' ), value: 'premium-sermon' },
						{ label: __( 'Pro: Mosque Notice', 'masjidos' ), value: 'mosque-notice' }
					] : [] ),
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
		moduleKey: 'announcements',
		manageUrl: adminUrls.announcements,
		manageLabel: __( 'Manage Notices', 'masjidos' ),
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
						{ label: __( 'Cards', 'masjidos' ), value: 'cards' },
						{ label: __( 'Ticker', 'masjidos' ), value: 'ticker' },
						{ label: __( 'Banner', 'masjidos' ), value: 'banner' },
						{ label: __( 'Popup', 'masjidos' ), value: 'popup' }
					].concat( blockData.proActive ? [
						{ label: __( 'Pro: Digital Board', 'masjidos' ), value: 'digital-board' },
						{ label: __( 'Pro: Ramadan Banner', 'masjidos' ), value: 'ramadan-banner' }
					] : [] ),
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
		moduleKey: 'events',
		manageUrl: adminUrls.events,
		manageLabel: __( 'Manage Events', 'masjidos' ),
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
		moduleKey: 'duas_azkar',
		manageUrl: adminUrls.duas,
		manageLabel: __( 'Manage Duas Library', 'masjidos' ),
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
		name: 'masjidos/custom-duas',
		title: __( 'MasjidOS Custom Duas', 'masjidos' ),
		description: __( 'Show only the duas you added in MasjidOS > Duas Library.', 'masjidos' ),
		icon: 'book-alt',
		keywords: [ 'dua', 'azkar', 'masjid', 'library' ],
		moduleKey: 'duas_azkar',
		manageUrl: adminUrls.duas,
		manageLabel: __( 'Manage Duas Library', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Duas & Azkar', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			category: { type: 'string', default: 'all' },
			limit: { type: 'string', default: '12' },
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
					key: 'category',
					label: __( 'Dua Library category', 'masjidos' ),
					value: attributes.category,
					options: customDuaCategoryOptions,
					help: __( 'Categories come from MasjidOS > Duas Library.', 'masjidos' ),
					onChange: function ( value ) { setAttributes( { category: value } ); }
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
				{ label: __( 'Category: ', 'masjidos' ), value: attributes.category },
				{ label: __( 'Limit: ', 'masjidos' ), value: attributes.limit }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/khutbah-archive',
		title: __( 'MasjidOS Khutbah Archive', 'masjidos' ),
		description: __( 'Browse published Jumuah khutbah records with search and filters.', 'masjidos' ),
		icon: 'portfolio',
		manageUrl: adminUrls.minbar,
		manageLabel: __( 'Manage Khutbah Archive', 'masjidos' ),
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
				createElement( SelectControl, {
					key: 'category',
					label: __( 'Filter by Category', 'masjidos' ),
					value: attributes.category || '',
					options: khutbahCategoryOptions,
					onChange: function ( value ) { setAttributes( { category: value } ); }
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
		manageUrl: adminUrls.minbar,
		manageLabel: __( 'Schedule Khatib in Minbar', 'masjidos' ),
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
		manageUrl: adminUrls.minbar,
		manageLabel: __( 'Manage Khutbah Schedule', 'masjidos' ),
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
		manageUrl: adminUrls.minbar,
		manageLabel: __( 'Manage Khutbah Archive', 'masjidos' ),
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
		manageUrl: adminUrls.articles,
		manageLabel: __( 'Manage Articles', 'masjidos' ),
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
				createElement( SelectControl, {
					key: 'category',
					label: __( 'Article Category', 'masjidos' ),
					value: attributes.category || '',
					options: articleCategoryOptions,
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

	registerMasjidBlock( {
		name: 'masjidos/ask-imam',
		title: __( 'MasjidOS Ask the Imam Form', 'masjidos' ),
		description: __( 'Display a public question submission form for the Imam.', 'masjidos' ),
		icon: 'format-chat',
		keywords: [ 'ask', 'imam', 'question', 'fatwa', 'qa', 'masjid' ],
		manageUrl: adminUrls.ask_imam,
		manageLabel: __( 'Moderate Questions', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Ask the Imam', 'masjidos' ) },
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
				{ label: __( 'Title: ', 'masjidos' ), value: attributes.title },
				{ label: __( 'Language: ', 'masjidos' ), value: attributes.language }
			];
		}
	} );

	registerMasjidBlock( {
		name: 'masjidos/imam-answers',
		title: __( 'MasjidOS Imam Answers (Q&A)', 'masjidos' ),
		description: __( 'Display answered public questions from the Ask the Imam library with search and category filters.', 'masjidos' ),
		icon: 'welcome-learn-more',
		keywords: [ 'ask', 'imam', 'answers', 'fatwa', 'qa', 'masjid' ],
		manageUrl: adminUrls.ask_imam,
		manageLabel: __( 'Manage Imam Answers', 'masjidos' ),
		attributes: {
			title: { type: 'string', default: __( 'Answered Questions', 'masjidos' ) },
			language: { type: 'string', default: defaultLanguage },
			category: { type: 'string', default: '' },
			limit: { type: 'string', default: '10' }
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
					key: 'category',
					label: __( 'Q&A Category', 'masjidos' ),
					value: attributes.category || '',
					options: qaCategoryOptions,
					onChange: function ( value ) { setAttributes( { category: value } ); }
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
	window.wp.i18n,
	window.wp.serverSideRender
);
