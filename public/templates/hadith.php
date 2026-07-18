<?php
/**
 * Template for public Hadith of the Day widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_hadith_labels = [
	'eyebrow' => ( 'bn' === $language ) ? 'হাদিস' : ( ( 'ar' === $language ) ? 'حديث' : __( 'Hadith', 'masjidos' ) ),
	'share'   => ( 'bn' === $language ) ? 'শেয়ার করুন' : ( ( 'ar' === $language ) ? 'مشاركة' : __( 'Share', 'masjidos' ) ),
	'copied'  => ( 'bn' === $language ) ? 'কপি হয়েছে' : ( ( 'ar' === $language ) ? 'تم النسخ' : __( 'Copied', 'masjidos' ) ),
];

$itmms_translation = $hadith['en'] ?? '';
if ( 'bn' === $language ) {
	$itmms_translation = $hadith['bn'] ?? $itmms_translation;
}

$itmms_share_text = trim(
	(string) ( $hadith['ar'] ?? '' ) . "\n\n" .
	(string) $itmms_translation . "\n\n" .
	'(' . (string) ( $hadith['ref'] ?? '' ) . ')'
);
?>
<section class="itmms-public-hadith itmms-public-hadith--<?php echo esc_attr( $design ); ?> itmms-public-hadith--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-hadith__header">
		<div>
			<span class="itmms-public-hadith__eyebrow"><?php echo esc_html( $itmms_hadith_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<?php if ( ! empty( $hadith['ref'] ) ) : ?>
			<b><?php echo esc_html( (string) $hadith['ref'] ); ?></b>
		<?php endif; ?>
	</header>
	<div class="itmms-public-hadith__content">
		<p class="itmms-public-hadith__arabic" dir="rtl" lang="ar"><?php echo esc_html( (string) ( $hadith['ar'] ?? '' ) ); ?></p>
		<?php if ( $itmms_translation ) : ?>
			<p class="itmms-public-hadith__translation"><?php echo esc_html( (string) $itmms_translation ); ?></p>
		<?php endif; ?>
		<?php if ( $show_share ) : ?>
			<div class="itmms-public-hadith__actions">
				<button type="button" class="itmms-public-hadith__share" data-itmms-education-share data-itmms-share-text="<?php echo esc_attr( $itmms_share_text ); ?>" data-itmms-share-success="<?php echo esc_attr( $itmms_hadith_labels['copied'] ); ?>">
					<?php echo esc_html( $itmms_hadith_labels['share'] ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</section>
