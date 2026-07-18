<?php
/**
 * Standalone template for TV / Fullscreen Display.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$settings = ITMMS_Settings::get_all();
$data     = ITMMS_Prayer_Times::today();
$meta     = $data['meta'] ?? [];
$prayers  = $data['prayers'] ?? [];
$next     = $data['next_prayer'] ?? [];

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$theme     = sanitize_key( $_GET['theme'] ?? $settings['tv_theme'] ?? 'dark' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$lang      = sanitize_key( $_GET['lang'] ?? $settings['ui_language'] ?? 'en' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$font_size = sanitize_key( $_GET['font_size'] ?? $settings['tv_font_size'] ?? 'normal' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$layout    = sanitize_key( $_GET['layout'] ?? $settings['tv_layout'] ?? 'classic' );

$logo_url    = esc_url( $settings['tv_logo_url'] ?: '' );
$masjid_name = esc_html( $settings['masjid_name'] ?: get_bloginfo( 'name' ) );
$slides_on   = ! empty( $settings['tv_slides'] );
$slide_interval = max( 6, min( 60, (int) ( $settings['tv_slide_interval'] ?? 12 ) ) );
$dim_enabled = ! empty( $settings['tv_dim_enabled'] );
$dim_start   = (string) ( $settings['tv_dim_start'] ?? '23:00' );
$dim_end     = (string) ( $settings['tv_dim_end'] ?? '04:30' );
$clock_format = sanitize_key( (string) ( $settings['tv_clock_format'] ?? '24h' ) );
$alert_minutes = max( 1, min( 30, (int) ( $settings['tv_alert_minutes'] ?? 10 ) ) );
$quiet_enabled = ! array_key_exists( 'tv_quiet_enabled', $settings ) || ! empty( $settings['tv_quiet_enabled'] );
$quiet_minutes = max( 5, min( 45, (int) ( $settings['tv_quiet_minutes'] ?? 15 ) ) );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$quiet_param = isset( $_GET['quiet'] ) ? sanitize_key( (string) wp_unslash( $_GET['quiet'] ) ) : '';
if ( in_array( $quiet_param, [ '0', 'off', 'false', 'no' ], true ) ) {
	$quiet_enabled = false;
} elseif ( in_array( $quiet_param, [ '1', 'on', 'true', 'yes' ], true ) ) {
	$quiet_enabled = true;
}
if ( ! in_array( $clock_format, [ '24h', '12h' ], true ) ) {
	$clock_format = '24h';
}

if ( ! in_array( $theme, [ 'dark', 'light', 'green' ], true ) ) {
	$theme = 'dark';
}
if ( ! in_array( $lang, [ 'en', 'bn', 'ar' ], true ) ) {
	$lang = in_array( (string) ( $settings['ui_language'] ?? 'en' ), [ 'en', 'bn', 'ar' ], true )
		? (string) $settings['ui_language']
		: 'en';
}
if ( ! in_array( $font_size, [ 'small', 'normal', 'large', 'xlarge' ], true ) ) {
	$font_size = 'normal';
}
if ( ! in_array( $layout, [ 'classic', 'split', 'focus' ], true ) ) {
	$layout = 'classic';
}

$labels = [
	'en' => [
		'azan'          => 'Azan',
		'iqamah'        => 'Iqamah',
		'next_prayer'   => 'Next Prayer',
		'fajr'          => 'Fajr',
		'sunrise'       => 'Sunrise',
		'ishraq'        => 'Ishraq',
		'zawal'         => 'Zawal',
		'dhuhr'         => 'Dhuhr',
		'asr'           => 'Asr',
		'maghrib'       => 'Maghrib',
		'isha'          => 'Isha',
		'jumuah'        => 'Jumuah',
		'khatib'        => 'Khatib',
		'khutbah'       => 'Khutbah',
		'jamaat'        => 'Jamaat',
		'announcements' => 'Announcements',
		'no_notices'    => 'Welcome to our Masjid',
		'notices_slide' => 'Masjid Notices',
		'dim_label'     => 'Night mode',
		'next_iqamah'   => 'Next Iqamah',
		'alert'         => 'Almost time',
		'quiet_eyebrow' => 'Salah mode',
		'quiet_title'   => 'Please be quiet',
		'quiet_message' => 'Prayer in progress',
		'time_to_azan'  => 'Time to Azan',
		'time_to_jamaat'=> 'Time to Jamaat',
		'prayer_col'    => 'Prayer',
		'tv_title'      => 'TV Display',
		'jumuah_n'      => 'Jumuah %d',
	],
	'bn' => [
		'azan'          => 'আযান',
		'iqamah'        => 'ইকামত',
		'next_prayer'   => 'পরবর্তী ওয়াক্ত',
		'fajr'          => 'ফজর',
		'sunrise'       => 'সূর্যোদয়',
		'ishraq'        => 'ইশরাক',
		'zawal'         => 'যাওয়াল',
		'dhuhr'         => 'যোহর',
		'asr'           => 'আসর',
		'maghrib'       => 'মাগরিব',
		'isha'          => 'এশা',
		'jumuah'        => 'জুমা',
		'khatib'        => 'খতিব',
		'khutbah'       => 'খুতবা',
		'jamaat'        => 'জামাত',
		'announcements' => 'মসজিদ নোটিশ',
		'no_notices'    => 'মসজিদে আপনাকে স্বাগতম',
		'notices_slide' => 'মসজিদ নোটিশ',
		'dim_label'     => 'নাইট মোড',
		'next_iqamah'   => 'পরবর্তী ইকামত',
		'alert'         => 'সময় ঘনিয়ে এসেছে',
		'quiet_eyebrow' => 'শান্ত মোড',
		'quiet_title'   => 'নীরব থাকুন',
		'quiet_message' => 'সালাত চলছে',
		'time_to_azan'  => 'আযানের সময়',
		'time_to_jamaat'=> 'জামাতের সময়',
		'prayer_col'    => 'ওয়াক্ত',
		'tv_title'      => 'টিভি ডিসপ্লে',
		'jumuah_n'      => 'জুমা %d',
	],
	'ar' => [
		'azan'          => 'الأذان',
		'iqamah'        => 'الإقامة',
		'next_prayer'   => 'الصلاة التالية',
		'fajr'          => 'الفجر',
		'sunrise'       => 'الشروق',
		'ishraq'        => 'الإشراق',
		'zawal'         => 'الزوال',
		'dhuhr'         => 'الظهر',
		'asr'           => 'العصر',
		'maghrib'       => 'المغرب',
		'isha'          => 'العشاء',
		'jumuah'        => 'صلاة الجمعة',
		'khatib'        => 'الخطيب',
		'khutbah'       => 'الخطبة',
		'jamaat'        => 'الجماعة',
		'announcements' => 'إعلانات المسجد',
		'no_notices'    => 'مرحبًا بكم في المسجد',
		'notices_slide' => 'إعلانات المسجد',
		'dim_label'     => 'وضع الليل',
		'next_iqamah'   => 'الإقامة التالية',
		'alert'         => 'اقترب الوقت',
		'quiet_eyebrow' => 'وضع الصلاة',
		'quiet_title'   => 'يرجى الالتزام بالهدوء',
		'quiet_message' => 'الصلاة قائمة',
		'time_to_azan'  => 'وقت الأذان',
		'time_to_jamaat'=> 'وقت الإقامة',
		'prayer_col'    => 'الصلاة',
		'tv_title'      => 'شاشة التلفزيون',
		'jumuah_n'      => 'الجمعة %d',
	],
];

$active_labels = $labels[ $lang ] ?? $labels['en'];

$notices = [];
if ( ! empty( $settings['modules']['announcements'] ) ) {
	$notices = ITMMS_Announcements::active( 10 );
}

$jumuah   = $settings['jumuah'] ?? [];
$sessions = $jumuah['sessions'] ?? [];
$has_jumuah = ! empty( $jumuah['enabled'] ) && ! empty( $sessions );

$hijri_label = '';
$tv_now = null;
try {
	$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
	$tv_now   = new DateTimeImmutable( 'now', $timezone );
	$hijri    = ITMMS_Hijri::for_date( $tv_now, (int) ( $settings['hijri_adjustment'] ?? 0 ), $lang );
	$hijri_label = $hijri['label'] ?? '';
} catch ( Exception $e ) {
	$hijri_label = '';
	$tv_now = new DateTimeImmutable( 'now' );
}

$weekday_names = [
	'en' => [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ],
	'bn' => [ 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' ],
	'ar' => [ 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت' ],
];
$month_names = [
	'en' => [ 1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
	'bn' => [ 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' ],
	'ar' => [ 1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ],
];
$wd = $weekday_names[ $lang ] ?? $weekday_names['en'];
$mn = $month_names[ $lang ] ?? $month_names['en'];
$gregorian_date = sprintf(
	'%s, %s %s %s',
	$wd[ (int) $tv_now->format( 'w' ) ],
	ITMMS_Hijri::number( $tv_now->format( 'j' ), $lang ),
	$mn[ (int) $tv_now->format( 'n' ) ],
	ITMMS_Hijri::number( $tv_now->format( 'Y' ), $lang )
);

$assets_version = ITMMS_VERSION;
$fonts_css_url  = ITMMS_PLUGIN_URL . 'public/assets/css/tv-fonts.css';
$css_url        = ITMMS_PLUGIN_URL . 'public/assets/css/tv-display.css';
$js_url         = ITMMS_PLUGIN_URL . 'public/assets/js/tv-display.js';

$body_classes = [
	'itmms-tv',
	'itmms-tv--theme-' . $theme,
	'itmms-tv--size-' . $font_size,
	'itmms-tv--layout-' . $layout,
	'itmms-tv--lang-' . $lang,
];
if ( $slides_on ) {
	$body_classes[] = 'itmms-tv--slides';
}
?>
<!DOCTYPE html>
<html lang="<?php echo esc_attr( $lang ); ?>" dir="<?php echo 'ar' === $lang ? 'rtl' : 'ltr'; ?>">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $masjid_name ); ?> — <?php echo esc_html( $active_labels['tv_title'] ); ?></title>
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link rel="stylesheet" href="<?php echo esc_url( $fonts_css_url ); ?>?ver=<?php echo esc_attr( $assets_version ); ?>">
	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<link rel="stylesheet" href="<?php echo esc_url( $css_url ); ?>?ver=<?php echo esc_attr( $assets_version ); ?>">
	<?php
	/**
	 * Extra stylesheets for TV display (Pro may enqueue collections CSS URL).
	 *
	 * @param array<int,string>   $urls     Absolute stylesheet URLs.
	 * @param array<string,mixed> $settings Settings.
	 */
	$extra_css = apply_filters( 'masjidos_tv_extra_styles', [], $settings );
	if ( is_array( $extra_css ) ) {
		foreach ( $extra_css as $extra_url ) {
			$extra_url = (string) $extra_url;
			if ( '' === esc_url( $extra_url ) ) {
				continue;
			}
			// Standalone TV HTML document has no wp_head(); Pro CSS is injected as a link tag.
			echo '<link rel="stylesheet" href="' . esc_url( $extra_url ) . '?ver=' . esc_attr( $assets_version ) . '">' . "\n"; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		}
	}
	?>
