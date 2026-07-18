<?php
/**
 * Template: upcoming khutbah topics.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'eyebrow'  => ( 'bn' === $language ) ? 'মিনবার' : ( ( 'ar' === $language ) ? 'المنبر' : __( 'Minbar', 'masjidos' ) ),
	'empty'    => ( 'bn' === $language ) ? 'আসন্ন খুতবার তথ্য নেই।' : ( ( 'ar' === $language ) ? 'لا توجد خطب قادمة.' : __( 'No upcoming khutbahs listed.', 'masjidos' ) ),
	'khatib'   => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
	'planned'  => ( 'bn' === $language ) ? 'পরিকল্পিত' : ( ( 'ar' === $language ) ? 'مخطط' : __( 'Planned', 'masjidos' ) ),
	'scheduled'=> ( 'bn' === $language ) ? 'নির্ধারিত' : ( ( 'ar' === $language ) ? 'مجدول' : __( 'Scheduled', 'masjidos' ) ),
];
$itmms_masjid = (string) ( ITMMS_Settings::get_all()['masjid_name'] ?? get_bloginfo( 'name' ) );
?>
<section class="itmms-public-minbar itmms-public-minbar--upcoming itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>" dir="<?php echo 'ar' === $language ? 'rtl' : 'ltr'; ?>">
	<header class="itmms-public-minbar__header">
		<div>
			<span class="itmms-public-minbar__eyebrow"><?php echo esc_html( $itmms_masjid ?: $itmms_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
	</header>
	<?php if ( empty( $items ) ) : ?>
		<div class="itmms-public-minbar__empty-state">
			<p><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
		</div>
	<?php else : ?>
		<ul class="itmms-public-minbar__list">
			<?php foreach ( $items as $itmms_row ) : ?>
				<?php
				$itmms_date_raw = (string) ( $itmms_row['scheduled_date'] ?? '' );
				$itmms_date_ts  = $itmms_date_raw ? strtotime( $itmms_date_raw ) : false;
				$itmms_date     = $itmms_date_ts ? ITMMS_Hijri::format_label( date_i18n( get_option( 'date_format' ), $itmms_date_ts ), $language ) : '';
				$itmms_status   = (string) ( $itmms_row['status'] ?? 'scheduled' );
				$itmms_is_plan  = ( 'planned' === $itmms_status );
				?>
				<li class="itmms-public-minbar__list-item<?php echo $itmms_is_plan ? ' is-planned' : ''; ?>">
					<div class="itmms-public-minbar__list-main">
						<strong><?php echo esc_html( (string) ( $itmms_row['topic'] ?? '' ) ); ?></strong>
						<?php if ( $itmms_date ) : ?>
							<time datetime="<?php echo esc_attr( $itmms_date_raw ); ?>"><?php echo esc_html( $itmms_date ); ?></time>
						<?php endif; ?>
					</div>
					<div class="itmms-public-minbar__list-meta">
						<?php if ( ! empty( $itmms_row['khatib_name'] ) ) : ?>
							<span><?php echo esc_html( $itmms_labels['khatib'] . ': ' . (string) $itmms_row['khatib_name'] ); ?></span>
						<?php endif; ?>
						<span class="itmms-public-minbar__badge"><?php echo esc_html( $itmms_is_plan ? $itmms_labels['planned'] : $itmms_labels['scheduled'] ); ?></span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
