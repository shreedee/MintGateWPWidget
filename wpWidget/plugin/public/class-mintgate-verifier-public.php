<?php


/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://newearthart.tech
 * @since      1.0.0
 *
 * @package    Mintgate_Verifier
 * @subpackage Mintgate_Verifier/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mintgate_Verifier
 * @subpackage Mintgate_Verifier/public
 * @author     new earth art @ tech <info@newearthart.tech>
 */
class Mintgate_Verifier_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		if (!session_id()) {
			session_start();
		}


	}

	/**
	 * Registers Rest API endpoint
	 *
	 * @since    1.0.0
	*/
	public function register_api(){

		/*
		register_rest_route( 'ne-mintgate/v1', '/wallet/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array($this,'checkWallet'),
		  ) );
		*/

		register_rest_route( 'ne-mintgate/v1', '/wallet', array(
			'methods' => 'POST',
			'callback' => array($this,'checkWallet'),
		  ) );

	}

	
	//availbale at http://localhost:56395/?rest_route=/ne-mintgate/v1/wallet/1

	public function checkWallet( WP_REST_Request $request ) {
		// You can access parameters via direct array access on the object:
		
		xdebug_break();

		try
		{
			$signed = $request->get_param( 'signed' );

			if(strlen($signed) == 0){
				throw new Exception('no signature found');
			}

			return rest_ensure_response(array(
				status=>"done"
			));

		}
		catch(Exception $e) {

			return rest_ensure_response(array(
				status=>"error",
				error=>$e->getMessage()
			));
		}
		
	  }

	/**
	 * Filters content
	 *
	 * @since    1.0.0
	 */
	public function filter_the_content($content) {

		$tid = $this->tokenId();

		if(null == $tid){
			return $content;
		}

		$options = get_option( 'ne_mintgate_plugin_options' );

		if (strlen($options['api_key']) == 0 ||  strlen($options['userid']) == 0){
			return "<div class=\"ne_error ne_minigate_no_sesttings\">NE Settings are not initialized</div>";
		}

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wallet-data.php';
		
		$wallet = WalletData::newNounce(); 

		$_SESSION['wallet'] = $wallet;

		return "<div id=\"mint-gated\" tid=\"".esc_attr($tid)."\" nounce=\"".esc_attr($wallet->nounce)."\"></div>";

	}

	private function tokenId(){

		if (! ( is_singular() && in_the_loop() && is_main_query() )) {
			return null;
		}

		$value = get_post_meta( get_the_ID(), 'mintgate_meta_link_value_key', true );

		if (strlen($value) == 0){
			return null;
		}

		return $value;

	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mintgate_Verifier_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mintgate_Verifier_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mintgate-verifier-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//xdebug_break();
		wp_enqueue_script( $this->plugin_name."mint-verifier", plugin_dir_url( __FILE__ ) . 'js/dist/mint-verifier.js', null, $this->version, true );

	}

}