<?php
/**
 * Template for upcoming events list widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$itmms_event_labels = [
	'location'  => ( 'bn' === $language ) ? 'স্থান' : __( 'Location', 'masjidos' ),
	'to'        => ( 'bn' === $language ) ? 'থেকে' : __( 'to', 'masjidos' ),
	'time'      => ( 'bn' === $language ) ? 'সময়' : __( 'Time', 'masjidos' ),
	'add_cal'   => ( 'bn' === $language ) ? 'ক্যালেন্ডারে যোগ করুন' : __( 'Add to Calendar', 'masjidos' ),
	/* translators: %d: number of days remaining */
	'days_left' => ( 'bn' === $language ) ? '%d দিন বাকি' : __( '%d days left', 'masjidos' ),
	'day_left'  => ( 'bn' === $language ) ? '১ দিন বাকি' : __( '1 day left', 'masjidos' ),
	'today'     => ( 'bn' === $language ) ? 'আজ' : __( 'Today', 'masjidos' ),
];
?>
<section class="itmms-public-events itmms-public-events--list itmms-public-events--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-events__header">
		<div>
			<span><?php echo esc_html( $location ?: get_bloginfo( 'name' ) ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b>
			<?php
			/* translators: %d: Number of active public events. */
			echo esc_html( sprintf( ( 'bn' === $language ? '%d টি ইভেন্ট' : __( '%d events', 'masjidos' ) ), count( $events ) ) );
			?>
		</b>
	</header>
	<div class="itmms-public-events__list">
		<?php foreach ( $events as $itmms_event ) : ?>
			<?php
			$itmms_start_ts = strtotime( (string) $itmms_event['start_time'] );
			$itmms_end_ts   = ! empty( $itmms_event['end_time'] ) ? strtotime( (string) $itmms_event['end_time'] ) : false;
			$itmms_day      = date_i18n( 'd', $itmms_start_ts );
			$itmms_month    = date_i18n( 'M', $itmms_start_ts );
			$itmms_weekday  = date_i18n( 'D', $itmms_start_ts );
			$itmms_date     = date_i18n( get_option( 'date_format' ), $itmms_start_ts );
			$itmms_time     = date_i18n( get_option( 'time_format' ), $itmms_start_ts );
			$itmms_range    = $itmms_end_ts ? $itmms_time . ' ' . $itmms_event_labels['to'] . ' ' . date_i18n( get_option( 'time_format' ), $itmms_end_ts ) : $itmms_time;

			// Reminder Badge calculation
			$itmms_diff = $itmms_start_ts - time();
			$itmms_days_left = ceil( $itmms_diff / DAY_IN_SECONDS );
			$itmms_badge = '';
			if ( $itmms_days_left === 0 || ( $itmms_days_left < 0 && $itmms_end_ts && $itmms_end_ts >= time() ) ) {
				$itmms_badge = $itmms_event_labels['today'];
			} elseif ( $itmms_days_left === 1 ) {
				$itmms_badge = $itmms_event_labels['day_left'];
			} elseif ( $itmms_days_left > 1 && $itmms_days_left <= 30 ) {
				$itmms_badge = sprintf( $itmms_event_labels['days_left'], $itmms_days_left );
			}
			?>
			<article class="itmms-public-events__item <?php echo ! empty( $itmms_event['image_url'] ) ? 'itmms-public-events__item--has-thumb' : ''; ?>">
				<?php if ( ! empty( $itmms_event['image_url'] ) ) : ?>
					<div class="itmms-public-events__thumb">
						<img src="<?php echo esc_url( $itmms_event['image_url'] ); ?>" alt="<?php echo esc_attr( (string) $itmms_event['title'] ); ?>">
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
							<span class="itmms-public-events__reminder-badge"><?php echo esc_html( $itmms_badge ); ?></span>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( (string) $itmms_event['title'] ); ?></h3>
					<div class="itmms-public-events__meta">
						<span><?php echo esc_html( $itmms_event_labels['time'] ); ?>: <?php echo esc_html( $itmms_range ); ?></span>
						<?php if ( ! empty( $itmms_event['location'] ) ) : ?>
							<span><?php echo esc_html( $itmms_event_labels['location'] ); ?>: <?php echo esc_html( (string) $itmms_event['location'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== (string) $itmms_event['description'] ) : ?>
						<p><?php echo nl2br( esc_html( (string) $itmms_event['description'] ) ); ?></p>
					<?php endif; ?>
					<div class="itmms-public-events__actions">
						<a href="<?php echo esc_url( add_query_arg( 'masjidos_ical', $itmms_event['id'], home_url( '/' ) ) ); ?>" class="itmms-public-events__ical-btn" title="<?php echo esc_attr( $itmms_event_labels['add_cal'] ); ?>">
							<svg viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
							<span><?php echo esc_html( $itmms_event_labels['add_cal'] ); ?></span>
						</a>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
