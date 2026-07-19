<?php
/**
 * ITMMS_REST_Content methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Content {

	public function get_announcements(): WP_REST_Response {
		return rest_ensure_response( [ 'announcements' => ITMMS_Announcements::all() ] );
	}

	public function get_public_announcements( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['announcements'] ) ) {
			return rest_ensure_response( [ 'announcements' => [] ] );
		}

		$limit = max( 1, min( 50, absint( $request->get_param( 'limit' ) ) ?: 10 ) );
		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		$announcements = array_map(
			static function ( array $notice ): array {
				return [
					'id'                => (int) $notice['id'],
					'title'             => (string) $notice['title'],
					'content'           => (string) $notice['content'],
					'announcement_type' => (string) $notice['announcement_type'],
					'priority'          => (int) $notice['priority'],
					'start_date'        => (string) $notice['start_date'],
					'end_date'          => (string) $notice['end_date'],
				];
			},
			ITMMS_Announcements::active( $limit, $type )
		);

		return $this->public_cached_response( [ 'announcements' => $announcements ], 30 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_announcement( WP_REST_Request $request ) {
		$notice = ITMMS_Announcements::create( $request->get_json_params() ?: [] );
		return is_wp_error( $notice ) ? $notice : new WP_REST_Response( [ 'announcement' => $notice ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_announcement( WP_REST_Request $request ) {
		$notice = ITMMS_Announcements::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $notice ) ? $notice : rest_ensure_response( [ 'announcement' => $notice ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_announcement( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Announcements::find( $id ) ) {
			return new WP_Error( 'itmms_notice_missing', __( 'Notice not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Announcements::delete( $id ) ) {
			return new WP_Error( 'itmms_notice_delete', __( 'The notice could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_events(): WP_REST_Response {
		return rest_ensure_response( [ 'events' => ITMMS_Events::all() ] );
	}

	public function get_public_events( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['events'] ) ) {
			return rest_ensure_response( [ 'events' => [] ] );
		}

		$limit = max( 1, min( 50, absint( $request->get_param( 'limit' ) ) ?: 10 ) );
		$events = array_map(
			static function ( array $event ): array {
				return [
					'id'          => (int) $event['id'],
					'title'       => (string) $event['title'],
					'description' => (string) $event['description'],
					'start_time'  => (string) $event['start_time'],
					'end_time'    => (string) $event['end_time'],
					'location'    => (string) $event['location'],
				];
			},
			ITMMS_Events::active( $limit )
		);

		return $this->public_cached_response( [ 'events' => $events ], 60 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_event( WP_REST_Request $request ) {
		$event = ITMMS_Events::create( $request->get_json_params() ?: [] );
		return is_wp_error( $event ) ? $event : new WP_REST_Response( [ 'event' => $event ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_event( WP_REST_Request $request ) {
		$event = ITMMS_Events::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $event ) ? $event : rest_ensure_response( [ 'event' => $event ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_event( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Events::find( $id ) ) {
			return new WP_Error( 'itmms_event_missing', __( 'Event not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Events::delete( $id ) ) {
			return new WP_Error( 'itmms_event_delete', __( 'The event could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

}