</head>
<body class="<?php echo esc_attr( implode( ' ', $body_classes ) ); ?>">

	<div class="itmms-tv__bg"></div>
	<div class="itmms-tv__dim" id="itmms-tv-dim" aria-hidden="true"></div>
	<div class="itmms-tv__quiet" id="itmms-tv-quiet" hidden>
		<div class="itmms-tv__quiet-panel">
			<span class="itmms-tv__quiet-eyebrow" id="itmms-tv-quiet-eyebrow"><?php echo esc_html( $active_labels['quiet_eyebrow'] ); ?></span>
			<strong class="itmms-tv__quiet-title" id="itmms-tv-quiet-title"><?php echo esc_html( $active_labels['quiet_title'] ); ?></strong>
			<p class="itmms-tv__quiet-name" id="itmms-tv-quiet-name">—</p>
			<p class="itmms-tv__quiet-message" id="itmms-tv-quiet-message"><?php echo esc_html( $active_labels['quiet_message'] ); ?></p>
		</div>
	</div>

	<div class="itmms-tv__wrapper">
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

		<div class="itmms-tv__stage" id="itmms-tv-stage" data-slides="<?php echo $slides_on ? '1' : '0'; ?>" data-interval="<?php echo esc_attr( (string) $slide_interval ); ?>">

			<section class="itmms-tv__slide is-active" data-slide="board">
				<main class="itmms-tv__main">
					<section class="itmms-tv__table-container">
						<div class="itmms-tv__table">
							<div class="itmms-tv__table-header">
								<span><?php echo esc_html( $active_labels['prayer_col'] ); ?></span>
								<span class="text-center"><?php echo esc_html( $active_labels['azan'] ); ?></span>
								<span class="text-right"><?php echo esc_html( $active_labels['iqamah'] ); ?></span>
							</div>

							<?php foreach ( $prayers as $prayer ) : ?>
								<?php
								$p_key      = (string) $prayer['key'];
								$p_name     = $active_labels[ $p_key ] ?? $prayer['name'];
								$is_sunrise = 'sunrise' === $p_key;
								$is_extra   = in_array( $p_key, [ 'ishraq', 'zawal', 'sunrise' ], true ) || 'extra' === ( $prayer['kind'] ?? '' );
								?>
								<div class="itmms-tv__table-row <?php echo ! empty( $prayer['current'] ) ? 'is-current' : ''; ?> <?php echo $is_extra ? 'is-extra' : ''; ?> <?php echo $is_sunrise ? 'is-sunrise' : ''; ?> <?php echo 'ishraq' === $p_key ? 'is-ishraq' : ''; ?> <?php echo 'zawal' === $p_key ? 'is-zawal' : ''; ?>" data-prayer-row="<?php echo esc_attr( $p_key ); ?>">
									<span class="itmms-tv__prayer-name"><?php echo esc_html( $p_name ); ?></span>
									<span class="itmms-tv__time-azan text-center"><?php echo esc_html( ITMMS_Hijri::format_clock( (string) ( $prayer['time'] ?? '' ), $lang ) ); ?></span>
									<span class="itmms-tv__time-iqamah text-right">
										<?php
										if ( $is_extra ) {
											echo '—';
										} else {
											$itmms_iqamah_raw = ! empty( $prayer['iqamah'] ) ? (string) $prayer['iqamah'] : (string) ( $prayer['time'] ?? '' );
											echo esc_html( ITMMS_Hijri::format_clock( $itmms_iqamah_raw, $lang ) );
										}
										?>
									</span>
								</div>
							<?php endforeach; ?>
						</div>
					</section>

					<aside class="itmms-tv__sidebar">
				<div class="itmms-tv__countdown-box" id="itmms-tv-countdown-box">
							<span class="itmms-tv__countdown-label" id="itmms-tv-countdown-label"><?php echo esc_html( $active_labels['time_to_jamaat'] ); ?></span>
							<strong class="itmms-tv__next-name" id="itmms-tv-next-name">-</strong>
							<div class="itmms-tv__countdown-timer" id="itmms-tv-countdown"><?php echo esc_html( ITMMS_Hijri::number( '00:00:00', $lang ) ); ?></div>
							<span class="itmms-tv__alert-badge" id="itmms-tv-alert-badge" hidden><?php echo esc_html( $active_labels['alert'] ); ?></span>
						</div>

						<?php if ( $has_jumuah ) : ?>
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
													echo esc_html(
														! empty( $session['label'] )
															? (string) $session['label']
															: sprintf( $active_labels['jumuah_n'], (int) $index + 1 )
													);
													?>
												</div>
												<div class="itmms-tv__jumuah-times">
													<?php if ( ! empty( $session['khutbah_time'] ) ) : ?>
														<span><?php echo esc_html( $active_labels['khutbah'] ); ?>: <b><?php echo esc_html( ITMMS_Hijri::format_clock( (string) $session['khutbah_time'], $lang ) ); ?></b></span>
													<?php endif; ?>
													<span><?php echo esc_html( $active_labels['jamaat'] ); ?>: <b><?php echo esc_html( ITMMS_Hijri::format_clock( (string) $session['jamaat_time'], $lang ) ); ?></b></span>
												</div>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</aside>
				</main>
			</section>

			<?php if ( $slides_on ) : ?>
				<section class="itmms-tv__slide" data-slide="notices">
					<div class="itmms-tv__notices-slide">
						<div class="itmms-tv__slide-panel">
							<header class="itmms-tv__notices-head">
								<span><?php echo esc_html( $active_labels['notices_slide'] ); ?></span>
							</header>
							<div class="itmms-tv__notices-grid">
								<?php if ( ! empty( $notices ) ) : ?>
									<?php foreach ( array_slice( $notices, 0, 6 ) as $notice ) : ?>
										<article class="itmms-tv__notice-card">
											<strong><?php echo esc_html( (string) ( $notice['title'] ?? '' ) ); ?></strong>
											<?php if ( ! empty( $notice['content'] ) ) : ?>
												<p><?php echo esc_html( wp_html_excerpt( (string) $notice['content'], 180, '…' ) ); ?></p>
											<?php endif; ?>
										</article>
									<?php endforeach; ?>
								<?php else : ?>
									<article class="itmms-tv__notice-card itmms-tv__notice-card--welcome">
										<strong><?php echo esc_html( $active_labels['no_notices'] ); ?></strong>
									</article>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</section>

				<?php if ( $has_jumuah ) : ?>
					<section class="itmms-tv__slide" data-slide="jumuah">
						<div class="itmms-tv__jumuah-slide">
							<div class="itmms-tv__slide-panel itmms-tv__slide-panel--jumuah">
								<header class="itmms-tv__notices-head">
									<span><?php echo esc_html( $active_labels['jumuah'] ); ?></span>
								</header>
								<div class="itmms-tv__jumuah-slide-grid">
									<?php foreach ( $sessions as $index => $session ) : ?>
										<?php if ( empty( $session['jamaat_time'] ) ) : ?>
											<?php continue; ?>
										<?php endif; ?>
										<article class="itmms-tv__jumuah-slide-card">
											<strong>
												<?php
												echo esc_html(
													$session['label']
														? (string) $session['label']
														: sprintf( $active_labels['jumuah_n'], (int) $index + 1 )
												);
												?>
											</strong>
											<?php if ( ! empty( $session['khutbah_time'] ) ) : ?>
												<p><?php echo esc_html( $active_labels['khutbah'] ); ?>: <?php echo esc_html( ITMMS_Hijri::format_clock( (string) $session['khutbah_time'], $lang ) ); ?></p>
											<?php endif; ?>
											<p><?php echo esc_html( $active_labels['jamaat'] ); ?>: <?php echo esc_html( ITMMS_Hijri::format_clock( (string) $session['jamaat_time'], $lang ) ); ?></p>
										</article>
									<?php endforeach; ?>
									<?php if ( ! empty( $jumuah['khatib']['name'] ) ) : ?>
										<article class="itmms-tv__jumuah-slide-card">
											<strong><?php echo esc_html( $active_labels['khatib'] ); ?></strong>
											<p><?php echo esc_html( (string) $jumuah['khatib']['name'] ); ?></p>
											<?php if ( ! empty( $jumuah['topic'] ) ) : ?>
												<p><?php echo esc_html( (string) $jumuah['topic'] ); ?></p>
											<?php endif; ?>
										</article>
									<?php endif; ?>
								</div>
							</div>
						</div>
					</section>
				<?php endif; ?>

				<?php
				/**
				 * Extra TV slides (Pro collections, etc.).
				 *
				 * Each item: id (string), html (escaped markup string).
				 *
				 * @param array<int,array{id?:string,html?:string}> $slides   Extra slides.
				 * @param array<string,mixed>                       $settings Settings.
				 * @param array<string,string>                      $labels   Active labels.
				 */
				$extra_slides = apply_filters( 'masjidos_tv_extra_slides', [], $settings, $active_labels );
				if ( is_array( $extra_slides ) ) :
					foreach ( $extra_slides as $extra ) :
						if ( empty( $extra['html'] ) || ! is_string( $extra['html'] ) ) {
							continue;
						}
						$slide_id = sanitize_key( (string) ( $extra['id'] ?? 'extra' ) );
						?>
						<section class="itmms-tv__slide" data-slide="<?php echo esc_attr( $slide_id ); ?>">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pro builds escaped HTML.
							echo $extra['html'];
							?>
						</section>
						<?php
					endforeach;
				endif;
				?>
			<?php endif; ?>
		</div>

		<?php if ( $slides_on ) : ?>
			<div class="itmms-tv__slide-dots" id="itmms-tv-slide-dots" aria-hidden="true"></div>
		<?php endif; ?>

		<footer class="itmms-tv__footer">
			<div class="itmms-tv__ticker-label">
				<?php echo esc_html( $active_labels['announcements'] ); ?>
			</div>
			<div class="itmms-tv__ticker-content" id="itmms-tv-ticker" data-speed="<?php echo esc_attr( (string) ( $settings['tv_announcement_speed'] ?? 7 ) ); ?>">
				<div class="itmms-tv__ticker-track" id="itmms-tv-ticker-track">
					<?php if ( ! empty( $notices ) ) : ?>
						<?php foreach ( $notices as $notice ) : ?>
							<?php
							$ticker_title   = trim( (string) ( $notice['title'] ?? '' ) );
							$ticker_content = trim( preg_replace( '/\s+/', ' ', (string) ( $notice['content'] ?? '' ) ) );
							?>
							<div class="itmms-tv__ticker-item">
								<span class="itmms-tv__ticker-bullet"></span>
								<strong><?php echo esc_html( $ticker_title ); ?></strong>
								<?php if ( '' !== $ticker_content ) : ?>
									<span class="itmms-tv__ticker-sep">—</span>
									<span class="itmms-tv__ticker-body"><?php echo esc_html( wp_html_excerpt( $ticker_content, 160, '…' ) ); ?></span>
								<?php endif; ?>
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

	<script id="itmms-tv-data" type="application/json">
		<?php
		echo wp_json_encode(
			[
				'prayers' => $prayers,
				'lang'    => $lang,
				'clock'   => $clock_format,
				'alertMinutes' => $alert_minutes,
				'quiet'   => [
					'enabled' => $quiet_enabled,
					'minutes' => $quiet_minutes,
				],
				'labels'  => [
					'azan'          => $active_labels['time_to_azan'],
					'jamaat'        => $active_labels['time_to_jamaat'],
					'next_iqamah'   => $active_labels['next_iqamah'],
					'alert'         => $active_labels['alert'],
					'quiet_eyebrow' => $active_labels['quiet_eyebrow'],
					'quiet_title'   => $active_labels['quiet_title'],
					'quiet_message' => $active_labels['quiet_message'],
					'fajr'          => $active_labels['fajr'],
					'sunrise'       => $active_labels['sunrise'],
					'ishraq'        => $active_labels['ishraq'],
					'zawal'         => $active_labels['zawal'],
					'dhuhr'         => $active_labels['dhuhr'],
					'asr'           => $active_labels['asr'],
					'maghrib'       => $active_labels['maghrib'],
					'isha'          => $active_labels['isha'],
				],
				'dim'     => [
					'enabled' => $dim_enabled,
					'start'   => $dim_start,
					'end'     => $dim_end,
					'label'   => $active_labels['dim_label'],
				],
			]
		);
		?>
	</script>

	<?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script src="<?php echo esc_url( $js_url ); ?>?ver=<?php echo esc_attr( $assets_version ); ?>"></script>
</body>
</html>
