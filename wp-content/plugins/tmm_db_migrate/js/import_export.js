var TMM_DB_MIGRATE = function() {

	var self = {
		tables: [],
		attachments_count: 0,

		init: function () {

			jQuery('#button_prepare_import_data').click(function () {

				if (jQuery(this).attr('data-active') != 'true') {
					if(confirm(tmm_migrate_l10n.import_caution)){
						jQuery(this).attr('data-active', true);
						self.import( jQuery(this) );
					}
				}

				return false;
			});

			jQuery('#button_prepare_export_data').click(function() {
				self.export();
				return false;
			});
		},

		import: function ($this) {

			var process_div = jQuery('#tmm_db_migrate_process_imp'),
				process_html = '<li><div id="squaresWaveG"><div id="squaresWaveG_1" class="squaresWaveG"></div><div id="squaresWaveG_2" class="squaresWaveG"></div><div id="squaresWaveG_3" class="squaresWaveG"></div><div id="squaresWaveG_4" class="squaresWaveG"></div><div id="squaresWaveG_5" class="squaresWaveG"></div><div id="squaresWaveG_6" class="squaresWaveG"></div><div id="squaresWaveG_7" class="squaresWaveG"></div><div id="squaresWaveG_8" class="squaresWaveG"></div></div></li>';

			process_html += '<div class="import-status">' + tmm_migrate_l10n.import_started + '</div>';
			process_div.append(process_html);

			var do_backup = jQuery('#tmm_migrate_backup').length ? jQuery('#tmm_migrate_backup').val() : 1,
				upload_attachments = jQuery('#tmm_migrate_upload_attachments').length ? jQuery('#tmm_migrate_upload_attachments').val() : 1;

			var data = {
				action: "tmm_migrate_import_content",
				backup: do_backup,
				upload_attachments: upload_attachments
			};

			jQuery.post(ajaxurl, data, function (response) {
				response = jQuery.parseJSON(response);

				process_div.find('.import-status').text(tmm_migrate_l10n.import_finished);

				if (upload_attachments != 0 && response.attachments && response.attachments.length > 0) {
					var i;
					attachments_count = response.attachments.length;

					for (i in response.attachments) {
						self.process_attachment(response.attachments[i]);
					}
				} else {
					alert(tmm_migrate_l10n.import_finished);
					location.reload();
				}
			}).always(function() {
				if (!upload_attachments) {
					process_div.empty();
					$this.attr('data-active', false);
				}
			});

		},

		process_attachment: function(attachment) {
			var data = {
				action: "tmm_migrate_import_attachment",
				attachment: attachment
			};

			jQuery.post(ajaxurl, data, function (response) {
				var msg = response ? response : '';
				jQuery('#tmm_db_migrate_process_imp').find('.import-status').text( msg);
			}).always(function() {
				attachments_count--;
				if (attachments_count <= 0) {
					alert(tmm_migrate_l10n.import_finished);
					location.reload();
				}
			});
		},

		export: function () {
			jQuery('#tmm_db_migrate_process').empty();
			var data = {
				action: "tmm_prepare_export_data"
			};
			jQuery.post(ajaxurl, data, function(tables) {
				self.tables = jQuery.parseJSON(tables);
				self.add_process_txt(tmm_migrate_l10n.prepare_finished + ' ' + self.tables.length);
				self.process_table(self.tables[0], 0);
			});
		},

		process_table: function(table, index) {
			if (index < self.tables.length) {
				self.add_process_txt(tmm_migrate_l10n.process_table + ' ' + table + ' ...');
				var data = {
					action: "tmm_process_export_data",
					table: table
				};
				jQuery.post(ajaxurl, data, function(row_count) {
					jQuery('#tmm_db_migrate_process').find('li:last-child').append('(' + (row_count ? row_count : 0) + ' rows processed)');
					self.process_table(self.tables[index + 1], index + 1);
				});
			} else {
				self.add_process_txt(tmm_migrate_l10n.process_finished);
				self.zip_tables();
			}
		},

		zip_tables: function() {
			var data = {
				action: "tmm_zip_export_data"
			};
			jQuery.post(ajaxurl, data, function(zip_link) {
				self.add_process_txt('<a href="' + zip_link + '">' + tmm_migrate_l10n.download_zip + '</a>');
			});
		},

		add_process_txt: function (txt) {
			jQuery('#tmm_db_migrate_process').append('<li>').find('li:last-child').html(txt);
		}

	};
	return self;
};

jQuery(document).ready(function () {
	var tmm_db_import = new TMM_DB_MIGRATE();
	tmm_db_import.init();
});


