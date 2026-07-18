<?php
/**
 * Template: this week's khatib.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'eyebrow' => ( 'bn' === $language ) ? 'এই সপ্তাহ' : ( ( 'ar' === $language ) ? 'هذا الأسبوع' : __( 'This week', 'masjidos' ) ),
	'khatib'  => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
	'topic'   => ( 'bn' === $language ) ? 'বিষয়' : ( ( 'ar' === $language ) ? 'الموضوع' : __( 'Topic', 'masjidos' ) ),
	'empty'   => ( 'bn' === $language ) ? 'এই সপ্তাহের খতিব এখনো নির্ধারিত হয়নি।' : ( ( 'ar' === $language ) ? 'لم يتم تحديد خطيب هذا الأسبوع بعد.' : __( 'This week\'s khatib is not scheduled yet.', 'masjidos' ) ),
	'tba'     => ( 'bn' === $language ) ? 'বিষয় নির্ধারিত হয়নি' : ( ( 'ar' === $language ) ? 'الموضوع غير محدد' : __( 'Topic TBA', 'masjidos' ) ),
	'friday'  => ( 'bn' === $language ) ? 'জুমার দিন' : ( ( 'ar' === $language ) ? 'يوم الجمعة' : __( 'Friday', 'masjidos' ) ),
];
$itmms_masjid      = (string) ( ITMMS_Settings::get_all()['masjid_name'] ?? get_bloginfo( 'name' ) );
$itmms_khatib_name = (string) ( $entry['khatib_name'] ?? '' );
$itmms_topic       = (string) ( ( $entry['topic'] ?? '' ) ?: $itmms_labels['tba'] );
$itmms_date_raw    = (string) ( $entry['scheduled_date'] ?? '' );
$itmms_date_ts     = $itmms_date_raw ? strtotime( $itmms_date_raw ) : false;
$itmms_date        = $itmms_date_ts ? ITMMS_Hijri::format_label( date_i18n( get_option( 'date_format' ), $itmms_date_ts ), $language ) : '';
$itmms_initials    = method_exists( $this, 'initials' ) ? $this->initials( $itmms_khatib_name ?: $itmms_labels['khatib'] ) : 'خ';
?>
<section class="itmms-public-minbar itmms-public-minbar--this-week itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>" dir="<?php echo 'ar' === $language ? 'rtl' : 'ltr'; ?>">
	<header class="itmms-public-minbar__header">
		<div>
			<span class="itmms-public-minbar__eyebrow"><?php echo esc_html( $itmms_masjid ?: $itmms_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
			<?php if ( $itmms_date ) : ?>
				<p><?php echo esc_html( $itmms_labels['friday'] ); ?> · <?php echo esc_html( $itmms_date ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php if ( empty( $entry ) ) : ?>
		<div class="itmms-public-minbar__empty-state">
			<p><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
		</div>
	<?php else : ?>
		<div class="itmms-public-minbar__card itmms-public-minbar__card--khatib">
			<?php if ( ! empty( $entry['khatib_photo'] ) ) : ?>
				<img class="itmms-public-minbar__photo" src="<?php echo esc_url( (string) $entry['khatib_photo'] ); ?>" alt="<?php echo esc_attr( $itmms_khatib_name ?: $itmms_labels['khatib'] ); ?>">
			<?php else : ?>
				<span class="itmms-public-minbar__avatar" aria-hidden="true"><?php echo esc_html( $itmms_initials ?: 'خ' ); ?></span>
			<?php endif; ?>
			<div class="itmms-public-minbar__card-body">
				<span class="itmms-public-minbar__field-label"><?php echo esc_html( $itmms_labels['khatib'] ); ?></span>
				<strong class="itmms-public-minbar__khatib-name"><?php echo esc_html( $itmms_khatib_name ?: '—' ); ?></strong>
				<span class="itmms-public-minbar__field-label"><?php echo esc_html( $itmms_labels['topic'] ); ?></span>
				<p class="itmms-public-minbar__topic"><?php echo esc_html( $itmms_topic ); ?></p>
				<?php if ( $itmms_date ) : ?>
					<time datetime="<?php echo esc_attr( $itmms_date_raw ); ?>"><?php echo esc_html( $itmms_date ); ?></time>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
