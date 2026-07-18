<?php
/**
 * Template for public Jumuah Khutbah Archive widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_archive_labels = [
	'eyebrow'             => ( 'bn' === $language ) ? 'খুতবা আর্কাইভ' : ( ( 'ar' === $language ) ? 'أرشيف الخطب' : __( 'Khutbah archive', 'masjidos' ) ),
	'search_placeholder' => ( 'bn' === $language ) ? 'খুতবা খুঁজুন (বিষয়, খতিব)...' : ( ( 'ar' === $language ) ? 'ابحث عن الخطبة...' : __( 'Search khutbahs (topic, khatib)...', 'masjidos' ) ),
	'search_btn'         => ( 'bn' === $language ) ? 'অনুসন্ধান' : ( ( 'ar' === $language ) ? 'بحث' : __( 'Search', 'masjidos' ) ),
	'date_label'         => ( 'bn' === $language ) ? 'তারিখ' : ( ( 'ar' === $language ) ? 'التاريخ' : __( 'Date', 'masjidos' ) ),
	'category_label'     => ( 'bn' === $language ) ? 'ক্যাটাগরি' : ( ( 'ar' === $language ) ? 'التصنيف' : __( 'Category', 'masjidos' ) ),
	'all_categories'     => ( 'bn' === $language ) ? 'সব ক্যাটাগরি' : ( ( 'ar' === $language ) ? 'كل التصنيفات' : __( 'All categories', 'masjidos' ) ),
	'khatib'             => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
	'listen'             => ( 'bn' === $language ) ? 'খুতবা শুনুন' : ( ( 'ar' === $language ) ? 'استمع إلى الخطبة' : __( 'Listen to Khutbah', 'masjidos' ) ),
	'download_pdf'       => ( 'bn' === $language ) ? 'PDF ডাউনলোড' : ( ( 'ar' === $language ) ? 'تحميل PDF' : __( 'Download PDF', 'masjidos' ) ),
	'no_results'         => ( 'bn' === $language ) ? 'কোনো খুতবা পাওয়া যায়নি।' : ( ( 'ar' === $language ) ? 'لم يتم العثور على خطب.' : __( 'No khutbahs found.', 'masjidos' ) ),
];
if ( 'bn' === $language ) {
	$itmms_archive_labels['results'] = '%dটি ফলাফল';
} elseif ( 'ar' === $language ) {
	$itmms_archive_labels['results'] = '%d نتيجة';
} else {
	/* translators: %d: number of khutbah archive results */
	$itmms_archive_labels['results'] = __( '%d results', 'masjidos' );
}
$itmms_categories = ( isset( $categories ) && is_array( $categories ) ) ? $categories : [];
$itmms_masjid     = (string) ( ITMMS_Settings::get_all()['masjid_name'] ?? get_bloginfo( 'name' ) );
$itmms_count      = is_array( $khutbahs ) ? count( $khutbahs ) : 0;
$itmms_has_filter = ( '' !== (string) $search || '' !== (string) $date_filter || '' !== (string) $category );
$itmms_search_id  = 'itmms-khutbah-search-' . wp_unique_id();
?>
<section class="itmms-public-archive itmms-public-archive--lang-<?php echo esc_attr( $language ); ?>" dir="<?php echo 'ar' === $language ? 'rtl' : 'ltr'; ?>">
	<header class="itmms-public-archive__header">
		<div>
			<span class="itmms-public-archive__eyebrow"><?php echo esc_html( $itmms_masjid ?: $itmms_archive_labels['eyebrow'] ); ?></span>
			<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
			<?php if ( $itmms_has_filter || $itmms_count > 0 ) : ?>
				<p><?php echo esc_html( sprintf( $itmms_archive_labels['results'], $itmms_count ) ); ?></p>
			<?php endif; ?>
		</div>
	</header>

	<form method="get" action="" class="itmms-public-archive__form" role="search">
		<div class="itmms-public-archive__search-group">
			<label class="screen-reader-text" for="<?php echo esc_attr( $itmms_search_id ); ?>"><?php echo esc_html( $itmms_archive_labels['search_btn'] ); ?></label>
			<input id="<?php echo esc_attr( $itmms_search_id ); ?>" type="search" name="itmms_khutbah_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $itmms_archive_labels['search_placeholder'] ); ?>">
			<input type="date" name="itmms_khutbah_date" value="<?php echo esc_attr( $date_filter ); ?>" aria-label="<?php echo esc_attr( $itmms_archive_labels['date_label'] ); ?>">
			<?php if ( ! empty( $itmms_categories ) ) : ?>
				<select name="itmms_khutbah_category" aria-label="<?php echo esc_attr( $itmms_archive_labels['category_label'] ); ?>">
					<option value=""><?php echo esc_html( $itmms_archive_labels['all_categories'] ); ?></option>
					<?php foreach ( $itmms_categories as $itmms_cat_key => $itmms_cat_label ) : ?>
						<option value="<?php echo esc_attr( (string) $itmms_cat_key ); ?>" <?php selected( $category, (string) $itmms_cat_key ); ?>><?php echo esc_html( (string) $itmms_cat_label ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<button type="submit" class="itmms-public-archive__submit"><?php echo esc_html( $itmms_archive_labels['search_btn'] ); ?></button>
		</div>
	</form>

	<div class="itmms-public-archive__list">
		<?php if ( empty( $khutbahs ) ) : ?>
			<div class="itmms-public-archive__empty">
				<p><?php echo esc_html( $itmms_archive_labels['no_results'] ); ?></p>
			</div>
		<?php else : ?>
			<?php foreach ( $khutbahs as $itmms_khutbah ) : ?>
				<?php
				$itmms_date_ts        = strtotime( (string) $itmms_khutbah['date'] );
				$itmms_date_formatted = $itmms_date_ts ? ITMMS_Hijri::format_label( date_i18n( get_option( 'date_format' ), $itmms_date_ts ), $language ) : '';
				$itmms_cat            = (string) ( $itmms_khutbah['category'] ?? '' );
				?>
				<article class="itmms-public-archive__item">
					<div class="itmms-public-archive__meta-top">
						<?php if ( $itmms_date_formatted ) : ?>
							<time datetime="<?php echo esc_attr( (string) $itmms_khutbah['date'] ); ?>"><?php echo esc_html( $itmms_date_formatted ); ?></time>
						<?php endif; ?>
						<?php if ( $itmms_cat && isset( $categories[ $itmms_cat ] ) ) : ?>
							<span class="itmms-public-archive__badge"><?php echo esc_html( (string) $categories[ $itmms_cat ] ); ?></span>
						<?php elseif ( ! empty( $itmms_khutbah['language'] ) ) : ?>
							<span class="itmms-public-archive__badge"><?php echo esc_html( (string) $itmms_khutbah['language'] ); ?></span>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( (string) $itmms_khutbah['topic'] ); ?></h3>
					<div class="itmms-public-archive__khatib-info">
						<span class="itmms-public-archive__field-label"><?php echo esc_html( $itmms_archive_labels['khatib'] ); ?></span>
						<span><?php echo esc_html( (string) $itmms_khutbah['khatib'] ); ?></span>
					</div>
					<?php if ( ! empty( $itmms_khutbah['summary'] ) ) : ?>
						<p class="itmms-public-archive__summary"><?php echo nl2br( esc_html( (string) $itmms_khutbah['summary'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $itmms_khutbah['audio_url'] ) ) : ?>
						<div class="itmms-public-archive__audio-player">
							<span class="screen-reader-text"><?php echo esc_html( $itmms_archive_labels['listen'] ); ?></span>
							<audio controls preload="none" src="<?php echo esc_url( $itmms_khutbah['audio_url'] ); ?>"></audio>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $itmms_khutbah['doc_url'] ) ) : ?>
						<p class="itmms-public-archive__doc">
							<a href="<?php echo esc_url( $itmms_khutbah['doc_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $itmms_archive_labels['download_pdf'] ); ?></a>
						</p>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>
