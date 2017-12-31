<?php
//Register scripts and styles for admin pages
function stm_startup_styles()
{
    wp_enqueue_style('stm-startup_css', get_template_directory_uri() . '/assets/admin/css/style.css', null, 1.6, 'all');
}

add_action('admin_enqueue_scripts', 'stm_startup_styles');

//Register Startup page in admin menu
function stm_register_startup_screen()
{
    $theme = stm_get_theme_info();
    $theme_name = $theme['name'];
    $theme_name_sanitized = 'stm-admin';

    // Work around for theme check.
    $stm_admin_menu_page_creation_method = 'add_menu_page';
    $stm_admin_submenu_page_creation_method = 'add_submenu_page';

	if ( !defined('ENVATO_HOSTED_SITE') ) {
		/*Item Registration*/
		$stm_admin_menu_page_creation_method(
			$theme_name,
			$theme_name,
			'manage_options',
			$theme_name_sanitized,
			'stm_theme_admin_page_functions',
			get_template_directory_uri() . '/assets/admin/images/icon.png',
			'2.1111111111'
		);

		/*Support page*/
		$stm_admin_submenu_page_creation_method(
			$theme_name_sanitized,
			esc_html__('Support', 'motors'),
			esc_html__('Support', 'motors'),
			'manage_options',
			$theme_name_sanitized . '-support',
			'stm_theme_admin_support_page'
		);

		/*System status*/
		$stm_admin_submenu_page_creation_method(
			$theme_name_sanitized,
			esc_html__('System status', 'motors'),
			esc_html__('System status', 'motors'),
			'manage_options',
			$theme_name_sanitized . '-system-status',
			'stm_theme_admin_system_status_page'
		);
		/*Demo Import*/
		$stm_admin_submenu_page_creation_method(
			$theme_name_sanitized,
			esc_html__('Demo import', 'motors'),
			esc_html__('Demo import', 'motors'),
			'manage_options',
			$theme_name_sanitized . '-demos',
			'stm_theme_admin_install_demo_page'
		);
	} else {
		/*Demo Import*/
		$stm_admin_menu_page_creation_method(
			$theme_name,
			$theme_name,
			'manage_options',
			$theme_name_sanitized,
			'stm_theme_admin_install_demo_page',
			get_template_directory_uri() . '/assets/admin/images/icon.png',
			'2.1111111111'
		);
	}

    /*Plugins*/
    $stm_admin_submenu_page_creation_method(
        $theme_name_sanitized,
        esc_html__('Plugins', 'motors'),
        esc_html__('Plugins', 'motors'),
        'manage_options',
        $theme_name_sanitized . '-plugins',
        'stm_theme_admin_plugins_page'
    );

    /*Patching*/
    $stm_admin_submenu_page_creation_method(
        $theme_name_sanitized,
        esc_html__('Patching', 'motors'),
        esc_html__('Patching', 'motors'),
        'manage_options',
        $theme_name_sanitized . '-patching',
        'stm_theme_admin_patching_page'
    );


    /*Listings Location Patch*/
    $stm_admin_submenu_page_creation_method(
        $theme_name_sanitized,
        esc_html__('Location Patch', 'motors'),
        esc_html__('Location Patch', 'motors'),
        'manage_options',
        $theme_name_sanitized . '-location-patching',
        'stm_theme_admin_location_patch_page'
    );

}

add_action('admin_menu', 'stm_register_startup_screen');

function stm_startup_templates($path)
{
    $path = 'admin/screens/' . $path . '.php';

    $located = locate_template($path);

    if ($located) {
        load_template($located);
    }
}

//Startup screen menu page welcome
function stm_theme_admin_page_functions()
{
    stm_startup_templates('startup');
}

/*Support Screen*/
function stm_theme_admin_support_page()
{
    stm_startup_templates('support');
}

/*Install Plugins*/
function stm_theme_admin_plugins_page()
{
    stm_startup_templates('plugins');
}

/*Install Demo*/
function stm_theme_admin_install_demo_page()
{
    stm_startup_templates('install_demo');
}

/*System status*/
function stm_theme_admin_system_status_page()
{
    stm_startup_templates('system_status');
}

/*System status*/
function stm_theme_admin_patching_page()
{
    stm_startup_templates('patching');
}

/*System status*/
function stm_theme_admin_location_patch_page()
{
    stm_startup_templates('location_patch');
}

//Admin tabs
function stm_get_admin_tabs($screen = 'welcome')
{
    $theme = stm_get_theme_info();
    $theme_name = $theme['name'];
    $theme_name_sanitized = 'stm-admin';
    if (empty($screen)) {
        $screen = $theme_name_sanitized;
    }
    $patched = get_option('stm_tax_patched', '');
    ?>
    <div class="clearfix">
        <div class="stm_theme_info">
            <div class="stm_theme_version"><?php echo substr($theme['v'], 0, 3); ?></div>
        </div>
        <div class="stm-about-text-wrap">
            <h1><?php printf(esc_html__('Welcome to %s', 'motors'), $theme_name); ?></h1>
            <div class="stm-about-text about-text">
                <?php add_thickbox(); ?>
                <?php printf(esc_html__('%s is now installed and ready to use! Get ready to build something beautiful. Please register your purchase to get automatic theme updates, import %1$s demos and install premium plugins. Read below for additional information. We hope you enjoy it! %s', 'motors'), $theme_name, '<a href="//youtube.com/embed/kBYBRMpzFHc?KeepThis=true&TB_iframe=true&autoplay=true" target="_blank" class="thickbox_dis">' . esc_attr__('Watch Our Quick Guided Tour!', 'motors') . '</a>'); ?>
            </div>
        </div>
    </div>
    <?php if ( !defined('ENVATO_HOSTED_SITE') ) { ?>
        <h2 class="nav-tab-wrapper <?php echo esc_attr('stm-patch-note' . $patched); ?>">
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized)); ?>"
               class="<?php echo ('welcome' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Registration', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-support')); ?>"
               class="<?php echo ('support' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Support', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-plugins')); ?>"
               class="<?php echo ('plugins' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Plugins', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-demos')); ?>"
               class="<?php echo ('demos' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Install Demos', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('customize.php')); ?>" class="nav-tab"><?php esc_attr_e('Theme Options', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-system-status')); ?>"
               class="<?php echo ('system-status' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('System Status', 'motors'); ?></a>
            <?php if(empty($patched)): ?>
                <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-patching')); ?>"
                   class="<?php echo ('patching' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Patching', 'motors'); ?></a>
            <?php endif; ?>
        </h2>
    <?php } else { ?>
        <h2 class="nav-tab-wrapper <?php echo esc_attr('stm-patch-note' . $patched); ?>">
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized)); ?>"
               class="<?php echo ('demos' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Demo import', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-plugins')); ?>"
               class="<?php echo ('plugins' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Plugins', 'motors'); ?></a>
            <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-demos')); ?>"
            <a href="<?php echo esc_url_raw(admin_url('customize.php')); ?>" class="nav-tab"><?php esc_attr_e('Theme Options', 'motors'); ?></a>
            <?php if(empty($patched)): ?>
                <a href="<?php echo esc_url_raw(admin_url('admin.php?page=' . $theme_name_sanitized . '-patching')); ?>"
                   class="<?php echo ('patching' === $screen) ? 'nav-tab-active' : ''; ?> nav-tab"><?php esc_attr_e('Patching', 'motors'); ?></a>
            <?php endif; ?>
        </h2>
    <?php }
}