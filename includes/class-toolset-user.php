<?php

class Toolset_User extends WP_REST_Users_Controller {
	function __construct( $view_results ) {
		$this->view_results = $view_results;
	}
	function get_list () {
		$data = [];
		foreach ( $this->view_results as $item ) {
			$this->taxonomy = $item->taxonomy;
			$item = $this->prepare_item_for_response ( $item, $request );
	    	$data[] = $this->prepare_response_for_collection ( $item );
		}
		$response = $data;
		return $response;
	}
}