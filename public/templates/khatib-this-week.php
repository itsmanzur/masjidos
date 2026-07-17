<?php
/**
 * Template: this week's khatib.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'khatib' => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
	'topic'  => ( 'bn' === $language ) ? 'বিষয়' : ( ( 'ar' === $language ) ? 'الموضوع' : __( 'Topic', 'masjidos' ) ),
	'empty'  => ( 'bn' === $language ) ? 'এই সপ্তাহের খতিব এখনো নির্ধারিত হয়নি।' : ( ( 'ar' === $language ) ? 'لم يتم تحديد خطيب هذا الأسبوع بعد.' : __( 'This week\'s khatib is not scheduled yet.', 'masjidos' ) ),
	'tba'    => __( 'Topic TBA', 'masjidos' ),
];
?>
<section class="itmms-public-minbar itmms-public-minbar--this-week itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-minbar__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<?php if ( empty( $entry ) ) : ?>
		<p class="itmms-public-minbar__empty"><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
	<?php else : ?>
		<div class="itmms-public-minbar__card itmms-public-minbar__card--khatib">
			<?php if ( ! empty( $entry['khatib_photo'] ) ) : ?>
				<img class="itmms-public-minbar__photo" src="<?php echo esc_url( (string) $entry['khatib_photo'] ); ?>" alt="">
			<?php endif; ?>
			<div>
				<p><strong><?php echo esc_html( $itmms_labels['khatib'] ); ?>:</strong> <?php echo esc_html( (string) ( $entry['khatib_name'] ?? '' ) ); ?></p>
				<p><strong><?php echo esc_html( $itmms_labels['topic'] ); ?>:</strong> <?php echo esc_html( (string) ( $entry['topic'] ?: $itmms_labels['tba'] ) ); ?></p>
				<?php if ( ! empty( $entry['scheduled_date'] ) ) : ?>
					<p><time datetime="<?php echo esc_attr( (string) $entry['scheduled_date'] ); ?>"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) $entry['scheduled_date'] ) ) ); ?></time></p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
