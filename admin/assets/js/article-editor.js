/**
 * MasjidOS Islamic Article editor sidebar (block editor).
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.plugins || ! wp.editPost || ! wp.element || ! wp.components || ! wp.data || ! wp.i18n ) {
		return;
	}

	var registerPlugin = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var __ = wp.i18n.__;

	var META = {
		language: 'itmms_article_language',
		author: 'itmms_article_author',
		translator: 'itmms_article_translator',
		source: 'itmms_article_source',
		takeaway: 'itmms_article_takeaway',
		external: 'itmms_article_external_url',
		audio: 'itmms_article_audio_url'
	};

	function ArticleDetailsPanel() {
		var meta = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			return editor && editor.getEditedPostAttribute
				? ( editor.getEditedPostAttribute( 'meta' ) || {} )
				: {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		function setMeta( key, value ) {
			var next = {};
			next[ key ] = value;
			editPost( { meta: Object.assign( {}, meta, next ) } );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'itmms-article-details',
				title: __( 'Article Details', 'masjidos' ),
				className: 'itmms-article-editor-panel'
			},
			el(
				Fragment,
				null,
				el( SelectControl, {
					label: __( 'Article Language', 'masjidos' ),
					value: meta[ META.language ] || 'en',
					options: [
						{ label: __( 'English', 'masjidos' ), value: 'en' },
						{ label: __( 'Bangla', 'masjidos' ), value: 'bn' },
						{ label: __( 'Arabic', 'masjidos' ), value: 'ar' }
					],
					onChange: function ( value ) {
						setMeta( META.language, value );
					},
					help: __( 'Language of this article’s content. Does not auto-translate the title.', 'masjidos' )
				} ),
				el( TextControl, {
					label: __( 'Author / Scholar', 'masjidos' ),
					value: meta[ META.author ] || '',
					onChange: function ( value ) {
						setMeta( META.author, value );
					},
					placeholder: __( 'e.g. Imam / Scholar name', 'masjidos' )
				} ),
				el( TextControl, {
					label: __( 'Translator', 'masjidos' ),
					value: meta[ META.translator ] || '',
					onChange: function ( value ) {
						setMeta( META.translator, value );
					},
					placeholder: __( 'Optional translator name', 'masjidos' )
				} ),
				el( TextControl, {
					label: __( 'Source / Reference', 'masjidos' ),
					value: meta[ META.source ] || '',
					onChange: function ( value ) {
						setMeta( META.source, value );
					},
					placeholder: __( 'e.g. Book, hadith collection, citation', 'masjidos' )
				} ),
				el( TextareaControl, {
					label: __( 'Key Takeaway', 'masjidos' ),
					value: meta[ META.takeaway ] || '',
					onChange: function ( value ) {
						setMeta( META.takeaway, value );
					},
					help: __( 'One-line summary shown above the article.', 'masjidos' ),
					rows: 2
				} ),
				el( TextControl, {
					label: __( 'Original / External URL', 'masjidos' ),
					value: meta[ META.external ] || '',
					onChange: function ( value ) {
						setMeta( META.external, value );
					},
					type: 'url',
					placeholder: 'https://'
				} ),
				el( TextControl, {
					label: __( 'Audio URL', 'masjidos' ),
					value: meta[ META.audio ] || '',
					onChange: function ( value ) {
						setMeta( META.audio, value );
					},
					type: 'url',
					placeholder: __( 'Optional audio narration URL', 'masjidos' )
				} )
			)
		);
	}

	registerPlugin( 'itmms-article-details', {
		render: ArticleDetailsPanel,
		icon: 'book-alt'
	} );
}( window.wp ) );
