<?php
/**
 * Template for Islamic Articles list widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="itmms-public-articles itmms-public-articles--lang-<?php echo esc_attr( $language ); ?>">
	<div class="itmms-public-articles__header">
		<h2><?php echo esc_html( (string) $atts['title'] ); ?></h2>
	</div>

	<?php if ( empty( $articles ) ) : ?>
		<p class="itmms-public-articles__empty"><?php echo esc_html( $labels['empty'] ); ?></p>
	<?php else : ?>
		<div class="itmms-public-articles__grid">
			<?php foreach ( $articles as $itmms_article ) : ?>
				<?php
				$itmms_article = is_array( $itmms_article ) ? $itmms_article : [];
				$itmms_cats = isset( $itmms_article['categories'] ) && is_array( $itmms_article['categories'] ) ? $itmms_article['categories'] : [];
				$itmms_cat_label = ! empty( $itmms_cats ) ? implode( ', ', $itmms_cats ) : $labels['uncat'];
				$itmms_author = isset( $itmms_article['author'] ) ? (string) $itmms_article['author'] : '';
				$itmms_source = isset( $itmms_article['source'] ) ? (string) $itmms_article['source'] : '';
				$itmms_lang = isset( $itmms_article['language'] ) ? (string) $itmms_article['language'] : 'en';
				$itmms_lang_label = $labels['lang_' . $itmms_lang] ?? strtoupper( $itmms_lang );
				?>
				<article class="itmms-public-articles__card itmms-public-articles__card--lang-<?php echo esc_attr( $itmms_lang ); ?>">
					<?php if ( ! empty( $itmms_article['image'] ) ) : ?>
						<a class="itmms-public-articles__thumb" href="<?php echo esc_url( (string) ( $itmms_article['url'] ?? '#' ) ); ?>">
							<img src="<?php echo esc_url( (string) $itmms_article['image'] ); ?>" alt="" loading="lazy" />
						</a>
					<?php endif; ?>
					<div class="itmms-public-articles__body">
						<div class="itmms-public-articles__meta-row">
							<span class="itmms-public-articles__cat"><?php echo esc_html( $itmms_cat_label ); ?></span>
							<span class="itmms-public-articles__lang"><?php echo esc_html( $itmms_lang_label ); ?></span>
						</div>
						<h3>
							<a href="<?php echo esc_url( (string) ( $itmms_article['url'] ?? '#' ) ); ?>">
								<?php echo esc_html( (string) ( $itmms_article['title'] ?? '' ) ); ?>
							</a>
						</h3>
						<?php if ( $itmms_author !== '' ) : ?>
							<p class="itmms-public-articles__author"><?php echo esc_html( $itmms_author ); ?></p>
						<?php endif; ?>
						<?php
						$itmms_takeaway = isset( $itmms_article['takeaway'] ) ? (string) $itmms_article['takeaway'] : '';
						if ( $itmms_takeaway !== '' ) :
							?>
							<p class="itmms-public-articles__takeaway"><?php echo esc_html( $itmms_takeaway ); ?></p>
						<?php elseif ( $show_excerpt && ! empty( $itmms_article['excerpt'] ) ) : ?>
							<p><?php echo esc_html( (string) $itmms_article['excerpt'] ); ?></p>
						<?php endif; ?>
						<?php if ( $itmms_source !== '' ) : ?>
							<p class="itmms-public-articles__source"><span><?php echo esc_html( $labels['source'] ); ?></span> <?php echo esc_html( $itmms_source ); ?></p>
						<?php endif; ?>
						<a class="itmms-public-articles__read" href="<?php echo esc_url( (string) ( $itmms_article['url'] ?? '#' ) ); ?>">
							<?php echo esc_html( $labels['read'] ); ?>
						</a>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
