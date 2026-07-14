<?php
/**
 * Template for public Hadith of the Day widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_hadith_labels = [
	'share'   => ( 'bn' === $language ) ? 'শেয়ার করুন' : __( 'Share', 'masjidos' ),
	'copied'  => ( 'bn' === $language ) ? 'কপি হয়েছে!' : __( 'Copied!', 'masjidos' ),
];
?>
<section class="itmms-public-hadith itmms-public-hadith--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-hadith__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<div class="itmms-public-hadith__content">
		<div class="itmms-public-hadith__arabic"><?php echo esc_html( $hadith['ar'] ); ?></div>
		<p class="itmms-public-hadith__translation">
			<?php echo esc_html( 'bn' === $language ? $hadith['bn'] : $hadith['en'] ); ?>
		</p>
		<div class="itmms-public-hadith__ref">
			<span><?php echo esc_html( $hadith['ref'] ); ?></span>
		</div>
		<div class="itmms-public-hadith__actions">
			<button type="button" class="itmms-btn itmms-btn-ghost itmms-hadith-share-btn" data-itmms-education-share data-itmms-share-text="<?php echo esc_attr( $hadith['ar'] . ' - ' . ( 'bn' === $language ? $hadith['bn'] : $hadith['en'] ) . ' (' . $hadith['ref'] . ')' ); ?>" data-itmms-share-success="<?php echo esc_attr( $itmms_hadith_labels['copied'] ); ?>">
				<?php echo esc_html( $itmms_hadith_labels['share'] ); ?>
			</button>
		</div>
	</div>
</section>
