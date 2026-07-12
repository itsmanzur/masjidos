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

	registerBlockType( 'masjidos/prayer-times', {
		title: __( 'MasjidOS Prayer Times', 'masjidos' ),
		description: __( 'Display daily prayer times, countdown, Qibla compass, and timezone details.', 'masjidos' ),
		icon: 'clock',
		category: 'widgets',
		keywords: [ 'prayer', 'mosque', 'masjid', 'namaz' ],
		attributes: {
			title: {
				type: 'string',
				default: __( 'Prayer Times', 'masjidos' )
			},
			design: {
				type: 'string',
				default: 'classic'
			},
			language: {
				type: 'string',
				default: 'en'
			},
			qibla: {
				type: 'string',
				default: 'yes'
			},
			meta: {
				type: 'string',
				default: 'yes'
			},
			iqamah: {
				type: 'string',
				default: 'yes'
			}
		},

		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var handleTitleChange = function ( value ) {
				setAttributes( { title: value } );
			};

			var handleDesignChange = function ( value ) {
				setAttributes( { design: value } );
			};

			var handleLanguageChange = function ( value ) {
				setAttributes( { language: value } );
			};

			var handleQiblaChange = function ( value ) {
				setAttributes( { qibla: value ? 'yes' : 'no' } );
			};

			var handleMetaChange = function ( value ) {
				setAttributes( { meta: value ? 'yes' : 'no' } );
			};

			var handleIqamahChange = function ( value ) {
				setAttributes( { iqamah: value ? 'yes' : 'no' } );
			};

			// Editor UI Preview
			return [
				createElement( InspectorControls, { key: 'inspector' },
					createElement( PanelBody, { title: __( 'Widget Settings', 'masjidos' ), initialOpen: true },
						createElement( TextControl, {
							label: __( 'Title', 'masjidos' ),
							value: attributes.title,
							onChange: handleTitleChange
						} ),
						createElement( SelectControl, {
							label: __( 'Design', 'masjidos' ),
							value: attributes.design,
							options: [
								{ label: __( 'Classic', 'masjidos' ), value: 'classic' },
								{ label: __( 'Compact', 'masjidos' ), value: 'compact' }
							],
							onChange: handleDesignChange
						} ),
						createElement( SelectControl, {
							label: __( 'Language', 'masjidos' ),
							value: attributes.language,
							options: [
								{ label: __( 'English', 'masjidos' ), value: 'en' },
								{ label: __( 'Bangla', 'masjidos' ), value: 'bn' },
								{ label: __( 'Arabic', 'masjidos' ), value: 'ar' }
							],
							onChange: handleLanguageChange
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Qibla', 'masjidos' ),
							checked: attributes.qibla === 'yes',
							onChange: handleQiblaChange
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Calculations Meta', 'masjidos' ),
							checked: attributes.meta === 'yes',
							onChange: handleMetaChange
						} ),
						createElement( ToggleControl, {
							label: __( 'Show Iqamah Times', 'masjidos' ),
							checked: attributes.iqamah === 'yes',
							onChange: handleIqamahChange
						} )
					)
				),
				createElement( 'div', {
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
						createElement( 'span', { className: 'dashicons dashicons-clock' } ),
						createElement( 'h3', { style: { margin: 0, color: '#1a6b5a' } }, __( 'MasjidOS Prayer Times', 'masjidos' ) )
					),
					createElement( 'div', { style: { fontSize: '13px', color: '#333' } },
						createElement( 'p', { style: { margin: '4px 0' } }, createElement( 'strong', null, __( 'Title: ', 'masjidos' ) ), attributes.title || '(' + __( 'None', 'masjidos' ) + ')' ),
						createElement( 'p', { style: { margin: '4px 0' } }, createElement( 'strong', null, __( 'Design: ', 'masjidos' ) ), attributes.design ),
						createElement( 'p', { style: { margin: '4px 0' } }, createElement( 'strong', null, __( 'Language: ', 'masjidos' ) ), attributes.language ),
						createElement( 'p', { style: { margin: '10px 0 0 0', fontStyle: 'italic', color: '#666', fontSize: '11px' } }, __( '[Renders live widget on frontend]', 'masjidos' ) )
					)
				)
			];
		},

		save: function () {
			// Renders dynamically on backend
			return null;
		}
	} );

	registerBlockType( 'masjidos/islamic-calendar', {
		title: __( 'MasjidOS Islamic Calendar', 'masjidos' ),
		description: __( 'Display a dual Gregorian + Hijri calendar with Islamic holy days and events.', 'masjidos' ),
		icon: 'calendar-alt',
		category: 'widgets',
		keywords: [ 'calendar', 'hijri', 'islamic', 'events' ],
		attributes: {
			title: {
				type: 'string',
				default: __( 'Islamic Calendar', 'masjidos' )
			},
			language: {
				type: 'string',
				default: 'en'
			}
		},

		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var handleTitleChange = function ( value ) {
				setAttributes( { title: value } );
			};

			var handleLanguageChange = function ( value ) {
				setAttributes( { language: value } );
			};

			return [
				createElement( InspectorControls, { key: 'inspector' },
					createElement( PanelBody, { title: __( 'Calendar Settings', 'masjidos' ), initialOpen: true },
						createElement( TextControl, {
							label: __( 'Title', 'masjidos' ),
							value: attributes.title,
							onChange: handleTitleChange
						} ),
						createElement( SelectControl, {
							label: __( 'Language', 'masjidos' ),
							value: attributes.language,
							options: [
								{ label: __( 'English', 'masjidos' ), value: 'en' },
								{ label: __( 'Bangla', 'masjidos' ), value: 'bn' },
								{ label: __( 'Arabic', 'masjidos' ), value: 'ar' }
							],
							onChange: handleLanguageChange
						} )
					)
				),
				createElement( 'div', {
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
						createElement( 'span', { className: 'dashicons dashicons-calendar-alt' } ),
						createElement( 'h3', { style: { margin: 0, color: '#1a6b5a' } }, __( 'MasjidOS Islamic Calendar', 'masjidos' ) )
					),
					createElement( 'div', { style: { fontSize: '13px', color: '#333' } },
						createElement( 'p', { style: { margin: '4px 0' } }, createElement( 'strong', null, __( 'Title: ', 'masjidos' ) ), attributes.title || '(' + __( 'None', 'masjidos' ) + ')' ),
						createElement( 'p', { style: { margin: '4px 0' } }, createElement( 'strong', null, __( 'Language: ', 'masjidos' ) ), attributes.language ),
						createElement( 'p', { style: { margin: '10px 0 0 0', fontStyle: 'italic', color: '#666', fontSize: '11px' } }, __( '[Renders live dual calendar on frontend]', 'masjidos' ) )
					)
				)
			];
		},

		save: function () {
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor || window.wp.editor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n
);
