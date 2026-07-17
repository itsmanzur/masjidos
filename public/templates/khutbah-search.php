<?php
/**
 * Template: public khutbah search widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

$itmms_labels = [
	'placeholder' => ( 'bn' === $language ) ? 'খুতবা খুঁজুন...' : ( ( 'ar' === $language ) ? 'ابحث عن الخطبة...' : __( 'Search khutbahs…', 'masjidos' ) ),
	'search'      => ( 'bn' === $language ) ? 'অনুসন্ধান' : ( ( 'ar' === $language ) ? 'بحث' : __( 'Search', 'masjidos' ) ),
	'empty'       => ( 'bn' === $language ) ? 'কোনো ফলাফল নেই।' : ( ( 'ar' === $language ) ? 'لا توجد نتائج.' : __( 'No results.', 'masjidos' ) ),
	'hint'        => ( 'bn' === $language ) ? 'বিষয় বা খতিবের নাম লিখে খুঁজুন।' : ( ( 'ar' === $language ) ? 'ابحث بالموضوع أو اسم الخطيب.' : __( 'Enter a topic or khatib name to search.', 'masjidos' ) ),
];
?>
<section class="itmms-public-minbar itmms-public-minbar--search itmms-public-minbar--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-minbar__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<form method="get" action="" class="itmms-public-minbar__form">
		<input type="search" name="itmms_khutbah_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( $itmms_labels['placeholder'] ); ?>">
		<button type="submit" class="itmms-btn itmms-btn-primary"><?php echo esc_html( $itmms_labels['search'] ); ?></button>
	</form>
	<?php if ( '' === $search ) : ?>
		<p class="itmms-public-minbar__hint"><?php echo esc_html( $itmms_labels['hint'] ); ?></p>
	<?php elseif ( empty( $khutbahs ) ) : ?>
		<p class="itmms-public-minbar__empty"><?php echo esc_html( $itmms_labels['empty'] ); ?></p>
	<?php else : ?>
		<ul class="itmms-public-minbar__list">
			<?php foreach ( $khutbahs as $itmms_row ) : ?>
				<li>
					<strong><?php echo esc_html( (string) ( $itmms_row['topic'] ?? '' ) ); ?></strong>
					<span><?php echo esc_html( (string) ( $itmms_row['khatib'] ?? '' ) ); ?></span>
					<span><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( (string) ( $itmms_row['date'] ?? '' ) ) ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>
