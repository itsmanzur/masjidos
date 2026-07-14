<?php
/**
 * Template for 99 Names of Allah public widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<section class="itmms-public-names itmms-public-names--lang-<?php echo esc_attr( $language ); ?>">
	<header class="itmms-public-names__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</header>
	<div class="itmms-public-names__grid">
		<?php foreach ( $names as $index => $name ) : ?>
			<div class="itmms-public-names__item">
				<div class="itmms-public-names__number"><?php echo esc_html( $index + 1 ); ?></div>
				<div class="itmms-public-names__arabic"><?php echo esc_html( $name['ar'] ); ?></div>
				<div class="itmms-public-names__trans"><?php echo esc_html( $name['trans'] ); ?></div>
				<div class="itmms-public-names__meaning">
					<?php echo esc_html( 'bn' === $language ? $name['bn'] : $name['en'] ); ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
