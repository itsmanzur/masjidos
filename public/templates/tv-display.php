<?php
/**
 * Standalone template for TV / Fullscreen Display.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound


// Fetch settings and calculations
$settings = ITMMS_Settings::get_all();
$data     = ITMMS_Prayer_Times::today();
$meta     = $data['meta'] ?? [];
$prayers  = $data['prayers'] ?? [];
$next     = $data['next_prayer'] ?? [];

// Resolve URL overrides or default settings
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$theme      = sanitize_key( $_GET['theme'] ?? $settings['tv_theme'] ?? 'dark' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$lang       = sanitize_key( $_GET['lang'] ?? $settings['language'] ?? 'en' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$font_size  = sanitize_key( $_GET['font_size'] ?? $settings['tv_font_size'] ?? 'normal' );
$logo_url   = esc_url( $settings['tv_logo_url'] ?: '' );
$masjid_name = esc_html( $settings['masjid_name'] ?: get_bloginfo( 'name' ) );

// Allowed choices validation
if ( ! in_array( $theme, [ 'dark', 'light', 'green' ], true ) ) {
	$theme = 'dark';
}
if ( ! in_array( $lang, [ 'en', 'bn', 'ar' ], true ) ) {
	$lang = 'en';
}
if ( ! in_array( $font_size, [ 'small', 'normal', 'large', 'xlarge' ], true ) ) {
	$font_size = 'normal';
}

// Labels translations mapping
$labels = [
	'en' => [
		'azan'         => 'Azan',
		'iqamah'       => 'Iqamah',
		'next_prayer'  => 'Next Prayer',
		'fajr'         => 'Fajr',
		'sunrise'      => 'Sunrise',
		'dhuhr'        => 'Dhuhr',
		'asr'          => 'Asr',
		'maghrib'      => 'Maghrib',
		'isha'         => 'Isha',
		'jumuah'       => 'Jumuah',
		'khatib'       => 'Khatib',
		'khutbah'      => 'Khutbah',
		'jamaat'       => 'Jamaat',
		'announcements'=> 'Announcements',
		'no_notices'   => 'Welcome to our Masjid',
	],
	'bn' => [
		'azan'         => 'আযান',
		'iqamah'       => 'ইকামত',
		'next_prayer'  => 'পরবর্তী ওয়াক্ত',
		'fajr'         => 'ফজর',
		'sunrise'      => 'সূর্যোদয়',
		'dhuhr'        => 'যোহর',
		'asr'          => 'আসর',
		'maghrib'      => 'মাগরিব',
		'isha'         => 'এশা',
		'jumuah'       => 'জুমা',
		'khatib'       => 'খতিব',
		'khutbah'      => 'খুতবা',
		'jamaat'       => 'জামাত',
		'announcements'=> 'মসজিদ নোটিশ',
		'no_notices'   => 'মসজিদে আপনাকে স্বাগতম',
	],
	'ar' => [
		'azan'         => 'الأذان',
		'iqamah'       => 'الإقامة',
		'next_prayer'  => 'الصلاة التالية',
		'fajr'         => 'الفجر',
		'sunrise'      => 'الشروق',
		'dhuhr'        => 'الظهر',
		'asr'          => 'العصر',
		'maghrib'      => 'المغرب',
		'isha'         => 'العشاء',
		'jumuah'       => 'صلاة الجمعة',
		'khatib'       => 'الخطيب',
		'khutbah'      => 'الخطبة',
		'jamaat'       => 'الجماعة',
		'announcements'=> 'إعلانات المسجد',
		'no_notices'   => 'مرحبًا بكم في المسجد',
	],
];

$active_labels = $labels[ $lang ] ?? $labels['en'];

// Active notices
$notices = [];
if ( ! empty( $settings['modules']['announcements'] ) ) {
	$notices = ITMMS_Announcements::active( 10 );
}

// Jumuah settings
$jumuah = $settings['jumuah'] ?? [];
$sessions = $jumuah['sessions'] ?? [];

// Date formats
$date_format = 'en' === $lang ? 'l, d F Y' : ( 'bn' === $lang ? 'l, d F Y' : 'l, d F Y' );
$gregorian_date = date_i18n( $date_format );

// Hijri Date
$hijri_label = '';
try {
	$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
	$day = new DateTimeImmutable( 'now', $timezone );
	$hijri = ITMMS_Hijri::for_date( $day, (int) ( $settings['hijri_adjustment'] ?? 0 ), $lang );
	$hijri_label = $hijri['label'] ?? '';
} catch ( Exception $e ) {
	$hijri_label = '';
}

// Build URL for plugin assets
$assets_version = ITMMS_VERSION;
$css_url = ITMMS_PLUGIN_URL . 'public/assets/css/tv-display.css';
$js_url  = ITMMS_PLUGIN_URL . 'public/assets/js/tv-display.js';
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'ar' === $lang ? 'rtl' : 'ltr'; ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $masjid_name ); ?> — TV Display</title>
	
	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;700;800&display=swap" rel="stylesheet">
	
	<!-- Style -->
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>?ver=<?php echo esc_attr( $assets_version ); ?>">
</head>
<body class="itmms-tv itmms-tv--theme-<?php echo esc_attr( $theme ); ?> itmms-tv--size-<?php echo esc_attr( $font_size ); ?>">

	<!-- Background Pattern Overlay -->
	<div class="itmms-tv__bg"></div>

	<!-- Standalone Page Container -->
	<div class="itmms-tv__wrapper">
		
		<!-- HEADER -->
		<header class="itmms-tv__header">
			<div class="itmms-tv__logo-area">
				<?php if ( $logo_url ) : ?>
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Logo" class="itmms-tv__logo">
				<?php endif; ?>
				<div>
					<h1><?php echo esc_html( $masjid_name ); ?></h1>
					<p class="itmms-tv__subtitle"><?php echo esc_html( implode( ', ', array_filter( [ $settings['city'] ?? '', $settings['country'] ?? '' ] ) ) ); ?></p>
				</div>
			</div>
			
			<div class="itmms-tv__date-time">
				<div class="itmms-tv__time" id="itmms-tv-clock">00:00:00</div>
				<div class="itmms-tv__date">
					<span><?php echo esc_html( $gregorian_date ); ?></span>
					<?php if ( $hijri_label ) : ?>
						<span class="itmms-tv__hijri-dot"></span>
						<span><?php echo esc_html( $hijri_label ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</header>

		<!-- MAIN CONTENT SECTION -->
		<main class="itmms-tv__main">
			
			<!-- PRAYER TIMES TABLE -->
			<section class="itmms-tv__table-container">
				<div class="itmms-tv__table">
					<div class="itmms-tv__table-header">
						<span><?php echo esc_html( 'bn' === $lang ? 'ওয়াক্ত' : ( 'ar' === $lang ? 'الصلاة' : 'Prayer' ) ); ?></span>
						<span class="text-center"><?php echo esc_html( $active_labels['azan'] ); ?></span>
						<span class="text-right"><?php echo esc_html( $active_labels['iqamah'] ); ?></span>
					</div>
					
					<?php foreach ( $prayers as $prayer ) : ?>
						<?php
						$p_key = (string) $prayer['key'];
						$p_name = $active_labels[ $p_key ] ?? $prayer['name'];
						$is_sunrise = 'sunrise' === $p_key;
						?>
						<div class="itmms-tv__table-row <?php echo ! empty( $prayer['current'] ) ? 'is-current' : ''; ?> <?php echo $is_sunrise ? 'is-sunrise' : ''; ?>" data-prayer-row="<?php echo esc_attr( $p_key ); ?>">
							<span class="itmms-tv__prayer-name">
								<?php echo esc_html( $p_name ); ?>
							</span>
							<span class="itmms-tv__time-azan text-center">
								<?php echo esc_html( $prayer['time'] ); ?>
							</span>
							<span class="itmms-tv__time-iqamah text-right">
								<?php
								if ( $is_sunrise ) {
									echo '-';
								} else {
									echo esc_html( $prayer['iqamah'] ?: $prayer['time'] );
								}
								?>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- RIGHT PANEL (COUNTDOWN & JUMUAH INFO) -->
			<aside class="itmms-tv__sidebar">
				
				<!-- COUNTDOWN BOX -->
				<div class="itmms-tv__countdown-box" id="itmms-tv-countdown-box">
					<span class="itmms-tv__countdown-label" id="itmms-tv-countdown-label">Time to Jamaat</span>
					<strong class="itmms-tv__next-name" id="itmms-tv-next-name">-</strong>
					<div class="itmms-tv__countdown-timer" id="itmms-tv-countdown">00:00:00</div>
				</div>

				<!-- JUMUAH DETAILS BOX -->
				<?php if ( ! empty( $jumuah['enabled'] ) && ! empty( $sessions ) ) : ?>
					<div class="itmms-tv__jumuah-box">
						<header class="itmms-tv__jumuah-header">
							<span><?php echo esc_html( $active_labels['jumuah'] ); ?></span>
						</header>
						<div class="itmms-tv__jumuah-sessions">
							<?php foreach ( $sessions as $index => $session ) : ?>
								<?php if ( ! empty( $session['jamaat_time'] ) ) : ?>
									<div class="itmms-tv__jumuah-session">
										<div class="itmms-tv__jumuah-label">
											<?php
											/* translators: %d: session number index */
											echo esc_html( $session['label'] ?: sprintf( __( 'Jumuah %d', 'masjidos' ), $index + 1 ) );
											?>
										</div>
										<div class="itmms-tv__jumuah-times">
											<?php if ( ! empty( $session['khutbah_time'] ) ) : ?>
												<span><?php echo esc_html( $active_labels['khutbah'] ); ?>: <b><?php echo esc_html( $session['khutbah_time'] ); ?></b></span>
											<?php endif; ?>
											<span><?php echo esc_html( $active_labels['jamaat'] ); ?>: <b><?php echo esc_html( $session['jamaat_time'] ); ?></b></span>
										</div>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				
			</aside>
		</main>

		<!-- ANNOUNCEMENT BANNER TICKER -->
		<footer class="itmms-tv__footer">
			<div class="itmms-tv__ticker-label">
				<?php echo esc_html( $active_labels['announcements'] ); ?>
			</div>
			<div class="itmms-tv__ticker-content" id="itmms-tv-ticker" data-speed="<?php echo esc_attr( $settings['tv_announcement_speed'] ?? 7 ); ?>">
				<div class="itmms-tv__ticker-track" id="itmms-tv-ticker-track">
					<?php if ( ! empty( $notices ) ) : ?>
						<?php foreach ( $notices as $notice ) : ?>
							<div class="itmms-tv__ticker-item">
								<span class="itmms-tv__ticker-bullet"></span>
								<?php echo esc_html( $notice['title'] ); ?>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="itmms-tv__ticker-item">
							<span class="itmms-tv__ticker-bullet"></span>
							<?php echo esc_html( $active_labels['no_notices'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</footer>
		
	</div>

	<!-- Localized JS Data payload -->
	<script id="itmms-tv-data" type="application/json">
		<?php echo wp_json_encode( [
			'prayers' => $prayers,
			'lang'    => $lang,
			'labels'  => [
				'azan'       => 'bn' === $lang ? 'আযানের সময়' : ( 'ar' === $lang ? 'وقت الأذان' : 'Time to Azan' ),
				'jamaat'     => 'bn' === $lang ? 'জামাতের সময়' : ( 'ar' === $lang ? 'وقت الإقامة' : 'Time to Jamaat' ),
				'fajr'       => $active_labels['fajr'],
				'sunrise'    => $active_labels['sunrise'],
				'dhuhr'      => $active_labels['dhuhr'],
				'asr'        => $active_labels['asr'],
				'maghrib'    => $active_labels['maghrib'],
				'isha'       => $active_labels['isha'],
			]
		] ); ?>
	</script>

	<!-- Script -->
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script src="<?php echo esc_url( $js_url ); ?>?ver=<?php echo esc_attr( $assets_version ); ?>"></script>
</body>
</html>
