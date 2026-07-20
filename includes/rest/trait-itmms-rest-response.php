<?php
/**
 * Shared REST response helpers for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Response utilities used by public/admin REST handlers.
 */
trait ITMMS_REST_Response {

	/**
	 * Public GET responses that may be cached briefly by browsers/CDNs.
	 *
	 * @param mixed $data Response data.
	 */
	private function public_cached_response( $data, int $max_age = 60 ): WP_REST_Response {
		$response = rest_ensure_response( $data );
		$response->header( 'Cache-Control', 'public, max-age=' . max( 0, $max_age ) );
		return $response;
	}
}
