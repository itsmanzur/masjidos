<?php
/**
 * Template: upcoming khutbah topics.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'empty'  => ( 'bn' === $language ) ? 'আসন্ন খুতবার তথ্য নেই।' : ( ( 'ar' === $language ) ? 'لا توجد خطب قادمة.' : __( 'No upcoming khutbahs listed.', 'masjidos' ) ),
	'khatib' => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
];
?>
<section class="itmms-public-minbar itmms-public-minbar--upcoming itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-minbar__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<?php if ( empty( $items ) ) : ?>
		<p class="itmms-public-minbar__empty"><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
	<?php else : ?>
		<ul class="itmms-public-minbar__list">
			<?php foreach ( $items as $itmms_row ) : ?>
				<li>
					<strong><?php echo esc_html( (string) ( $itmms_row['topic'] ?? '' ) ); ?></strong>
					<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) ( $itmms_row['scheduled_date'] ?? '' ) ) ) ); ?></span>
					<?php if ( ! empty( $itmms_row['khatib_name'] ) ) : ?>
						<span><?php echo esc_html( $itmms_labels['khatib'] . ': ' . (string) $itmms_row['khatib_name'] ); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
