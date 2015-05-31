<?php

/*
Gravity forms load scripts in footer
------------------------------------- */
add_filter("gform_init_scripts_footer", "init_scripts");
function init_scripts() {
return true;
}

// Gravity Forms anchor - disable auto scrolling of forms
add_filter("gform_confirmation_anchor", create_function("","return false;"));

/*
Customize Login Screen
------------------------------------- */
// custom stylesheet
function custom_login_css() {
echo '<link rel="stylesheet" type="text/css" href="'.get_stylesheet_directory_uri().'/css/main.min.css">';
echo '<script src="//use.typekit.net/orf6ofm.js"></script>
<script>try{Typekit.load();}catch(e){}</script>';
}
add_action('login_head', 'custom_login_css');


// change logo link destination
function my_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
return 'The Offline Society';
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );

// login redirect URL
function admin_login_redirect( $redirect_to, $request, $user )
{
global $user;
if( isset( $user->roles ) && is_array( $user->roles ) ) {
if( in_array( "administrator", $user->roles ) ) {
return home_url();
} else {
return home_url();
}
}
else
{
return $redirect_to;
}
}
add_filter("login_redirect", "admin_login_redirect", 10, 3);



/*
DISABLE COMMENTS
------------------------------------- */
include('functions/disable-comments.php');


/*
CUSTOM USER FIELDS
------------------------------------- */
include('functions/custom-user-fields.php');

/*
UPDATE USER ROLES
------------------------------------- */
$role = add_role( 'member', 'Member', array(
    'read' => true, // True allows that capability
) );

remove_role( 'subscriber' );
remove_role( 'contributor' );
remove_role( 'author' );
remove_role( 'editor' );


/*
CHECK FOR TIMBER
------------------------------------- */
if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php' ) ) . '</a></p></div>';
		} );
	return;
}

/*
CUSTOMIZE GRAVITY FORM BUTTONS
------------------------------------- */
// filter the Gravity Forms button type
add_filter( 'gform_submit_button_1', 'form_submit_button', 10, 2 );
function form_submit_button( $button, $form ) {
    return "<button class='btn-primary' id='gform_submit_button_{$form['id']}'><span>Start my Application</span></button>";
}


/*
AUTO LOGIN AFTER USER REGISTRATION
------------------------------------- */
add_action( 'gform_user_registered', 'pi_gravity_registration_autologin', 10, 4 );
/**
 * Auto login after registration.
 */
function pi_gravity_registration_autologin( $user_id, $user_config, $entry, $password ) {
	$user = get_userdata( $user_id );
	$user_login = $user->user_login;
	$user_password = $password;

    wp_signon( array(
		'user_login' => $user_login,
		'user_password' =>  $user_password,
		'remember' => false
    ) );

    if ( is_wp_error($user) )
		echo $user->get_error_message();
}

/*
Timber StarterSite Functions
------------------------------------- */
class StarterSite extends TimberSite {

	function __construct() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		parent::__construct();
	}

	function register_post_types() {
		//this is where you can register custom post types
	}

	function register_taxonomies() {
		//this is where you can register custom taxonomies
	}

	function add_to_context( $context ) {
		//$context['stuff'] = 'I am a value set in your functions.php file';
		//$context['notes'] = 'These values are available everytime you call Timber::get_context();';
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		
		return $context;
	}

	function add_to_twig( $twig ) {
		/* this is where you can add your own fuctions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		$twig->addFilter( 'myfoo', new Twig_Filter_Function( 'myfoo' ) );

    // Pass Gravity Form function through to Twig
    // --------------------------------------------
		$gravityfunction = new Twig_SimpleFunction('displaygform', function ($id) {
        $form = gravity_form($id, false, false, false, '', $ajax=false );
        return $form;
      });
      $twig->addFunction($gravityfunction);

		return $twig;
	}

	function my_router() {
		Timber::add_route('/user/:userid', function($params){
		    //$query = 'posts_per_page=3&post_type='.$params['name'];
		    Timber::load_template('template-home.php');
		});

	}

}

new StarterSite();  // init classs

//function myfoo( $text ) {
//	$text .= ' bar!';
//	return $text;
//}
