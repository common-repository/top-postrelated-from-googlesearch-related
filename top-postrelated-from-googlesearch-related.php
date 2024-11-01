<?php
/*  
	Plugin Name: TOP Postrelated from GoogleSearch Related
	Plugin URI: http://www.prontovacanze.net/
	Description: It suggest post related from GoogleSearch. For more SEO Friendly posts    and best ranking in Google, Yahoo! and Bing search engines.  
	Version: 1.0
	Author: dechigno (Prima Posizione Srl)
	Author URI: http://www.prima-posizione.it/
*/
define( "TITLE_PR_LONG", "GoogleSearch Post Related");
define( "TITLE_PR_SHORT", "GoogleSearch PR");
define( "POWERED_BY"	, "Prontovacanze.net");
define( "LINK_ADMIN"	, "http://www.prontovacanze.net/");


function pr_check_dependencies() {
	// Return true if CURL and DOM XML modules exist and false otherwise
	return ( ( function_exists( 'curl_init' ) || ini_get('allow_url_fopen') ) &&
		( function_exists( 'preg_match' ) || function_exists( 'ereg' ) ) );
}

function pr_admin_init() {
	global $current_user;
	get_currentuserinfo();

	if ($current_user->user_level <  8) { //if not admin, die with message
		wp_die( __('You are not allowed to access this part of the site') );
	}
	wp_enqueue_script('jquery-ui-draggable');
	wp_enqueue_script('jquery-ui-droppable');
	wp_enqueue_script('jquery-timers',  WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/js/jQuery.timers.js');
	/*wp_enqueue_script('jquery-easing',  WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/js/fancybox/jquery.easing-1.3.pack.js');
	wp_enqueue_script('jquery-fancybox',  WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/js/fancybox/jquery.fancybox-1.3.4.pack.js');
	wp_enqueue_style('css-fancybox',  WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/js/fancybox/jquery.fancybox-1.3.4.css');*/
	wp_enqueue_style('css',  WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/css/style.css');
}
add_action('admin_init', 'pr_admin_init');

// Custom Meta Box
function pr_add_custom_box() {
	if( function_exists( 'add_meta_box' )) {
		add_meta_box( 'pr_sidebar', __( TITLE_PR_LONG, 'myplugin_textdomain' ), 
	        'pr_inner_custom_box', 'post', 'side', 'high' );
	}
}
function pr_inner_custom_box() {
	// Use nonce for verification
	echo '<p style="text-align: center" id="loading_pr"><img src="'.WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/ajax-loader.gif" alt="Loading" /></p><div id="pr_sidebar"><ul></ul></div>';
  
}
add_action('admin_head', 'wp_init_js');

function wp_init_js() {
	if( strpos( $_SERVER['REQUEST_URI'], 'post-new.php' ) || strpos( $_SERVER['REQUEST_URI'], 'post.php' ) ){

?>
		<script type="text/javascript">
		
		jQuery(document).ready(function($) {
			var html_ul = "";
			var search_value = "";
			var search_progress = false;
			$.fn.inputdrop = function ( myValue, valEnd ) {
				return this.each(function(){
					//IE support
					if (document.selection) {
							this.focus();
							sel = document.selection.createRange();
							sel.text = myValue;
							this.focus();
					}
					//MOZILLA / NETSCAPE support
					else if (this.selectionStart || this.selectionStart == '0') {
							var startPos = this.selectionStart;
							var endPos = this.selectionEnd;
							var scrollTop = this.scrollTop;
							this.value = this.value.substring(0, startPos)+ myValue+ this.value.substring(endPos,this.value.length);
							this.focus();
							this.selectionStart = startPos + myValue.length;
							this.selectionEnd = startPos + myValue.length;
							this.scrollTop = scrollTop;
					} else {
							this.value += myValue;
							this.focus();
					}
				});
			};
			function createBox( title, link, description ){
				html_ul = html_ul + '<li><p class="img"><a class="iframe" href="' + link + '" target="_blank"><img src="<?php echo WP_PLUGIN_URL . '/top-postrelated-from-googlesearch-related/Preview.png';?>" alt="Anteprima" style="margin-left: 10px;" /></a></p><a href="' + link + '" target="_blank">' + title + '</a><br class="clear" /></li>';
			}
			function searchPost(){
				if( search_value != $("#title").val() && !search_progress ){
					html_ul = "";
					search_value = $("#title").val();
					search_progress = true;
					$("#pr_sidebar ul").html("");
					$("#loading_pr").html('<img src="<?php echo WP_PLUGIN_URL ?>/top-postrelated-from-googlesearch-related/ajax-loader.gif" alt="Loading" />');
					var api_key = "<?php echo get_option('api_key_pr');?>";
					if( api_key.length <= 1 ){
						$("#loading_pr").html("Attenzione, &egrave; necessario configurare il Plugin ed inserire l'API-KEY corretto.");
					}else{
						var data = {
							title : $("#title").val(),
							api_key: "<?php echo get_option('api_key_pr');?>"
						};
						jQuery.post("<?php echo WP_PLUGIN_URL?>/top-postrelated-from-googlesearch-related/ajax/query.php", data, function( response ) {
							try {
								var result_one_page = jQuery.parseJSON( response );
								if( result_one_page.responseData ){
									var array_results = result_one_page.responseData.results;
									for (var i = 0; i < array_results.length; i++ ){
										createBox( array_results[i].titleNoFormatting, array_results[i].postUrl, array_results[i].content );
									}
								}else if( result_one_page.responseDetails == "invalid key" ){
									$("#loading_pr").html("Attenzione. L'Api-Key inserito non &egrave; corretto.");
								}else $("#loading_pr").html("Errore Tecnico. Riprovare pi&ugrave; tardi.");
							}catch(err){}
							
							var data = {
								title : $("#title").val(),
								api_key: "<?php echo get_option('api_key_pr');?>",
								pag : "1"
							};
	
							jQuery.post("<?php echo WP_PLUGIN_URL?>/top-postrelated-from-googlesearch-related/ajax/query.php", data, function( response ) {
								try {
									var result_two_page = jQuery.parseJSON( response );
									if( result_two_page.responseData ){
										var array_results = result_two_page.responseData.results;
										for (var i = 0; i < array_results.length; i++ ){
											createBox( array_results[i].titleNoFormatting, array_results[i].postUrl, array_results[i].content );
										}
										$("#loading_pr").html("");
										$("#pr_sidebar ul").html( html_ul );
										/*$("a.iframe").fancybox({
											'hideOnContentClick': true,
											'width'	: 700,
											'height' : 450
										});*/
										search_progress = false;
										if( $("#edButtonPreview").hasClass("active") && !$.browser.msie ){
											mode_visual();
										}else if( $("#edButtonHTML").hasClass("active")  && !$.browser.msie ){
											mode_html();
										}else if( $("#edButtonPreview").hasClass("active") && $.browser.msie ){
											mode_visual_msie();
										}else if( $("#edButtonHTML").hasClass("active") && $.browser.msie ){
											mode_visual_msie();
										}
									}else if( result_one_page.responseDetails == "invalid key" ){
										$("#loading_pr").html("Attenzione. L'Api-Key inserito non &egrave; corretto.");
									}else $("#loading_pr").html("Errore Tecnico. Riprovare pi&ugrave; tardi.");
								}catch(err){}
							});
						});
					}
					
					
				}
			}
			
			$(document).everyTime(5000, function(){
				searchPost();
			});
			$("#title").blur(function(){
				searchPost();
			})
			
			if( $("#title").val() == ""){
				$("#loading_pr").html("Inserire il titolo del post per avviare la ricerca.");
			}
			if( $.browser.msie ){
				function mode_html_msie(){
					$("#pr_sidebar ul li a").draggable({
						helper: 'clone', 
						cursor: 'move'
					});
					$("#pr_sidebar ul li a").draggable( "enable" );
					
					$("textarea").droppable({
						accept: "#pr_sidebar ul li a",
						drop: function(ev, ui) {
							$("#content").inputdrop( '<a href="'+ui.draggable.attr("href")+'" target="_blank">' + ui.draggable.text() + '</a>', false );
						}
					});
				}
				function mode_visual_msie(){
					$("#pr_sidebar ul li a").draggable( "disable" );
					$("#pr_sidebar ul li a").css("cursor", "pointer");
					$("#pr_sidebar ul li a").click( function(){
						var content_html = tinyMCE.activeEditor.getContent();
						var value_a = $(this).text();
						var href_a = $(this).attr("href");
						var new_value = '<a href="'+href_a+'" target="_blank">'+value_a+'</a>';
						var lenght = (content_html.length - 4);
						content_html = content_html.substr(0, lenght) + " " + new_value + " ";
						tinyMCE.activeEditor.setContent( content_html );
						return false;
					});
				}
				if( $("#edButtonPreview").hasClass("active") ){
					mode_visual_msie();
				}
				if( $("#edButtonHTML").hasClass("active") ){
					mode_html_msie();
				}
				$("#edButtonHTML").click(function(){
					mode_html_msie();
				});
				$("#edButtonPreview").click(function(){
					mode_visual_msie();
				});
				$("#pr_sidebar ul li a").click(function(){
					return false;
				});
			}else{
					
				function mode_visual(){
					$("#pr_sidebar ul li a").draggable( "disable" );
				}
				function mode_html(){
					$("#pr_sidebar ul li a").draggable({
						helper: 'clone', 
						cursor: 'move'
					});
					
					$("#pr_sidebar ul li a").draggable( "enable" );
					
					$("textarea").droppable({
						accept: "#pr_sidebar ul li a",
						drop: function(ev, ui) {
							$("#content").inputdrop( '<a href="'+ui.draggable.attr("href")+'" target="_blank">' + ui.draggable.text() + '</a>', false );
						}
					});
				}
				$("#edButtonHTML").click(function(){
					mode_html();
				});
				$("#edButtonPreview").click(function(){
					mode_visual();
				});
				$("#pr_sidebar ul li a").click(function(){
					return false;
				});
				if( $("#edButtonPreview").hasClass("active") ){
					mode_visual();
				}
				if( $("#edButtonHTML").hasClass("active") ){
					mode_html();
				}
			}
		});
		
		</script>
<?php
	}
}
//Pagina per settare l'API KEY
function pr_config_page() {
	if ( function_exists( 'add_submenu_page' ) )
		add_submenu_page( 'plugins.php', __(TITLE_PR_SHORT.' Configuration'), __(TITLE_PR_SHORT.' Configuration'), 'manage_options', 'top-postrelated-from-googlesearch-related', 'pr_wp_admin' );
}
function pr_wp_admin() {
	global $wp_version;
?>
<div class="updated"><p><strong><?php _e('Configurazione Salvata', 'top-postrelated-from-googlesearch-related' ); ?></strong></p></div>
<?php

	// Now display the options editing screen
	echo '<div class="wrap">';

	// header
	echo "<h2>" . __( 'Configurazione Plugin ' . TITLE_PR_LONG, 'top-postrelated-from-googlesearch-related' ) . "</h2>";
	if( isset( $_POST["api_key_pr"] ) ){
		$api_key_pr = $_POST["api_key_pr"];
		update_option('api_key_pr', $api_key_pr);
	}
	
	// options form
	?>
	<form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
        <p>Per il corretto funzionamento del Plugin &egrave; necessario inserire l'API-KEY.</p>
        <p><a href="http://code.google.com/intl/it-IT/apis/loader/signup.html" target="_blank">Clicca qui</a> per ottenere l'API-KEY relativo al tuo dominio.</p>
        <p><?php _e('API key:', 'top-postrelated-from-googlesearch-related' ); ?>
			<input type="text" name="api_key_pr" value="<?php echo get_option('api_key_pr'); ?>" size="100">
		</p>
		<p class="submit">
			<input type="submit" name="Submit" value="<?php _e('Salva', 'postrelad' ) ?>" />
		</p>
	</form>
</div>
<?php
}

// Check dependencies
if ( !pr_check_dependencies() ) {
	function pr_warning () {
		echo "
		<div class='updated fade'><p>".__('Per il corretto funzionamento del plugin &egrave; necessario abilitare il modulo CURL di PHP.')."</p></div>";
	}

	add_action('admin_notices', 'pr_warning');
	return;
}

//Hook for WP2.7 - MetaBoxes generated with native Wordpress functions to enable storing of their position
global $wp_version;
if (substr($wp_version,0,3) >= '2.7') {
	/* Set Zemanta default position (if it is not already set) */
	require_once(ABSPATH . '/wp-includes/pluggable.php');
	/* Use the admin_menu action to define the custom boxes */
	add_action('admin_menu', 'pr_add_custom_box');
}

add_action( 'admin_menu', 'pr_config_page' );

function pr_activate() {
	chmod( dirname(__FILE__) . "/ajax/query.php", 0755);
}

function pr_deactivate() {
}
function pr_footer_sign(){
	echo '<p style="text-align: center">Powered by <a href="'.LINK_ADMIN.'" target="_blank">'.POWERED_BY.'</a></p>';
}
add_action( 'wp_footer', 'pr_footer_sign' );
add_action( 'pr_active_zemanta', 'pr_activate' );
add_action( 'pr_deactive_zemanta', 'pr_deactivate' );
register_activation_hook(__FILE__, 'pr_activate');
register_deactivation_hook(__FILE__, 'pr_deactivate');
?>