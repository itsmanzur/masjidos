<?php
/**
 * Template for public Quran Verse of the Day widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_verse_labels = [
	'share'   => ( 'bn' === $language ) ? 'শেয়ার করুন' : __( 'Share', 'masjidos' ),
	'tafsir'  => ( 'bn' === $language ) ? 'তাফসীর দেখুন' : __( 'View Tafsir', 'masjidos' ),
	'copied'  => ( 'bn' === $language ) ? 'কপি হয়েছে!' : __( 'Copied!', 'masjidos' ),
];

$itmms_tafsir_url = 'https://quran.com/search?q=' . rawurlencode( $verse['ref'] );
?>
<section class="itmms-public-verse itmms-public-verse--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-verse__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<div class="itmms-public-verse__content">
		<div class="itmms-public-verse__arabic"><?php echo esc_html( $verse['ar'] ); ?></div>
		<p class="itmms-public-verse__translation">
			<?php echo esc_html( 'bn' === $language ? $verse['bn'] : $verse['en'] ); ?>
		</p>
		<div class="itmms-public-verse__ref">
			<span><?php echo esc_html( $verse['ref'] ); ?></span>
		</div>
		<div class="itmms-public-verse__actions">
			<a href="<?php echo esc_url( $itmms_tafsir_url ); ?>" target="_blank" rel="noopener noreferrer" class="itmms-btn itmms-btn-ghost">
				<?php echo esc_html( $itmms_verse_labels['tafsir'] ); ?>
			</a>
			<button type="button" class="itmms-btn itmms-btn-ghost itmms-verse-share-btn" data-itmms-education-share data-itmms-share-text="<?php echo esc_attr( $verse['ar'] . ' - ' . ( 'bn' === $language ? $verse['bn'] : $verse['en'] ) . ' (' . $verse['ref'] . ')' ); ?>" data-itmms-share-success="<?php echo esc_attr( $itmms_verse_labels['copied'] ); ?>">
				<?php echo esc_html( $itmms_verse_labels['share'] ); ?>
			</button>
		</div>
	</div>
</section>
