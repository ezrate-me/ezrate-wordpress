/**
 * «EZRate price widget» block: pick a saved widget; the editor shows the same server-rendered HTML as the site.
 * Plain script (no build step) using the editor globals.
 */
( function ( blocks, element, components, blockEditor, ServerSideRender, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;
	var widgets = window.ezrpwWidgets || [];

	blocks.registerBlockType( 'ezrate-price-widgets/widget', {
		edit: function ( props ) {
			var id = props.attributes.widgetId || ( widgets[ 0 ] ? widgets[ 0 ].value : 0 );
			var options = widgets.length ? widgets : [ { value: 0, label: __( 'No widgets yet', 'ezrate-price-widgets' ) } ];
			return el(
				'div',
				blockEditor.useBlockProps(),
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Widget', 'ezrate-price-widgets' ), initialOpen: true },
						el( components.SelectControl, {
							label: __( 'Show this widget', 'ezrate-price-widgets' ),
							value: id,
							options: options,
							onChange: function ( v ) {
								props.setAttributes( { widgetId: parseInt( v, 10 ) || 0 } );
							},
						} ),
						el(
							'p',
							null,
							el( 'a', { href: window.ezrpwAdminUrl, target: '_blank', rel: 'noopener' }, __( 'Create or edit widgets', 'ezrate-price-widgets' ) )
						)
					)
				),
				el( ServerSideRender, { block: 'ezrate-price-widgets/widget', attributes: { widgetId: id } } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n );
