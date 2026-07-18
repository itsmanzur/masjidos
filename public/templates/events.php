<?php
/**
 * Template for upcoming events list widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$itmms_event_labels = [
	'en' => [
		'location'  => __( 'Location', 'masjidos' ),
		'to'        => __( 'to', 'masjidos' ),
		'time'      => __( 'Time', 'masjidos' ),
		'add_cal'   => __( 'Add to Calendar', 'masjidos' ),
		'google'    => __( 'Google Calendar', 'masjidos' ),
		'outlook'   => __( 'Outlook', 'masjidos' ),
		'apple'     => __( 'Apple Calendar (.ics)', 'masjidos' ),
		/* translators: %s: number of days remaining */
		'days_left' => __( '%s days left', 'masjidos' ),
		'day_left'  => __( '1 day left', 'masjidos' ),
		'today'     => __( 'Today', 'masjidos' ),
		/* translators: %s: Number of active public events. */
		'count'     => __( '%s events', 'masjidos' ),
		'count_one' => __( '1 event', 'masjidos' ),
	],
	'bn' => [
		'location'  => 'স্থান',
		'to'        => 'থেকে',
		'time'      => 'সময়',
		'add_cal'   => 'ক্যালেন্ডারে যোগ করুন',
		'google'    => 'Google Calendar',
		'outlook'   => 'Outlook',
		'apple'     => 'Apple Calendar (.ics)',
		'days_left' => '%s দিন বাকি',
		'day_left'  => '১ দিন বাকি',
		'today'     => 'আজ',
		'count'     => '%s টি ইভেন্ট',
		'count_one' => '১ টি ইভেন্ট',
	],
	'ar' => [
		'location'  => 'الموقع',
		'to'        => 'إلى',
		'time'      => 'الوقت',
		'add_cal'   => 'أضف إلى التقويم',
		'google'    => 'Google Calendar',
		'outlook'   => 'Outlook',
		'apple'     => 'Apple Calendar (.ics)',
		'days_left' => 'متبقي %s أيام',
		'day_left'  => 'متبقي يوم واحد',
		'today'     => 'اليوم',
		'count'     => '%s فعاليات',
		'count_one' => 'فعالية واحدة',
	],
];
$itmms_labels = $itmms_event_labels[ $language ] ?? $itmms_event_labels['en'];
$itmms_count  = count( $events );
$itmms_count_label = 1 === $itmms_count
	? $itmms_labels['count_one']
	: sprintf(
		$itmms_labels['count'],
		class_exists( 'ITMMS_Hijri' )
			? ITMMS_Hijri::number( (string) $itmms_count, $language )
			: (string) $itmms_count
	);
