<?php
/**
 * Template: public khutbah search widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'eyebrow'     => ( 'bn' === $language ) ? 'খুতবা খুঁজুন' : ( ( 'ar' === $language ) ? 'بحث الخطب' : __( 'Search khutbahs', 'masjidos' ) ),
	'placeholder' => ( 'bn' === $language ) ? 'খুতবা খুঁজুন...' : ( ( 'ar' === $language ) ? 'ابحث عن الخطبة...' : __( 'Search khutbahs…', 'masjidos' ) ),
	'search'      => ( 'bn' === $language ) ? 'অনুসন্ধান' : ( ( 'ar' === $language ) ? 'بحث' : __( 'Search', 'masjidos' ) ),
	'empty'       => ( 'bn' === $language ) ? 'কোনো ফলাফল নেই।' : ( ( 'ar' === $language ) ? 'لا توجد نتائج.' : __( 'No results.', 'masjidos' ) ),
	'hint'        => ( 'bn' === $language ) ? 'বিষয় বা খতিবের নাম লিখে খুঁজুন।' : ( ( 'ar' === $language ) ? 'ابحث بالموضوع أو اسم الخطيب.' : __( 'Enter a topic or khatib name to search.', 'masjidos' ) ),
	'khatib'      => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
];
if ( 'bn' === $language ) {
	$itmms_labels['results'] = '%dটি ফলাফল';
} elseif ( 'ar' === $language ) {
	$itmms_labels['results'] = '%d نتيجة';
} else {
	/* translators: %d: number of khutbah search results */
	$itmms_labels['results'] = __( '%d results', 'masjidos' );
}
$itmms_masjid = (string) ( ITMMS_Settings::get_all()['masjid_name'] ?? get_bloginfo( 'name' ) );
$itmms_count  = is_array( $khutbahs ) ? count( $khutbahs ) : 0;
$itmms_search_id = 'itmms-minbar-search-' . wp_unique_id();
?>
<section class="itmms-public-minbar itmms-public-minbar--search itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>" dir="<?php echo 'ar' === $language ? 'rtl' : 'ltr'; ?>">
	<header class="itmms-public-minbar__header">
		<div>
			<span class="itmms-public-minbar__eyebrow"><?php echo esc_html( $itmms_masjid ?: $itmms_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
		</div>
	</header>
	<form method="get" action="" class="itmms-public-minbar__form" role="search">
		<label class="screen-reader-text" for="<?php echo esc_attr( $itmms_search_id ); ?>"><?php echo esc_html( $itmms_labels['search'] ); ?></label>
		<input id="<?php echo esc_attr( $itmms_search_id ); ?>" type="search" name="itmms_khutbah_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $itmms_labels['placeholder'] ); ?>">
		<button type="submit" class="itmms-public-minbar__submit"><?php echo esc_html( $itmms_labels['search'] ); ?></button>
	</form>
	<?php if ( '' === $search ) : ?>
		<p class="itmms-public-minbar__hint"><?php echo esc_html( $itmms_labels['hint'] ); ?></p>
	<?php elseif ( empty( $khutbahs ) ) : ?>
		<div class="itmms-public-minbar__empty-state">
			<p><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
		</div>
	<?php else : ?>
		<p class="itmms-public-minbar__count"><?php echo esc_html( sprintf( $itmms_labels['results'], $itmms_count ) ); ?></p>
		<ul class="itmms-public-minbar__list">
			<?php foreach ( $khutbahs as $itmms_row ) : ?>
				<?php
				$itmms_date_raw = (string) ( $itmms_row['date'] ?? '' );
				$itmms_date_ts  = $itmms_date_raw ? strtotime( $itmms_date_raw ) : false;
				$itmms_date     = $itmms_date_ts ? ITMMS_Hijri::format_label( date_i18n( get_option( 'date_format' ), $itmms_date_ts ), $language ) : '';
				?>
				<li class="itmms-public-minbar__list-item">
					<div class="itmms-public-minbar__list-main">
						<strong><?php echo esc_html( (string) ( $itmms_row['topic'] ?? '' ) ); ?></strong>
						<?php if ( $itmms_date ) : ?>
							<time datetime="<?php echo esc_attr( $itmms_date_raw ); ?>"><?php echo esc_html( $itmms_date ); ?></time>
						<?php endif; ?>
					</div>
					<?php if ( ! empty( $itmms_row['khatib'] ) ) : ?>
						<div class="itmms-public-minbar__list-meta">
							<span><?php echo esc_html( $itmms_labels['khatib'] . ': ' . (string) $itmms_row['khatib'] ); ?></span>
						</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
