(function() {
	tinymce.PluginManager.add('tie_arqam_mce_button', function( editor, url ) {
		editor.addButton( 'tie_arqam_mce_button', {
            icon: ' tie-arqam-shortcodes-icon ',
			tooltip: tiearqam_js.shortcodes_tooltip,
			type: 'button',
			minWidth: 250,
			
			onclick: function() {
				var tiearqamWin = editor.windowManager.open({
					title: 'Arqam',

					body: [
						{
							type: 'listbox',
							name: 'BoxStyle',
							label: tiearqam_js.style,
							'values': [
								{text: tiearqam_js.metro, 		value: 'metro'},
								{text: tiearqam_js.gray, 		value: 'gray'},
								{text: tiearqam_js.colored, 	value: 'colored'},
								{text: tiearqam_js.bordered,	value: 'colored_border'},
								{text: tiearqam_js.flat, 		value: 'flat'}
							]
						},
						{
							type: 'listbox',
							name: 'BoxColumns',
							label: tiearqam_js.columns,
							'values': [
								{text: tiearqam_js.column1, 	value: '1'},
								{text: tiearqam_js.column2, 	value: '2'},
								{text: tiearqam_js.column3, 	value: '3'}
							]
						},
						{
							type: 'checkbox',
							name: 'DarkSkin',
							label: tiearqam_js.dark,
							value: 'true',
						},
						{
							type: 'textbox',
							name: 'Width',
							minWidth: 250,
							label: tiearqam_js.width,
						},
						{
							type: 'textbox',
							name: 'ItemsWidth',
							minWidth: 250,
							label: tiearqam_js.items_width,
						},
						
					],
					onsubmit: function( e ) {
						var output ;
						output = '[arqam';

						if( e.data.BoxStyle )		output += ' style= ' + e.data.BoxStyle; 
						if( e.data.BoxColumns )		output += ' columns= ' + e.data.BoxColumns; 
						if( e.data.DarkSkin )		output += ' dark=1'; 
						if( e.data.Width )			output += ' width= ' + e.data.Width; 
						if( e.data.ItemsWidth )		output += ' item= ' + e.data.ItemsWidth; 

						output += ' ]'; 
						editor.insertContent( output );

					}

				});
			}
		});
	});
})();