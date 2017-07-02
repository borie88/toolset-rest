<?php
	
class Toolset_Rest extends WP_REST_Controller {
	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init(){
		add_action( 'rest_api_init', function () {
		    register_rest_route( 'toolset-views/v2', '/(?P<id>[a-zA-Z-_0-9]+)', array(
			    'methods' => WP_REST_Server::READABLE,
			    'callback' => array( $this, 'custom_route_callback' )
			) );
		});
	}

	function  custom_route_callback( $request ){
		global $wpdb;

		// Retrieving the View ID
		if ( null !== $request->get_param( 'id' )){
			$request_param_id = $request->get_param( 'id' );
			$view = get_post( $request_param_id );
			if ( $view != null ){
				$view_id = $view->ID;
			} else {
				$view_by_name = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM 
				$wpdb->posts WHERE post_name = %s", $request_param_id ) );
				$view_id = $view_by_name->ID;
			}
			$settings = get_post_meta( $view_id, '_wpv_settings', true );
			$query_type = $settings['query_type'][0];
		}

		//Handling the GET parameters to fit into the View $args
		if ( $request->get_params() &&
		   !empty( $request->get_params() ) ){
			$get_params = $request->get_params();
			foreach ( $get_params as $key => $value ) {
				$args[ $key ] = $value;
			}
		}

		// It lets you change the query only in the custom Rest Route
		add_filter( 'wpv_filter_query', array( $this, 'toolset_rest_query_filter' ), 99, 3 );

		if ( function_exists( 'get_view_query_results' ) ) {
			///Use our query type to decide whether we should get posts, terms, or users...
			$view_results = get_view_query_results( $view_id, $post_in, $current_user_in, $args );
			if ( 'posts' === $query_type ) {
				$data = new Toolset_Post( $view_results );
				$data = $data->get_list();
			}

			if ( 'taxonomy' === $query_type ) {
				$data = new Toolset_Taxonomy( $view_results );
				$data = $data->get_list();
			}

			if ( 'users' === $query_type ) {
				$data = new Toolset_User( $view_results );
				$data = $data->get_list();
			}

			$response = rest_ensure_response( $data );
			return $response;
		}
	}

	function toolset_rest_query_filter( $query_args, $view_settings, $view_id ) {
	    return apply_filters( 'toolset_rest_query', $query_args, $view_settings, $view_id );
	}
}

$toolset_rest = new Toolset_Rest();
?>