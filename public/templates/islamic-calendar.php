<?php
/**
 * Template for Islamic Hijri + Gregorian Dual Calendar widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Fetch settings
$settings = ITMMS_Settings::get_all();
$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );

// Gregorian calendar calculation base
$now = new DateTimeImmutable( 'now', $timezone );
$month_start = new DateTimeImmutable( sprintf( '%04d-%02d-01 00:00:00', $year, $month ), $timezone );
$days_in_month = (int) $month_start->format( 't' );
$first_day_weekday = (int) $month_start->format( 'N' ); // 1 = Monday, 7 = Sunday

// Construct grid days array
$grid_days = [];

// 1. Padding days from the previous month
$prev_month_padding = $first_day_weekday - 1;
if ( $prev_month_padding > 0 ) {
	for ( $i = $prev_month_padding; $i > 0; $i-- ) {
		$pad_date = $month_start->modify( sprintf( '-%d days', $i ) );
		$grid_days[] = [
			'date'          => $pad_date,
			'is_current'    => false,
			'is_prev_month' => true,
			'is_next_month' => false,
		];
	}
}

// 2. Days of the current month
for ( $d = 1; $d <= $days_in_month; $d++ ) {
	$day_date = $month_start->modify( sprintf( '+%d days', $d - 1 ) );
	$grid_days[] = [
		'date'          => $day_date,
		'is_current'    => true,
		'is_prev_month' => false,
		'is_next_month' => false,
	];
}

// 3. Padding days from the next month
$last_day_date = $month_start->modify( sprintf( '+%d days', $days_in_month - 1 ) );
$last_day_weekday = (int) $last_day_date->format( 'N' );
$next_month_padding = 7 - $last_day_weekday;
if ( $next_month_padding > 0 ) {
	for ( $i = 1; $i <= $next_month_padding; $i++ ) {
		$pad_date = $last_day_date->modify( sprintf( '+%d days', $i ) );
		$grid_days[] = [
			'date'          => $pad_date,
			'is_current'    => false,
			'is_prev_month' => false,
			'is_next_month' => true,
		];
	}
}

// Hijri adjustment setting
$hijri_adjustment = (int) ( $settings['hijri_adjustment'] ?? 0 );

// Fixed Hijri Holy Days Registry
$holy_days_registry = [
	'en' => [
		'1-1'   => 'Islamic New Year',
		'1-10'  => 'Ashura',
		'3-12'  => 'Mawlid al-Nabi',
		'7-27'  => 'Isra\' and Mi\'raj',
		'8-15'  => 'Laylat al-Bara\'at',
		'9-1'   => 'Ramadan Begins',
		'9-27'  => 'Laylat al-Qadr',
		'10-1'  => 'Eid al-Fitr',
		'12-9'  => 'Day of Arafah',
		'12-10' => 'Eid al-Adha',
	],
	'bn' => [
		'1-1'   => 'হিজরি নববর্ষ',
		'1-10'  => 'আশুরা',
		'3-12'  => 'ঈদে মিলাদুন্নবী',
		'7-27'  => 'শবে মেরাজ',
		'8-15'  => 'শবে বরাত',
		'9-1'   => 'রমজান শুরু',
		'9-27'  => 'শবে কদর',
		'10-1'  => 'ঈদুল ফিতর',
		'12-9'  => 'আরাফাহ দিবস',
		'12-10' => 'ঈদুল আজহা',
	],
	'ar' => [
		'1-1'   => 'رأس السنة الهجرية',
		'1-10'  => 'عاشوراء',
		'3-12'  => 'المولد النبوي',
		'7-27'  => 'الإسراء والمعراج',
		'8-15'  => 'ليلة البراءة',
		'9-1'   => 'بداية رمضان',
		'9-27'  => 'ليلة القدر',
		'10-1'  => 'عيد الفطر',
		'12-9'  => 'يوم عرفة',
		'12-10' => 'عيد الأضحى',
	],
];

$active_holy_days = $holy_days_registry[ $language ] ?? $holy_days_registry['en'];

// Query mosque events in range
$grid_start_date = $grid_days[0]['date']->format( 'Y-m-d 00:00:00' );
$grid_end_date = end( $grid_days )['date']->format( 'Y-m-d 23:59:59' );

// Retrieve active events from events module if enabled
$events = [];
if ( ! empty( $settings['modules']['events'] ) ) {
	$raw_events = ITMMS_Events::all( 150 );
	foreach ( $raw_events as $event ) {
		$e_start = $event['start_time'];
		$e_end = $event['end_time'] ?: $e_start;
		
		// If event overlaps with calendar grid range
		if ( $e_start <= $grid_end_date && $e_end >= $grid_start_date ) {
			$events[] = $event;
		}
	}
}

// Helper to translate weekday names
$weekdays = [
	'en' => [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ],
	'bn' => [ 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ', 'শুক্র', 'শনি', 'রবি' ],
	'ar' => [ 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد' ],
];
$active_weekdays = $weekdays[ $language ] ?? $weekdays['en'];

// Month names list for the dropdown select controls
$month_dropdown_names = [
	'en' => [
		1  => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
		5  => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
		9  => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
	],
	'bn' => [
		1  => 'জানুয়ারি', 2 => 'ফেব্রুয়ারি', 3 => 'মার্চ', 4 => 'এপ্রিল',
		5  => 'মে', 6 => 'জুন', 7 => 'জুলাই', 8 => 'আগস্ট',
		9  => 'সেপ্টেম্বর', 10 => 'অক্টোবর', 11 => 'নভেম্বর', 12 => 'ডিসেম্বর'
	],
	'ar' => [
		1  => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
		5  => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
		9  => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
	]
];
$active_dropdown_months = $month_dropdown_names[ $language ] ?? $month_dropdown_names['en'];

// Formatted month title label
$current_month_title = date_i18n( 'F Y', $month_start->getTimestamp() );
if ( 'bn' === $language ) {
	$current_month_title = $active_dropdown_months[ $month ] . ' ' . ITMMS_Hijri::number( (string) $year, 'bn' );
} elseif ( 'ar' === $language ) {
	$current_month_title = $active_dropdown_months[ $month ] . ' ' . ITMMS_Hijri::number( (string) $year, 'ar' );
}

$hijri_month_range = ITMMS_Hijri::range_label( $month_start, $last_day_date, $hijri_adjustment, $language );
?>
<div class="itmms-public-calendar itmms-public-calendar--lang-<?php echo esc_attr( $language ); ?>" <?php if ( $show_navigation ) : ?> data-itmms-calendar data-endpoint="<?php echo esc_url( rest_url( 'masjidos/v1/calendar' ) ); ?>" data-month="<?php echo esc_attr( (string) $month ); ?>" data-year="<?php echo esc_attr( (string) $year ); ?>" data-current-month="<?php echo esc_attr( $now->format( 'n' ) ); ?>" data-current-year="<?php echo esc_attr( $now->format( 'Y' ) ); ?>" data-language="<?php echo esc_attr( $language ); ?>" data-title="<?php echo esc_attr( (string) $atts['title'] ); ?>" data-error="<?php echo esc_attr( $active_labels['no_events'] ); ?>"<?php endif; ?>>
	
	<!-- CALENDAR HEADER -->
	<div class="itmms-public-calendar__header">
		<div>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p>
				<span><?php echo esc_html( $current_month_title ); ?></span>
				<?php if ( ! empty( $hijri_month_range ) ) : ?>
					<span class="itmms-public-calendar__dot-sep"></span>
					<span><?php echo esc_html( $hijri_month_range ); ?></span>
				<?php endif; ?>
			</p>
		</div>
		
		<?php if ( $show_navigation ) : ?>
			<div class="itmms-public-calendar__nav" aria-label="<?php echo esc_attr( $active_labels['navigation'] ); ?>">
				<button type="button" data-itmms-calendar-step="-1" aria-label="<?php echo esc_attr( $active_labels['previous'] ); ?>" title="<?php echo esc_attr( $active_labels['previous'] ); ?>">&#8249;</button>
				
				<label>
					<span class="screen-reader-text"><?php echo esc_html( $active_labels['month'] ); ?></span>
					<select data-itmms-calendar-month aria-label="<?php echo esc_attr( $active_labels['month'] ); ?>">
						<?php foreach ( $active_dropdown_months as $num => $name ) : ?>
							<option value="<?php echo esc_attr( (string) $num ); ?>" <?php selected( $month, $num ); ?>><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				
				<label>
					<span class="screen-reader-text"><?php echo esc_html( $active_labels['year'] ); ?></span>
					<select data-itmms-calendar-year aria-label="<?php echo esc_attr( $active_labels['year'] ); ?>">
						<?php for ( $y_opt = max( 1970, $year - 5 ); $y_opt <= min( 2099, $year + 5 ); $y_opt++ ) : ?>
							<option value="<?php echo esc_attr( (string) $y_opt ); ?>" <?php selected( $year, $y_opt ); ?>><?php echo esc_html( ITMMS_Hijri::number( (string) $y_opt, $language ) ); ?></option>
						<?php endfor; ?>
					</select>
				</label>
				
				<button type="button" data-itmms-calendar-step="1" aria-label="<?php echo esc_attr( $active_labels['next'] ); ?>" title="<?php echo esc_attr( $active_labels['next'] ); ?>">&#8250;</button>
				<button type="button" class="itmms-public-calendar__current" data-itmms-calendar-current <?php disabled( $month === (int) $now->format( 'n' ) && $year === (int) $now->format( 'Y' ) ); ?>><?php echo esc_html( $active_labels['current_month'] ); ?></button>
			</div>
		<?php endif; ?>
	</div>
	
	<!-- CALENDAR GRID -->
	<div class="itmms-public-calendar__grid-container">
		
		<!-- Weekday Labels -->
		<div class="itmms-public-calendar__weekdays">
			<?php foreach ( $active_weekdays as $wday ) : ?>
				<div class="itmms-public-calendar__weekday-label"><?php echo esc_html( $wday ); ?></div>
			<?php endforeach; ?>
		</div>
		
		<!-- Calendar Cells -->
		<div class="itmms-public-calendar__days-grid">
			<?php foreach ( $grid_days as $grid_day ) : ?>
				<?php
				$g_date = $grid_day['date'];
				$g_day_str = $g_date->format( 'Y-m-d' );
				$is_today = ( $g_day_str === $today );
				
				// Compute Hijri Date Details
				$hijri_details = ITMMS_Hijri::for_date( $g_date, $hijri_adjustment, $language );
				$h_day = (int) $hijri_details['day'];
				$h_month = (int) $hijri_details['month'];
				$h_month_name = (string) $hijri_details['month_name'];
				
				// Holy Day Matcher
				$holy_key = $h_month . '-' . $h_day;
				$holy_day_label = $active_holy_days[ $holy_key ] ?? '';
				$is_holy_day = ! empty( $holy_day_label );
				
				// Local Events Matcher
				$day_events = [];
				foreach ( $events as $event ) {
					$start_date_str = substr( $event['start_time'], 0, 10 );
					$end_date_str = $event['end_time'] ? substr( $event['end_time'], 0, 10 ) : $start_date_str;
					
					if ( $g_day_str >= $start_date_str && $g_day_str <= $end_date_str ) {
						$day_events[] = $event;
					}
				}
				$has_events = ! empty( $day_events );
				
				// Class list builder
				$cell_classes = [ 'itmms-public-calendar__cell' ];
				if ( ! $grid_day['is_current'] ) {
					$cell_classes[] = 'is-outside-month';
				}
				if ( $is_today ) {
					$cell_classes[] = 'is-today';
				}
				if ( $is_holy_day ) {
					$cell_classes[] = 'is-holy-day';
				}
				if ( $has_events ) {
					$cell_classes[] = 'has-events';
				}
				?>
				<div class="<?php echo esc_attr( implode( ' ', $cell_classes ) ); ?>" data-gregorian-date="<?php echo esc_attr( $g_day_str ); ?>" data-hijri-date-label="<?php echo esc_attr( $hijri_details['label'] ); ?>">
					
					<!-- Day Numbers Header -->
					<div class="itmms-public-calendar__cell-header">
						<span class="itmms-public-calendar__gregorian-num">
							<?php echo esc_html( ITMMS_Hijri::number( $g_date->format( 'j' ), $language ) ); ?>
						</span>
						<span class="itmms-public-calendar__hijri-num" title="<?php echo esc_attr( $hijri_details['label'] ); ?>">
							<?php
							// Show month name on Hijri 1st day of the month
							if ( 1 === $h_day ) {
								echo esc_html( ITMMS_Hijri::number( (string) $h_day, $language ) . ' ' . $h_month_name );
							} else {
								echo esc_html( ITMMS_Hijri::number( (string) $h_day, $language ) );
							}
							?>
						</span>
					</div>
					
					<!-- Events & Holy Days Body -->
					<div class="itmms-public-calendar__cell-body">
						<?php if ( $is_holy_day ) : ?>
							<div class="itmms-public-calendar__holy-label" title="<?php echo esc_attr( $holy_day_label ); ?>">
								★ <?php echo esc_html( $holy_day_label ); ?>
							</div>
						<?php endif; ?>
						
						<?php if ( $has_events ) : ?>
							<div class="itmms-public-calendar__events-list">
								<?php foreach ( $day_events as $d_event ) : ?>
									<div class="itmms-public-calendar__event-item" title="<?php echo esc_attr( $d_event['title'] . ( $d_event['location'] ? ' @ ' . $d_event['location'] : '' ) ); ?>">
										<span class="itmms-public-calendar__event-dot"></span>
										<span class="itmms-public-calendar__event-title"><?php echo esc_html( $d_event['title'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
							<!-- Mobile indicator -->
							<div class="itmms-public-calendar__mobile-event-dots">
								<?php foreach ( $day_events as $index => $d_event ) : ?>
									<span class="itmms-public-calendar__mobile-event-dot"></span>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<!-- MOBILE EVENT LIST DRAWER (Shown on tap under the calendar on mobile) -->
	<div class="itmms-public-calendar__mobile-events-drawer" id="itmms-calendar-mobile-drawer" style="display: none;">
		<h4 id="itmms-calendar-drawer-title">-</h4>
		<div class="itmms-public-calendar__drawer-list" id="itmms-calendar-drawer-list"></div>
	</div>
</div>
