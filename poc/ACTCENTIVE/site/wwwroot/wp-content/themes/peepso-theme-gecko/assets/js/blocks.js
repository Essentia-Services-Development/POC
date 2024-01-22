( function( wp ) {
	var registerBlockType = wp.blocks.registerBlockType;
	var el = wp.element.createElement;
	var InnerBlocks = wp.editor.InnerBlocks;
	var InspectorControls = wp.editor.InspectorControls;
	var PanelColorSettings = wp.editor.PanelColorSettings;

	registerBlockType( 'gecko-blocks/container', {
		title: 'Gecko: Container',
		icon: 'universal-access-alt',
		category: 'layout',

		attributes: {
			backgroundColor: {
				type: 'string',
				default: ''
			},
			textColor: {
				type: 'string',
				default: ''
			}
		},

		edit: function( props ) {
			var attributes = props.attributes || {},
				backgroundColor = attributes.backgroundColor,
				textColor = attributes.textColor;

			function setBackgroundColor( color ) {
				props.setAttributes( { backgroundColor: color } );
			}

			function setTextColor( color ) {
				props.setAttributes( { textColor: color } );
			}

			var colorSettings = el( PanelColorSettings, {
				title: 'Color Settings',
				initialOpen: false,
				colorSettings: [
					{ label: 'Background Color', value: backgroundColor, onChange: setBackgroundColor },
					{ label: 'Text Color', value: textColor, onChange: setTextColor }
				]
			} );

			var blockStyle = {
				backgroundColor: backgroundColor,
				color: textColor
			};

			return [
				el( InspectorControls, null, colorSettings ),
				el( 'div', { className: props.className, style: blockStyle }, el( InnerBlocks ) )
			];
		},

		save: function( props ) {
			var attributes = props.attributes || {},
				backgroundColor = attributes.backgroundColor,
				textColor = attributes.textColor;

			var blockStyle = {
				backgroundColor: backgroundColor,
				color: textColor
			};

			return el( 'div', { style: blockStyle }, el( InnerBlocks.Content ) );
		}
	} );
} )( window.wp );
