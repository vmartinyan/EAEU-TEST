<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>

<h2 class="section-title"><?php _e('Demo Data Installation', TMM_MIGRATE_TEXTDOMAIN); ?></h2>

<div class="option">

	<div class="controls alternative">

		<a href="#" class="button button-primary button-large" id="button_prepare_import_data"><?php _e('Demo Data Install', TMM_MIGRATE_TEXTDOMAIN); ?></a>

	</div>

	<div class="explain alternative">

		<?php
		$count_posts = wp_count_posts();
		$count_pages = wp_count_posts('page');
		$published_posts = $count_posts->publish;
		$published_pages = $count_pages->publish;

		if ($published_posts > 3  || $published_pages > 3) { ?>

			<h3 class="red"><?php _e('Important Notice:', TMM_MIGRATE_TEXTDOMAIN); ?></h3>
			<p class="red big"><?php _e('We just defined there are some posts/pages already there on your website, therefore it is not a clean WordPress Installation!', TMM_MIGRATE_TEXTDOMAIN); ?></p>
			<p class="big"><?php _e('Please note, that your current database(all your content) will be overwritten after clicking "Demo Data Install" button and there is no way to revert it back, so we would kindly ask you making a database backup before installing demo content.', TMM_MIGRATE_TEXTDOMAIN); ?></p>

		<?php } else { ?>

			<h3 class="green"><?php _e('Everything is fine.', TMM_MIGRATE_TEXTDOMAIN); ?><br/><?php _e('You are ready to go...', TMM_MIGRATE_TEXTDOMAIN); ?></h3>

		<?php } ?>

	</div>

</div>



<?php
TMM_OptionsHelper::draw_theme_option(array(
	'title' => __('Import Attachments', TMM_MIGRATE_TEXTDOMAIN),
	'type' => 'checkbox',
	'name' => 'tmm_migrate_upload_attachments',
	'default_value' => 1,
	'value' => 1,
	'css_class' => '',
	'description' => __('Download and import file attachments (images, videos, audios)', TMM_MIGRATE_TEXTDOMAIN)
));
?>

<?php
TMM_OptionsHelper::draw_theme_option(array(
	'title' => __('Backup DB', TMM_MIGRATE_TEXTDOMAIN),
	'type' => 'checkbox',
	'name' => 'tmm_migrate_backup',
	'default_value' => 1,
	'value' => 1,
	'css_class' => '',
	'description' => __('Backup your database content before importing. Placed in ', TMM_MIGRATE_TEXTDOMAIN) . "'/uploads/tmm_backup/'"
));
?>

<ul id="tmm_db_migrate_process_imp"></ul>

<hr>
<br><br>

<h2 class="section-title"><?php _e('Export Data', TMM_MIGRATE_TEXTDOMAIN); ?></h2>

<div class="option">

	<div class="controls alternative">

		<a href="#" class="button button-primary button-large" id="button_prepare_export_data"><?php _e('Export Data', TMM_MIGRATE_TEXTDOMAIN); ?></a>

		<ul id="tmm_db_migrate_process"></ul>

	</div>

	<div class="explain alternative">

		<p><?php _e('In Case you need to transfer your website to another domain the easiest way to export all the data is here.', TMM_MIGRATE_TEXTDOMAIN); ?></p>
		<p><?php _e('Video guide on how to do that properly is coming soon...', TMM_MIGRATE_TEXTDOMAIN); ?></p>

	</div>

</div>