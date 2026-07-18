<?php
/**
 * Template for 99 Names of Allah public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_names_count = count( $names );
$itmms_names_count_label = class_exists( 'ITMMS_Hijri' )
	? ITMMS_Hijri::number( (string) $itmms_names_count, $language )
	: (string) $itmms_names_count;
$itmms_names_eyebrow = ( 'bn' === $language ) ? 'আসমাউল হুসনা' : ( ( 'ar' === $language ) ? 'الأسماء الحسنى' : __( 'Asma ul Husna', 'masjidos' ) );
?>
<section class="itmms-public-names itmms-public-names--<?php echo esc_attr( $design ); ?> itmms-public-names--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-names__header">
		<div>
			<span class="itmms-public-names__eyebrow"><?php echo esc_html( $itmms_names_eyebrow ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<b><?php echo esc_html( $itmms_names_count_label ); ?></b>
	</header>
	<div class="itmms-public-names__grid">
		<?php foreach ( $names as $itmms_index => $itmms_name ) : ?>
			<?php
			$itmms_num = (string) ( $itmms_index + 1 );
			if ( class_exists( 'ITMMS_Hijri' ) ) {
				$itmms_num = ITMMS_Hijri::number( $itmms_num, $language );
			}
			?>
			<article class="itmms-public-names__item">
				<span class="itmms-public-names__number"><?php echo esc_html( $itmms_num ); ?></span>
				<p class="itmms-public-names__arabic" dir="rtl" lang="ar"><?php echo esc_html( (string) ( $itmms_name['ar'] ?? '' ) ); ?></p>
				<strong class="itmms-public-names__trans"><?php echo esc_html( (string) ( $itmms_name['trans'] ?? '' ) ); ?></strong>
				<span class="itmms-public-names__meaning">
					<?php echo esc_html( 'bn' === $language ? (string) ( $itmms_name['bn'] ?? '' ) : (string) ( $itmms_name['en'] ?? '' ) ); ?>
				</span>
			</article>
		<?php endforeach; ?>
	</div>
</section>
