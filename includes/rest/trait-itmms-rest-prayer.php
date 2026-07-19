<?php
/**
 * ITMMS_REST_Prayer methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Prayer {

	/**
	 * Return today's calculated prayer times (public headless JSON).
	 */
	public function get_prayer_times_today(): WP_REST_Response {
		return $this->public_cached_response( ITMMS_SalahAPI::headless_day( ITMMS_Prayer_Times::today() ), 60 );
	}

	/**
	 * Return prayer times for a specific Gregorian date (public headless JSON).
	 */
	public function get_prayer_times_date( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$date_str = (string) $request->get_param( 'date' );
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}

		$date = DateTimeImmutable::createFromFormat( 'Y-m-d', $date_str, $timezone );
		if ( ! $date ) {
			return new WP_Error( 'itmms_invalid_date', __( 'Invalid date. Use YYYY-MM-DD.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$day = ITMMS_Prayer_Times::for_date( $date->setTime( 0, 0, 0 ), $settings );
		return $this->public_cached_response( ITMMS_SalahAPI::headless_day( $day ), 120 );
	}

	/**
	 * Return a month of prayer times as JSON (not the HTML widget).
	 */
	public function get_prayer_times_month_json( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}
		$now = new DateTimeImmutable( 'now', $timezone );
		$year = (int) $request->get_param( 'year' );
		$month = (int) $request->get_param( 'month' );
		if ( $year < 1970 || $year > 2099 ) {
			$year = (int) $now->format( 'Y' );
		}
		if ( $month < 1 || $month > 12 ) {
			$month = (int) $now->format( 'n' );
		}

		$month_data = ITMMS_Prayer_Times::for_month( $year, $month, $settings );
		$days = [];
		foreach ( (array) ( $month_data['days'] ?? [] ) as $day ) {
			if ( is_array( $day ) ) {
				$days[] = ITMMS_SalahAPI::headless_day( $day );
			}
		}

		return rest_ensure_response(
			[
				'year'     => $year,
				'month'    => $month,
				'label'    => (string) ( $month_data['label'] ?? '' ),
				'timezone' => (string) ( $month_data['timezone'] ?? '' ),
				'days'     => $days,
				'meta'     => is_array( $month_data['meta'] ?? null ) ? $month_data['meta'] : [],
			]
		);
	}

	/**
	 * Public SalahAPI 1.0 document.
	 */
	public function get_salahapi_document(): WP_REST_Response {
		return rest_ensure_response( ITMMS_SalahAPI::document() );
	}

	/**
	 * Public SalahAPI CSV feed (fromDate / toDate query params).
	 */
	public function get_salahapi_csv( WP_REST_Request $request ) {
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}
		$now = new DateTimeImmutable( 'now', $timezone );

		$from = (string) $request->get_param( 'fromDate' );
		$to = (string) $request->get_param( 'toDate' );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$from = $now->format( 'Y-m-01' );
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$to = $now->modify( 'last day of this month' )->format( 'Y-m-d' );
		}

		$csv = ITMMS_SalahAPI::csv( $from, $to );
		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'inline; filename="masjidos-salahapi.csv"' );
		return $response;
	}

	public function get_prayer_timetable(): WP_REST_Response {
		return rest_ensure_response(
			[
				'summary' => ITMMS_Prayer_Timetable::summary(),
			]
		);
	}

	public function import_prayer_timetable( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$params  = $request->get_json_params();
		$csv     = isset( $params['csv'] ) ? (string) $params['csv'] : '';
		$mode    = isset( $params['mode'] ) ? sanitize_key( (string) $params['mode'] ) : 'merge';
		$dry_run = ! empty( $params['dry_run'] );
		if ( ! in_array( $mode, [ 'merge', 'replace' ], true ) ) {
			$mode = 'merge';
		}

		if ( '' === trim( $csv ) ) {
			return new WP_Error( 'itmms_empty_csv', __( 'CSV content is required.', 'masjidos' ), [ 'status' => 400 ] );
		}

		if ( strlen( $csv ) > 2 * 1024 * 1024 ) {
			return new WP_Error(
				'itmms_csv_too_large',
				__( 'CSV file is too large. Import one year at a time (about 365 rows).', 'masjidos' ),
				[ 'status' => 413 ]
			);
		}

		$result = ITMMS_Prayer_Timetable::import_csv( $csv, $mode, $dry_run );
		if ( empty( $result['success'] ) ) {
			return new WP_Error(
				'itmms_csv_import_failed',
				$dry_run
					? __( 'CSV validation found no valid rows.', 'masjidos' )
					: __( 'CSV import could not be completed.', 'masjidos' ),
				[
					'status' => 400,
					'data'   => $result,
				]
			);
		}

		return rest_ensure_response( $result );
	}

	public function export_prayer_timetable( WP_REST_Request $request ) {
		$source = sanitize_key( (string) $request->get_param( 'source' ) );
		$year = (int) $request->get_param( 'year' );
		if ( 'calculated' === $source ) {
			if ( $year < 1970 || $year > 2099 ) {
				$year = (int) gmdate( 'Y' );
			}
			$csv = ITMMS_Prayer_Timetable::export_calculated_year_csv( $year );
			$filename = sprintf( 'masjidos-calculated-%d.csv', $year );
		} else {
			$filter_year = ( $year >= 1970 && $year <= 2099 ) ? $year : null;
			$csv         = ITMMS_Prayer_Timetable::export_csv( $filter_year );
			$filename    = $filter_year
				? sprintf( 'masjidos-prayer-timetable-%d.csv', $filter_year )
				: 'masjidos-prayer-timetable.csv';
		}

		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'attachment; filename="' . $filename . '"' );
		return $response;
	}

	public function sample_prayer_timetable() {
		$csv = ITMMS_Prayer_Timetable::sample_csv();
		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'attachment; filename="masjidos-prayer-timetable-sample.csv"' );
		return $response;
	}

	public function clear_prayer_timetable( WP_REST_Request $request ): WP_REST_Response {
		$year = (int) $request->get_param( 'year' );
		if ( $year >= 1970 && $year <= 2099 ) {
			return rest_ensure_response( ITMMS_Prayer_Timetable::clear_year( $year ) );
		}

		ITMMS_Prayer_Timetable::clear();
		return rest_ensure_response(
			[
				'success' => true,
				'summary' => ITMMS_Prayer_Timetable::summary(),
			]
		);
	}

	/**
	 * Return a public prayer times widget for the Features preview modal.
	 */

}
