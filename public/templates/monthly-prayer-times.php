<?php
/**
 * Template for monthly prayer timetable widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="itmms-public-monthly itmms-public-monthly--<?php echo esc_attr( $design ); ?> itmms-public-monthly--lang-<?php echo esc_attr( $language ); ?>"<?php if ( $show_navigation ) : ?> data-itmms-monthly data-endpoint="<?php echo esc_url( rest_url( 'masjidos/v1/prayer-times/monthly' ) ); ?>" data-month="<?php echo esc_attr( (string) $month ); ?>" data-year="<?php echo esc_attr( (string) $year ); ?>" data-current-month="<?php echo esc_attr( $now->format( 'n' ) ); ?>" data-current-year="<?php echo esc_attr( $now->format( 'Y' ) ); ?>" data-design="<?php echo esc_attr( $design ); ?>" data-language="<?php echo esc_attr( $language ); ?>" data-iqamah="<?php echo esc_attr( $show_iqamah ? 'yes' : 'no' ); ?>" data-title="<?php echo esc_attr( (string) $atts['title'] ); ?>" data-error="<?php echo esc_attr( $labels['error'] ); ?>"<?php endif; ?>>
	<div class="itmms-public-monthly__header">
		<div>
			<span><?php echo esc_html( $meta['location'] ?? get_bloginfo( 'name' ) ); ?></span>
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p>
				<span><?php echo esc_html( (string) ( $data['label'] ?? '' ) ); ?></span>
				<?php if ( ! empty( $hijri_range_label ) ) : ?>
					<span><?php echo esc_html( $hijri_range_label ); ?></span>
				<?php endif; ?>
				<span><?php echo esc_html( (string) ( $data['timezone'] ?? '' ) ); ?></span>
			</p>
		</div>
		<?php if ( $show_navigation ) : ?>
			<div class="itmms-public-monthly__nav" aria-label="<?php echo esc_attr( $labels['navigation'] ); ?>">
				<button type="button" data-itmms-monthly-step="-1" aria-label="<?php echo esc_attr( $labels['previous'] ); ?>" title="<?php echo esc_attr( $labels['previous'] ); ?>">&#8249;</button>
				<label>
					<span class="screen-reader-text"><?php echo esc_html( $labels['month'] ); ?></span>
					<select data-itmms-monthly-month aria-label="<?php echo esc_attr( $labels['month'] ); ?>">
						<?php foreach ( $this->month_names( $language ) as $itmms_number => $itmms_name ) : ?>
							<option value="<?php echo esc_attr( (string) $itmms_number ); ?>" <?php selected( $month, $itmms_number ); ?>><?php echo esc_html( $itmms_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					<span class="screen-reader-text"><?php echo esc_html( $labels['year'] ); ?></span>
					<select data-itmms-monthly-year aria-label="<?php echo esc_attr( $labels['year'] ); ?>">
		<?php for ( $itmms_option_year = max( 1970, $year - 5 ); $itmms_option_year <= min( 2099, $year + 5 ); $itmms_option_year++ ) : ?>
							<option value="<?php echo esc_attr( (string) $itmms_option_year ); ?>" <?php selected( $year, $itmms_option_year ); ?>><?php echo esc_html( (string) $itmms_option_year ); ?></option>
						<?php endfor; ?>
					</select>
				</label>
				<button type="button" data-itmms-monthly-step="1" aria-label="<?php echo esc_attr( $labels['next'] ); ?>" title="<?php echo esc_attr( $labels['next'] ); ?>">&#8250;</button>
				<button type="button" class="itmms-public-monthly__current" data-itmms-monthly-current <?php disabled( $month === (int) $now->format( 'n' ) && $year === (int) $now->format( 'Y' ) ); ?>><?php echo esc_html( $labels['current_month'] ); ?></button>
				<button type="button" class="itmms-public-monthly__print" data-itmms-monthly-print><?php echo esc_html( $labels['print'] ); ?></button>
			</div>
			<p class="itmms-public-monthly__error" data-itmms-monthly-error role="status" hidden></p>
		<?php endif; ?>
	</div>
	<div class="itmms-public-monthly__trust">
		<?php foreach ( $trust_items as $itmms_trust_item ) : ?>
			<?php if ( '' !== trim( (string) $itmms_trust_item[1] ) ) : ?>
				<span><b><?php echo esc_html( (string) $itmms_trust_item[0] ); ?></b><?php echo esc_html( (string) $itmms_trust_item[1] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php if ( 'compact' === $design ) : ?>
		<div class="itmms-public-monthly__cards">
			<?php foreach ( $data['days'] as $itmms_day ) : ?>
				<?php $itmms_prayers = $this->indexed_prayers( (array) ( $itmms_day['prayers'] ?? [] ) ); ?>
				<article class="itmms-public-monthly__card <?php echo (string) ( $itmms_day['date'] ?? '' ) === $today ? 'is-today' : ''; ?> <?php echo '5' === date_i18n( 'w', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ? 'is-friday' : ''; ?>">
					<header>
						<div>
							<strong><?php echo esc_html( date_i18n( 'M j', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ); ?></strong>
							<span><?php echo esc_html( date_i18n( 'l', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ); ?></span>
						</div>
					</header>
					<div>
						<?php foreach ( [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ] as $itmms_key ) : ?>
							<span>
								<b><?php echo esc_html( $this->prayer_label( $itmms_key, $language, ucfirst( $itmms_key ) ) ); ?></b>
								<time><?php echo esc_html( (string) ( $itmms_prayers[ $itmms_key ]['time'] ?? '' ) ); ?></time>
							</span>
						<?php endforeach; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	<?php else : ?>
		<div class="itmms-public-monthly__scroll">
			<table class="itmms-public-monthly__table">
				<caption class="screen-reader-text"><?php echo esc_html( (string) $atts['title'] . ' - ' . (string) ( $data['label'] ?? '' ) ); ?></caption>
				<thead>
					<tr>
						<th scope="col"><?php echo esc_html( $labels['date'] ); ?></th>
						<?php foreach ( $prayer_keys as $itmms_key ) : ?>
							<th scope="col"><?php echo esc_html( $this->prayer_label( $itmms_key, $language, ucfirst( $itmms_key ) ) ); ?></th>
							<?php if ( $show_iqamah && 'sunrise' !== $itmms_key ) : ?>
								<th scope="col"><?php echo esc_html( $labels['iqamah'] ); ?></th>
							<?php endif; ?>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $data['days'] as $itmms_day ) : ?>
						<?php $itmms_prayers = $this->indexed_prayers( (array) ( $itmms_day['prayers'] ?? [] ) ); ?>
						<tr class="<?php echo (string) ( $itmms_day['date'] ?? '' ) === $today ? 'is-today' : ''; ?> <?php echo '5' === date_i18n( 'w', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ? 'is-friday' : ''; ?>">
							<td>
								<strong><?php echo esc_html( date_i18n( 'M j', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ); ?></strong>
								<span><?php echo esc_html( date_i18n( 'D', strtotime( (string) ( $itmms_day['date'] ?? 'now' ) ) ) ); ?></span>
							</td>
							<?php foreach ( $prayer_keys as $itmms_key ) : ?>
								<td><?php echo esc_html( (string) ( $itmms_prayers[ $itmms_key ]['time'] ?? '' ) ); ?></td>
								<?php if ( $show_iqamah && 'sunrise' !== $itmms_key ) : ?>
									<td><?php echo esc_html( (string) ( $itmms_prayers[ $itmms_key ]['iqamah'] ?? '' ) ); ?></td>
								<?php endif; ?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>
