<?php
/**
 * ITMMS_REST_Minbar methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Minbar {

	public function get_khutbahs(): WP_REST_Response {
		return rest_ensure_response(
			[
				'khutbahs'   => ITMMS_Khutbah::all(),
				'stats'      => ITMMS_Khutbah::stats(),
				'categories' => ITMMS_Khutbah::categories(),
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_khutbah( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: [];
		$item = ITMMS_Khutbah::create( $params );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$similar = ITMMS_Khutbah::find_similar_topics( (string) ( $item['topic'] ?? '' ), 6, (int) ( $item['id'] ?? 0 ) );
		return new WP_REST_Response(
			[
				'khutbah'  => $item,
				'similar'  => $similar,
			],
			201
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_khutbah( WP_REST_Request $request ) {
		$item = ITMMS_Khutbah::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$similar = ITMMS_Khutbah::find_similar_topics( (string) ( $item['topic'] ?? '' ), 6, (int) ( $item['id'] ?? 0 ) );
		return rest_ensure_response(
			[
				'khutbah' => $item,
				'similar' => $similar,
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_khutbah( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Khutbah::find( $id ) ) {
			return new WP_Error( 'itmms_khutbah_missing', __( 'Khutbah not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Khutbah::delete( $id ) ) {
			return new WP_Error( 'itmms_khutbah_delete', __( 'The khutbah could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_dashboard(): WP_REST_Response {
		return rest_ensure_response( ITMMS_Minbar::dashboard() );
	}

	public function get_minbar_profiles(): WP_REST_Response {
		return rest_ensure_response( [ 'profiles' => ITMMS_Minbar::profiles_all() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_minbar_profile( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::profile_create( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : new WP_REST_Response( [ 'profile' => $item ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_minbar_profile( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::profile_update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'profile' => $item ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_profile( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Minbar::profile_find( $id ) ) {
			return new WP_Error( 'itmms_profile_missing', __( 'Khatib profile not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		if ( ! ITMMS_Minbar::profile_delete( $id ) ) {
			return new WP_Error( 'itmms_profile_delete', __( 'Could not delete profile.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_schedule(): WP_REST_Response {
		return rest_ensure_response(
			[
				'schedule' => ITMMS_Minbar::schedule_all(),
				'upcoming' => ITMMS_Minbar::schedule_upcoming( 12 ),
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_minbar_schedule( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::schedule_create( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : new WP_REST_Response( [ 'entry' => $item ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_minbar_schedule( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::schedule_update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'entry' => $item ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_schedule( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Minbar::schedule_find( $id ) ) {
			return new WP_Error( 'itmms_schedule_missing', __( 'Schedule entry not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		if ( ! ITMMS_Minbar::schedule_delete( $id ) ) {
			return new WP_Error( 'itmms_schedule_delete', __( 'Could not delete schedule entry.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_plans(): WP_REST_Response {
		return rest_ensure_response( [ 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_minbar_plan( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::plan_save( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'plan' => $item, 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_plan( WP_REST_Request $request ) {
		$id = sanitize_key( (string) $request['id'] );
		ITMMS_Minbar::plan_delete( $id );
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id, 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	public function search_minbar_references( WP_REST_Request $request ): WP_REST_Response {
		$q = sanitize_text_field( (string) $request->get_param( 'q' ) );
		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		return rest_ensure_response(
			[
				'results'    => ITMMS_Minbar::search_references( $q, $type ?: 'all' ),
				'bookmarks'  => ITMMS_Minbar::get_bookmarks(),
			]
		);
	}

	public function get_minbar_bookmarks(): WP_REST_Response {
		return rest_ensure_response( [ 'bookmarks' => ITMMS_Minbar::get_bookmarks() ] );
	}

	public function add_minbar_bookmark( WP_REST_Request $request ): WP_REST_Response {
		$bookmarks = ITMMS_Minbar::bookmark_add( $request->get_json_params() ?: [] );
		return rest_ensure_response( [ 'bookmarks' => $bookmarks ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_bookmark( WP_REST_Request $request ) {
		$id = sanitize_key( (string) $request['id'] );
		$bookmarks = ITMMS_Minbar::bookmark_remove( $id );
		return rest_ensure_response( [ 'bookmarks' => $bookmarks, 'deleted' => true ] );
	}

}
