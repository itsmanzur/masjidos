<?php
/**
 * ITMMS_Public_Display methods for ITMMS_Public.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_Public_Display {

	/**
	 * Register TV Display rewrite rules.
	 */
	public function register_display_rewrites(): void {
		add_rewrite_rule( '^masjidos-display/?$', 'index.php?masjidos_display=1', 'top' );
	}

	/**
	 * Register TV Display query vars.
	 *
	 * @param array<int,string> $vars Query variables.
	 * @return array<int,string>
	 */
	public function register_display_query_vars( array $vars ): array {
		$vars[] = 'masjidos_display';
		return $vars;
	}

	public function handle_display_template_redirect(): void {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$is_pretty_display = preg_match( '#/masjidos-display/?(\?.*)?$#', $request_uri );
		$is_plain_display  = (int) get_query_var( 'masjidos_display' ) === 1;

		if ( $is_pretty_display || $is_plain_display ) {
			// Disable browser cache to keep prayer times accurate
			nocache_headers();

			$settings = ITMMS_Settings::get_all();
			$template_path = ITMMS_PLUGIN_DIR . 'public/templates/tv-display.php';
			if ( file_exists( $template_path ) ) {
				include $template_path;
				exit;
			}
		}
	}

	/**
	 * Build Google / Outlook / ICS links for an event.
	 *
	 * @param array<string,mixed> $event Event row.
	 * @return array{google:string,outlook:string,ics:string}
	 */
	private function event_calendar_links( array $event ): array {
		$id = absint( $event['id'] ?? 0 );
		$title = html_entity_decode( (string) ( $event['title'] ?? '' ), ENT_QUOTES, 'UTF-8' );
		$description = html_entity_decode( wp_strip_all_tags( (string) ( $event['description'] ?? '' ) ), ENT_QUOTES, 'UTF-8' );
		$location = html_entity_decode( (string) ( $event['location'] ?? '' ), ENT_QUOTES, 'UTF-8' );
		$tz = wp_timezone();

		try {
			$start = new DateTimeImmutable( (string) ( $event['start_time'] ?? 'now' ), $tz );
		} catch ( Exception $e ) {
			$start = new DateTimeImmutable( 'now', $tz );
		}

		try {
			$end = ! empty( $event['end_time'] )
				? new DateTimeImmutable( (string) $event['end_time'], $tz )
				: $start->modify( '+1 hour' );
		} catch ( Exception $e ) {
			$end = $start->modify( '+1 hour' );
		}

		$start_utc = $start->setTimezone( new DateTimeZone( 'UTC' ) );
		$end_utc = $end->setTimezone( new DateTimeZone( 'UTC' ) );
		$google_dates = $start_utc->format( 'Ymd\THis\Z' ) . '/' . $end_utc->format( 'Ymd\THis\Z' );

		$google = add_query_arg(
			[
				'action'   => 'TEMPLATE',
				'text'     => $title,
				'dates'    => $google_dates,
				'details'  => $description,
				'location' => $location,
			],
			'https://calendar.google.com/calendar/render'
		);

		$outlook = add_query_arg(
			[
				'path'    => '/calendar/action/compose',
				'rru'     => 'addevent',
				'subject' => $title,
				'body'    => $description,
				'location'=> $location,
				'startdt' => $start->format( 'Y-m-d\TH:i:s' ),
				'enddt'   => $end->format( 'Y-m-d\TH:i:s' ),
			],
			'https://outlook.live.com/calendar/0/deeplink/compose'
		);

		return [
			'google'  => $google,
			'outlook' => $outlook,
			'ics'     => add_query_arg( 'masjidos_ical', $id, home_url( '/' ) ),
		];
	}

	/**
	 * Handle iCal export template redirect when 'masjidos_ical' URL parameter is present.
	 */
	public function handle_ical_export_redirect(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['masjidos_ical'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = absint( $_GET['masjidos_ical'] );
		$event = null;

		if ( $id >= 90000 ) {
			$current_year = (int) gmdate( 'Y' );
			$islamic = ITMMS_Events::get_islamic_events( $current_year );
			$islamic = array_merge( $islamic, ITMMS_Events::get_islamic_events( $current_year + 1 ) );
			$islamic = array_merge( $islamic, ITMMS_Events::get_islamic_events( $current_year - 1 ) );
			foreach ( $islamic as $ie ) {
				if ( (int) $ie['id'] === $id ) {
					$event = $ie;
					break;
				}
			}
		} else {
			$event = ITMMS_Events::find( $id );
		}

		if ( ! $event ) {
			wp_die( esc_html__( 'Event not found.', 'masjidos' ), esc_html__( 'Error', 'masjidos' ), [ 'response' => 404 ] );
		}

		$dt_start = new DateTime( $event['start_time'] );
		$dt_end = ! empty( $event['end_time'] ) ? new DateTime( $event['end_time'] ) : ( clone $dt_start )->modify( '+1 hour' );

		$dt_start_utc = $dt_start->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
		$dt_end_utc = $dt_end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
		$dt_stamp = ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'Ymd\THis\Z' );

		$title = html_entity_decode( $event['title'], ENT_QUOTES, 'UTF-8' );
		$description = html_entity_decode( wp_strip_all_tags( $event['description'] ), ENT_QUOTES, 'UTF-8' );
		$location = html_entity_decode( $event['location'], ENT_QUOTES, 'UTF-8' );

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="event-' . $id . '.ics"' );

		echo "BEGIN:VCALENDAR\r\n";
		echo "VERSION:2.0\r\n";
		echo "PRODID:-//MasjidOS//WordPress Plugin//EN\r\n";
		echo "CALSCALE:GREGORIAN\r\n";
		echo "BEGIN:VEVENT\r\n";
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Calendar files require unescaped plain text formatting.
		echo "UID:itmms-event-" . $id . "@" . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n";
		echo "DTSTAMP:" . $dt_stamp . "\r\n";
		echo "DTSTART:" . $dt_start_utc . "\r\n";
		echo "DTEND:" . $dt_end_utc . "\r\n";
		echo "SUMMARY:" . str_replace( [ "\r", "\n" ], ' ', $title ) . "\r\n";
		echo "DESCRIPTION:" . str_replace( [ "\r", "\n" ], ' ', $description ) . "\r\n";
		echo "LOCATION:" . str_replace( [ "\r", "\n" ], ' ', $location ) . "\r\n";
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "END:VEVENT\r\n";
		echo "END:VCALENDAR\r\n";
		exit;
	}

}
