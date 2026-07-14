<?php
/**
 * Template for public Jumuah Khutbah Archive widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_archive_labels = [
	'search_placeholder' => ( 'bn' === $language ) ? 'খুতবা খুঁজুন (বিষয়বস্তু, খতিব)...' : ( ( 'ar' === $language ) ? 'ابحث عن الخطبة...' : __( 'Search khutbahs (topic, khatib)...', 'masjidos' ) ),
	'search_btn'         => ( 'bn' === $language ) ? 'অনুসন্ধান' : ( ( 'ar' === $language ) ? 'بحث' : __( 'Search', 'masjidos' ) ),
	'date_label'         => ( 'bn' === $language ) ? 'তারিখ ফিল্টার' : ( ( 'ar' === $language ) ? 'تصفية بالتاريخ' : __( 'Filter by Date', 'masjidos' ) ),
	'khatib'             => ( 'bn' === $language ) ? 'খতিব' : ( ( 'ar' === $language ) ? 'الخطيب' : __( 'Khatib', 'masjidos' ) ),
	'language'           => ( 'bn' === $language ) ? 'ভাষা' : ( ( 'ar' === $language ) ? 'اللغة' : __( 'Language', 'masjidos' ) ),
	'listen'             => ( 'bn' === $language ) ? 'খুতবা শুনুন' : ( ( 'ar' === $language ) ? 'استمع إلى الخطبة' : __( 'Listen to Khutbah', 'masjidos' ) ),
	'no_results'         => ( 'bn' === $language ) ? 'কোনো খুতবা পাওয়া যায়নি।' : ( ( 'ar' === $language ) ? 'لم يتم العثور على خطب.' : __( 'No khutbahs found.', 'masjidos' ) ),
];
?>
<section class="itmms-public-archive itmms-public-archive--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-archive__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>

	<!-- SEARCH & FILTER FORM -->
	<form method="get" action="" class="itmms-public-archive__form">
		<div class="itmms-public-archive__search-group">
			<input type="text" name="itmms_khutbah_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $itmms_archive_labels['search_placeholder'] ); ?>">
			<input type="date" name="itmms_khutbah_date" value="<?php echo esc_attr( $date_filter ); ?>" aria-label="<?php echo esc_attr( $itmms_archive_labels['date_label'] ); ?>">
			<button type="submit" class="itmms-btn itmms-btn-primary"><?php echo esc_html( $itmms_archive_labels['search_btn'] ); ?></button>
		</div>
	</form>

	<!-- ARCHIVE LIST -->
	<div class="itmms-public-archive__list">
		<?php if ( empty( $khutbahs ) ) : ?>
			<div class="itmms-public-archive__empty">
				<p><?php echo esc_html( $itmms_archive_labels['no_results'] ); ?></p>
			</div>
		<?php else : ?>
			<?php foreach ( $khutbahs as $itmms_khutbah ) : ?>
				<?php
				$itmms_date_ts = strtotime( (string) $itmms_khutbah['date'] );
				$itmms_date_formatted = date_i18n( get_option( 'date_format' ), $itmms_date_ts );
				?>
				<article class="itmms-public-archive__item">
					<div class="itmms-public-archive__meta-top">
						<time datetime="<?php echo esc_attr( (string) $itmms_khutbah['date'] ); ?>"><?php echo esc_html( $itmms_date_formatted ); ?></time>
						<?php if ( ! empty( $itmms_khutbah['language'] ) ) : ?>
							<span class="itmms-public-archive__lang-badge"><?php echo esc_html( (string) $itmms_khutbah['language'] ); ?></span>
						<?php endif; ?>
					</div>
					<h3><?php echo esc_html( (string) $itmms_khutbah['topic'] ); ?></h3>
					<div class="itmms-public-archive__khatib-info">
						<strong><?php echo esc_html( $itmms_archive_labels['khatib'] ); ?>:</strong> <span><?php echo esc_html( (string) $itmms_khutbah['khatib'] ); ?></span>
					</div>
					<?php if ( ! empty( $itmms_khutbah['summary'] ) ) : ?>
						<p class="itmms-public-archive__summary"><?php echo nl2br( esc_html( (string) $itmms_khutbah['summary'] ) ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $itmms_khutbah['audio_url'] ) ) : ?>
						<div class="itmms-public-archive__audio-player">
							<audio controls src="<?php echo esc_url( $itmms_khutbah['audio_url'] ); ?>"></audio>
						</div>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</section>
