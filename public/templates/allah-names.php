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
$itmms_search_placeholder = ( 'bn' === $language ) ? 'নাম বা অর্থ দিয়ে খুঁজুন...' : ( ( 'ar' === $language ) ? 'ابحث بالاسم أو المعنى...' : __( 'Filter by name or meaning…', 'masjidos' ) );
$itmms_initial_visible = 18;
$itmms_has_collapsible = $itmms_names_count > $itmms_initial_visible;
$itmms_expand_label = ( 'bn' === $language )
	? sprintf( 'সব %sটি নাম দেখুন', $itmms_names_count_label )
	: ( ( 'ar' === $language )
		? sprintf( 'عرض جميع الأسماء (%s)', $itmms_names_count_label )
		/* translators: %s: total count of Allah names */
		: sprintf( __( 'Show all %s Names', 'masjidos' ), $itmms_names_count_label ) );
$itmms_collapse_label = ( 'bn' === $language )
	? 'সংক্ষিপ্ত করুন'
	: ( ( 'ar' === $language ) ? 'عرض أقل' : __( 'Show Less', 'masjidos' ) );
?>
<section class="itmms-public-names itmms-public-names--<?php echo esc_attr( $design ); ?> itmms-public-names--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-names__header">
		<div>
			<span class="itmms-public-names__eyebrow"><?php echo esc_html( $itmms_names_eyebrow ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
		<div class="itmms-public-names__header-tools">
			<?php if ( $itmms_names_count > 12 ) : ?>
				<input
					type="search"
					class="itmms-public-names__search"
					data-itmms-names-search
					placeholder="<?php echo esc_attr( $itmms_search_placeholder ); ?>"
					aria-label="<?php echo esc_attr( $itmms_search_placeholder ); ?>"
				>
			<?php endif; ?>
			<b><?php echo esc_html( $itmms_names_count_label ); ?></b>
		</div>
	</header>
	<div class="itmms-public-names__grid">
		<?php foreach ( $names as $itmms_index => $itmms_name ) : ?>
			<?php
			$itmms_num = (string) ( $itmms_index + 1 );
			if ( class_exists( 'ITMMS_Hijri' ) ) {
				$itmms_num = ITMMS_Hijri::number( $itmms_num, $language );
			}
			$itmms_meaning = 'bn' === $language ? (string) ( $itmms_name['bn'] ?? '' ) : (string) ( $itmms_name['en'] ?? '' );
			$itmms_search_blob = strtolower(
				trim(
					(string) ( $itmms_name['ar'] ?? '' ) . ' ' .
					(string) ( $itmms_name['trans'] ?? '' ) . ' ' .
					(string) ( $itmms_name['en'] ?? '' ) . ' ' .
					(string) ( $itmms_name['bn'] ?? '' ) . ' ' .
					(string) ( $itmms_index + 1 )
				)
			);
			$itmms_is_extra_item = $itmms_has_collapsible && $itmms_index >= $itmms_initial_visible;
			?>
			<article
				class="itmms-public-names__item"
				data-itmms-name-search-text="<?php echo esc_attr( $itmms_search_blob ); ?>"
				<?php echo $itmms_is_extra_item ? 'data-itmms-name-extra="1" hidden' : ''; ?>
			>
				<span class="itmms-public-names__number"><?php echo esc_html( $itmms_num ); ?></span>
				<p class="itmms-public-names__arabic" dir="rtl" lang="ar"><?php echo esc_html( (string) ( $itmms_name['ar'] ?? '' ) ); ?></p>
				<strong class="itmms-public-names__trans"><?php echo esc_html( (string) ( $itmms_name['trans'] ?? '' ) ); ?></strong>
				<span class="itmms-public-names__meaning">
					<?php echo esc_html( $itmms_meaning ); ?>
				</span>
			</article>
		<?php endforeach; ?>
	</div>
	<?php if ( $itmms_has_collapsible ) : ?>
		<div class="itmms-public-names__footer">
			<button
				type="button"
				class="itmms-public-names__toggle"
				data-itmms-names-toggle
				data-expand-label="<?php echo esc_attr( $itmms_expand_label ); ?>"
				data-collapse-label="<?php echo esc_attr( $itmms_collapse_label ); ?>"
				aria-expanded="false"
			>
				<?php echo esc_html( $itmms_expand_label ); ?>
			</button>
		</div>
	<?php endif; ?>
</section>
