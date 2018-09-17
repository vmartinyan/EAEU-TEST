<?php
$page_id = 0;

if (is_object($post)) {
	$page_id = $post->ID;
}

if (is_home()) {
	$page_id = get_option('page_for_posts');
}

$header_type = TMM::get_option('header_type');

if ($page_id) {
	$page_header_type = get_post_meta($page_id, 'header_type', 1);

	if (!empty($page_header_type)) {
		$header_type = $page_header_type;
	}
}

if (tmm_is_blog_archive()) {
	$page_header_type = TMM::get_option('header_type');

	if (!empty($page_header_type)) {
		$header_type = $page_header_type;
	}
}

if (is_search()) {
	$page_header_type = TMM::get_option('header_type');

	if (!empty($page_header_type)) {
		$header_type = $page_header_type;
	}
}

$header_type_class = '';

if ($header_type === 'alternate') {
	$header_type_class = ' type-4';
} else if ($header_type === 'blue') {
	$header_type_class = ' type-3';
} else if ($header_type === 'dark') {
	$header_type_class = ' type-2';
} else {
	$header_type_class = ' type-1';
}
?>

<header id="header" class="header<?php echo esc_attr($header_type_class); ?>">

	<div class="header-top">

		<div class="row">

			<div class="large-12 columns">

				<?php get_template_part('header', 'socials'); ?>

			</div>

		</div><!--/ .row-->

	</div><!--/ .header-top-->

	<div class="header-middle">

		<div class="row">

			<div class="large-12 columns">
				<div class="header-middle-entry">
                    <div class="headerLogo large-3 columns">
					   <?php get_template_part('header', 'logo'); ?>
                    </div>
                    <!-- for Adding the Site name-->
                    <div class="headerNameSection large-9 columns">
                        <h1 class="headerName">
                            <?php if(ICL_LANGUAGE_CODE=='en'): ?>
                            Regulatory Framework to Promote Energy Efficiency in Countries of the Eurasian Economic Union
                            <?php elseif(ICL_LANGUAGE_CODE=='ru'): ?>
                            Стандарты и нормативное регулирование для продвижения энергоэффективности в странах Евразийского экономического союза
                            <?php endif;?>
                        </h1>
                    </div>


  
                    
                    
                    
                    
<!---
					<div class="account">

						<?php if (TMM::get_option('show_login_buttons') !== '0') { ?>

						<ul>
							<?php if (is_user_logged_in()) { ?>
								<li class="lock"><a href="<?php echo wp_logout_url(get_home_url()); ?>"><?php esc_html_e('Log out', 'diplomat'); ?></a></li>
							<?php } else { ?>
								<li data-login="loginDialog" class="lock"><a href="#"><?php esc_html_e('Log in', 'diplomat'); ?></a></li>
								<li data-account="accountDialog" class="user"><a href="#"><?php esc_html_e('Create Account', 'diplomat'); ?></a></li>
							<?php } ?>
						</ul>

						<?php } ?>-->

						<!-- - - - - - - - - - - - - Donate Button - - - - - - - - - - - - - - -->

						<!--	<?php get_template_part('header', 'donate'); ?> -->

						<!-- - - - - - - - - - - - - end Donate Button - - - - - - - - - - - - -->

			<!--		</div> -->
                    

				</div>
			</div>

		</div><!--/ .row-->

	</div><!--/ .header-middle-->

	<div class="header-bottom">

		<div class="row">

			<div class="large-12 columns">

				<nav id="navigation" class="navigation top-bar" data-topbar role="navigation">

						<?php get_template_part('header', 'nav'); ?>
						<?php
							$detect = new Mobile_Detect();
							// Check for any mobile device.
							if (!$detect->isMobile()) {
								get_template_part('header', 'search');
							}
						?>
						<?php get_template_part('header', 'donate'); ?>

				</nav><!--/ .navigation-->


			</div>

		</div><!--/ .row-->

	</div><!--/ .header-bottom-->

</header><!--/ #header-->