?>
<section class="itmms-public-events itmms-public-events--list itmms-public-events--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-events__header">
		<div>
			<span class="itmms-public-events__eyebrow"><?php echo esc_html( $location ?: get_bloginfo( 'name' ) ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b><?php echo esc_html( $itmms_count_label ); ?></b>
	</header>
	<div class="itmms-public-events__list">
		<?php foreach ( $events as $itmms_event ) : ?>
			<?php
			$itmms_start_ts = strtotime( (string) $itmms_event['start_time'] );
			$itmms_end_ts   = ! empty( $itmms_event['end_time'] ) ? strtotime( (string) $itmms_event['end_time'] ) : false;
			$itmms_day_raw  = date_i18n( 'd', $itmms_start_ts );
			$itmms_month    = date_i18n( 'M', $itmms_start_ts );
			$itmms_weekday  = date_i18n( 'D', $itmms_start_ts );
			$itmms_day      = class_exists( 'ITMMS_Hijri' )
				? ITMMS_Hijri::number( (string) $itmms_day_raw, $language )
				: (string) $itmms_day_raw;
			$itmms_date     = date_i18n( get_option( 'date_format' ), $itmms_start_ts );
			$itmms_time     = date_i18n( get_option( 'time_format' ), $itmms_start_ts );
			$itmms_range    = $itmms_end_ts ? $itmms_time . ' ' . $itmms_labels['to'] . ' ' . date_i18n( get_option( 'time_format' ), $itmms_end_ts ) : $itmms_time;

			$itmms_diff      = $itmms_start_ts - time();
			$itmms_days_left = (int) ceil( $itmms_diff / DAY_IN_SECONDS );
			$itmms_badge     = '';
			$itmms_badge_mod = '';
			if ( 0 === $itmms_days_left || ( $itmms_days_left < 0 && $itmms_end_ts && $itmms_end_ts >= time() ) ) {
				$itmms_badge     = $itmms_labels['today'];
				$itmms_badge_mod = 'is-today';
			} elseif ( 1 === $itmms_days_left ) {
				$itmms_badge     = $itmms_labels['day_left'];
				$itmms_badge_mod = 'is-soon';
			} elseif ( $itmms_days_left > 1 && $itmms_days_left <= 30 ) {
				$itmms_days_label = class_exists( 'ITMMS_Hijri' )
					? ITMMS_Hijri::number( (string) $itmms_days_left, $language )
					: (string) $itmms_days_left;
				$itmms_badge     = sprintf( $itmms_labels['days_left'], $itmms_days_label );
				$itmms_badge_mod = $itmms_days_left <= 7 ? 'is-soon' : 'is-upcoming';
			}

			$itmms_item_classes = [ 'itmms-public-events__item' ];
			if ( ! empty( $itmms_event['image_url'] ) ) {
				$itmms_item_classes[] = 'itmms-public-events__item--has-thumb';
			}
			if ( $itmms_badge_mod ) {
				$itmms_item_classes[] = 'itmms-public-events__item--' . $itmms_badge_mod;
			}
			?>
			<article class="<?php echo esc_attr( implode( ' ', $itmms_item_classes ) ); ?>">
				<?php if ( ! empty( $itmms_event['image_url'] ) ) : ?>
					<div class="itmms-public-events__thumb">
						<img src="<?php echo esc_url( $itmms_event['image_url'] ); ?>" alt="<?php echo esc_attr( (string) $itmms_event['title'] ); ?>" loading="lazy">
					</div>
				<?php endif; ?>
				<div class="itmms-public-events__date">
					<span><?php echo esc_html( $itmms_month ); ?></span>
					<strong><?php echo esc_html( $itmms_day ); ?></strong>
					<small><?php echo esc_html( $itmms_weekday ); ?></small>
				</div>
				<div class="itmms-public-events__body">
					<div class="itmms-public-events__item-head">
						<time datetime="<?php echo esc_attr( str_replace( ' ', 'T', (string) $itmms_event['start_time'] ) ); ?>"><?php echo esc_html( $itmms_date ); ?></time>
						<?php if ( ! empty( $itmms_badge ) ) : ?>
							<span class="itmms-public-events__reminder-badge <?php echo esc_attr( $itmms_badge_mod ); ?>"><?php echo esc_html( $itmms_badge ); ?></span>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( (string) $itmms_event['title'] ); ?></h3>
					<div class="itmms-public-events__meta">
						<span><b><?php echo esc_html( $itmms_labels['time'] ); ?></b><?php echo esc_html( $itmms_range ); ?></span>
						<?php if ( ! empty( $itmms_event['location'] ) ) : ?>
							<span><b><?php echo esc_html( $itmms_labels['location'] ); ?></b><?php echo esc_html( (string) $itmms_event['location'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== (string) $itmms_event['description'] ) : ?>
						<p><?php echo nl2br( esc_html( (string) $itmms_event['description'] ) ); ?></p>
					<?php endif; ?>
					<div class="itmms-public-events__actions">
						<?php
						$itmms_cal_links = $this->event_calendar_links( $itmms_event );
						$itmms_menu_id   = 'itmms-cal-menu-' . absint( $itmms_event['id'] );
						?>
						<div class="itmms-public-events__cal">
							<button type="button" class="itmms-public-events__ical-btn" data-itmms-cal-toggle aria-expanded="false" aria-controls="<?php echo esc_attr( $itmms_menu_id ); ?>">
								<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" fill="none" stroke="currentColor" stroke-width="2"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" fill="none" stroke="currentColor" stroke-width="2"></path></svg>
								<span><?php echo esc_html( $itmms_labels['add_cal'] ); ?></span>
							</button>
							<div class="itmms-public-events__cal-menu" id="<?php echo esc_attr( $itmms_menu_id ); ?>" hidden>
								<a href="<?php echo esc_url( $itmms_cal_links['google'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $itmms_labels['google'] ); ?></a>
								<a href="<?php echo esc_url( $itmms_cal_links['outlook'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $itmms_labels['outlook'] ); ?></a>
								<a href="<?php echo esc_url( $itmms_cal_links['ics'] ); ?>"><?php echo esc_html( $itmms_labels['apple'] ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
