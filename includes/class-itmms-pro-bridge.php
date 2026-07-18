<?php
/**
 * Free ↔ Pro bridge helpers (read-only detection + marketing URLs).
 *
 * Free never loads Pro PHP. Pro defines MASJIDOS_PRO_ACTIVE when bootstrapped.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether MasjidOS Pro is active (defined by the Pro plugin).
 */
function masjidos_pro_is_active(): bool {
	return defined( 'MASJIDOS_PRO_ACTIVE' ) && MASJIDOS_PRO_ACTIVE;
}

/**
 * Marketing / purchase URL for MasjidOS Pro.
 */
function masjidos_pro_url(): string {
	/**
	 * Filter the MasjidOS Pro marketing URL shown in Free upsell UI.
	 *
	 * @param string $url Absolute URL.
	 */
	return (string) apply_filters( 'masjidos_pro_url', 'https://masjidos.com/pro/' );
}

/**
 * Docs / learn-more URL for MasjidOS Pro.
 */
function masjidos_pro_docs_url(): string {
	/**
	 * Filter the MasjidOS Pro documentation URL.
	 *
	 * @param string $url Absolute URL.
	 */
	return (string) apply_filters( 'masjidos_pro_docs_url', masjidos_pro_url() );
}

/**
 * Payload localized to the admin SPA for Pro awareness (info only).
 *
 * @return array<string,mixed>
 */
function masjidos_pro_localize(): array {
	$active = masjidos_pro_is_active();

	/**
	 * Pro plugin injects Docs tab sections here (shortcodes, attributes, notes).
	 * Free only renders the payload — Pro owns the content.
	 *
	 * @param array<int,array<string,mixed>> $docs Sections.
	 */
	$docs = (array) apply_filters( 'masjidos_pro_docs', [] );

	return [
		'active'  => $active,
		'url'     => masjidos_pro_url(),
		'docsUrl' => masjidos_pro_docs_url(),
		'label'   => $active
			? __( 'MasjidOS Pro is active', 'masjidos' )
			: __( 'Unlock with MasjidOS Pro', 'masjidos' ),
		'cta'     => $active
			? __( 'Open Pro', 'masjidos' )
			: __( 'Learn about Pro', 'masjidos' ),
		'docs'    => $docs,
	];
}
