<?php
/**
 * Template for upcoming events list widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_event_labels = [
	'location' => ( 'bn' === $language ) ? 'স্থান' : __( 'Location', 'masjidos' ),
	'to'       => ( 'bn' === $language ) ? 'থেকে' : __( 'to', 'masjidos' ),
	'time'     => ( 'bn' === $language ) ? 'সময়' : __( 'Time', 'masjidos' ),
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
			?>
			<article class="itmms-public-events__item">
				<div class="itmms-public-events__date">
					<span><?php echo esc_html( $itmms_month ); ?></span>
					<strong><?php echo esc_html( $itmms_day ); ?></strong>
					<small><?php echo esc_html( $itmms_weekday ); ?></small>
				</div>
				<div class="itmms-public-events__body">
					<div class="itmms-public-events__item-head">
						<time datetime="<?php echo esc_attr( str_replace( ' ', 'T', (string) $itmms_event['start_time'] ) ); ?>"><?php echo esc_html( $itmms_date ); ?></time>
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
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>
