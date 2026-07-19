/**
 * Re-apply polish patches on monolith Public/REST, then split into traits.
 * Usage: node _future/tools/apply-improvements-and-split.js
 */
const fs = require( 'node:fs' );
const path = require( 'node:path' );
const { execFileSync } = require( 'node:child_process' );

const root = path.resolve( __dirname, '..', '..' );

function patchPublic() {
	const file = path.join( root, 'public/class-itmms-public.php' );
	let src = fs.readFileSync( file, 'utf8' );

	if ( ! src.includes( 'function translate_for_language' ) ) {
		const helper = `
	/**
	 * Translate an English msgid for a public widget language without changing the global locale.
	 */
	private function translate_for_language( string $text, string $language ): string {
		$language = $this->normalize_language( $language );
		if ( 'en' === $language || '' === $text ) {
			return $text;
		}

		$locale = ( 'bn' === $language ) ? 'bn_BD' : 'ar';
		static $catalogs = [];

		if ( ! array_key_exists( $locale, $catalogs ) ) {
			$catalogs[ $locale ] = null;
			$mofile               = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '.mo';
			if ( is_readable( $mofile ) ) {
				if ( ! class_exists( 'MO', false ) ) {
					require_once ABSPATH . WPINC . '/pomo/mo.php';
				}
				$mo = new MO();
				if ( $mo->import_from_file( $mofile ) ) {
					$catalogs[ $locale ] = $mo;
				}
			}
		}

		if ( $catalogs[ $locale ] instanceof MO ) {
			$translated = $catalogs[ $locale ]->translate( $text );
			if ( is_string( $translated ) && '' !== $translated ) {
				return $translated;
			}
		}

		return $text;
	}

	/**
	 * Empty-state notice localized to the widget language (en|bn|ar).
	 *
	 * @param string $title   English msgid.
	 * @param string $message English msgid.
	 */
	private function render_localized_empty_state( string $title, string $message, string $language ): string {
		return $this->render_announcement_empty_state(
			$this->translate_for_language( $title, $language ),
			$this->translate_for_language( $message, $language )
		);
	}

	/**
	 * Keep empty-state msgids visible to gettext scanners (not called at runtime).
	 *
	 * @return array<int,string>
	 */
	private function empty_state_msgids_for_i18n(): array {
		return [
			__( 'Prayer Times is disabled', 'masjidos' ),
			__( 'Enable the Prayer Times module before using this shortcode.', 'masjidos' ),
			__( 'No prayer times available', 'masjidos' ),
			__( 'Check Prayer Setup: timezone, coordinates, and calculation method.', 'masjidos' ),
			__( 'No monthly timetable available', 'masjidos' ),
			__( 'Check Prayer Setup and try again.', 'masjidos' ),
			__( 'No duas found', 'masjidos' ),
			__( 'Try another category or increase the limit.', 'masjidos' ),
			__( 'No verse available', 'masjidos' ),
			__( 'Check back later for today’s Quran verse.', 'masjidos' ),
			__( 'No hadith available', 'masjidos' ),
			__( 'Check back later for today’s hadith.', 'masjidos' ),
			__( 'No names available', 'masjidos' ),
			__( 'The 99 Names collection could not be loaded.', 'masjidos' ),
			__( 'No surahs available', 'masjidos' ),
			__( 'The Audio Quran list could not be loaded.', 'masjidos' ),
		];
	}
`;
		src = src.replace(
			/(private function normalize_language\( string \$language \): string \{[\s\S]*?return in_array\( \$language, \[ 'en', 'bn', 'ar' \], true \) \? \$language : 'en';\n\t\})/,
			`$1\n${ helper }`
		);
	}

	const replacements = [
		[
			/\$language = \$this->normalize_language\( \(string\) \$atts\['language'\] \);\n\t\t\t\$empty_title = __\( 'Prayer Times is disabled', 'masjidos' \);\n\t\t\t\$empty_message = __\( 'Enable the Prayer Times module before using this shortcode\.', 'masjidos' \);\n\t\t\tif \( 'bn' === \$language \) \{\n\t\t\t\t\$empty_title = '[^']*';\n\t\t\t\t\$empty_message = '[^']*';\n\t\t\t\} elseif \( 'ar' === \$language \) \{\n\t\t\t\t\$empty_title = '[^']*';\n\t\t\t\t\$empty_message = '[^']*';\n\t\t\t\}\n\t\t\treturn \$this->render_announcement_empty_state\( \$empty_title, \$empty_message \);/g,
			`$language = $this->normalize_language( (string) $atts['language'] );
			return $this->render_localized_empty_state(
				'Prayer Times is disabled',
				'Enable the Prayer Times module before using this shortcode.',
				$language
			);`,
		],
	];

	// Generic: replace empty-state blocks that follow the if bn / elseif ar pattern.
	src = src.replace(
		/\$empty_title = __\( ('[^']+'), 'masjidos' \);\n\t\t\t\$empty_message = __\( ('[^']+'), 'masjidos' \);\n\t\t\tif \( 'bn' === \$language \) \{\n\t\t\t\t\$empty_title = '[^']*';\n\t\t\t\t\$empty_message = '[^']*';\n\t\t\t\} elseif \( 'ar' === \$language \) \{\n\t\t\t\t\$empty_title = '[^']*';\n\t\t\t\t\$empty_message = '[^']*';\n\t\t\t\}\n\t\t\treturn \$this->render_announcement_empty_state\( \$empty_title, \$empty_message \);/g,
		( match, title, message ) => `return $this->render_localized_empty_state(\n\t\t\t\t${ title },\n\t\t\t\t${ message },\n\t\t\t\t$language\n\t\t\t);`
	);

	// Two-language (bn only, no ar) empty states.
	src = src.replace(
		/\$empty_title = __\( ('[^']+'), 'masjidos' \);\n\t\t\t\$empty_message = __\( ('[^']+'), 'masjidos' \);\n\t\t\tif \( 'bn' === \$language \) \{\n\t\t\t\t\$empty_title = '[^']*';\n\t\t\t\t\$empty_message = '[^']*';\n\t\t\t\}\n\t\t\treturn \$this->render_announcement_empty_state\( \$empty_title, \$empty_message \);/g,
		( match, title, message ) => `return $this->render_localized_empty_state(\n\t\t\t\t${ title },\n\t\t\t\t${ message },\n\t\t\t\t$language\n\t\t\t);`
	);

	// Tabs may be spaces in some blocks — also handle 2-tab indent variants used in monthly etc.
	src = src.replace(
		/\$empty_title = __\( ('[^']+'), 'masjidos' \);\r?\n\t\t\$empty_message = __\( ('[^']+'), 'masjidos' \);\r?\n\t\tif \( 'bn' === \$language \) \{\r?\n\t\t\t\$empty_title = '[^']*';\r?\n\t\t\t\$empty_message = '[^']*';\r?\n\t\t\} elseif \( 'ar' === \$language \) \{\r?\n\t\t\t\$empty_title = '[^']*';\r?\n\t\t\t\$empty_message = '[^']*';\r?\n\t\t\}\r?\n\t\treturn \$this->render_announcement_empty_state\( \$empty_title, \$empty_message \);/g,
		( match, title, message ) => `return $this->render_localized_empty_state(\n\t\t\t${ title },\n\t\t\t${ message },\n\t\t\t$language\n\t\t);`
	);

	src = src.replace(
		/\$empty_title = __\( ('[^']+'), 'masjidos' \);\r?\n\t\t\$empty_message = __\( ('[^']+'), 'masjidos' \);\r?\n\t\tif \( 'bn' === \$language \) \{\r?\n\t\t\t\$empty_title = '[^']*';\r?\n\t\t\t\$empty_message = '[^']*';\r?\n\t\t\}\r?\n\t\treturn \$this->render_announcement_empty_state\( \$empty_title, \$empty_message \);/g,
		( match, title, message ) => `return $this->render_localized_empty_state(\n\t\t\t${ title },\n\t\t\t${ message },\n\t\t\t$language\n\t\t);`
	);

	fs.writeFileSync( file, src, 'utf8' );
	const left = ( src.match( /if \( 'bn' === \$language \) \{\n\t+\$empty_title =/g ) || [] ).length;
	console.log( 'Patched public. Remaining hardcoded empty-title bn blocks:', left );
}

function patchRest() {
	const file = path.join( root, 'includes/class-itmms-rest.php' );
	let src = fs.readFileSync( file, 'utf8' );

	src = src.replace(
		/public function can_manage_khutbah\(\): bool \{\n\t\treturn current_user_can\( 'manage_options' \)\n\t\t\t\|\| current_user_can\( 'itmms_manage_khutbah' \)\n\t\t\t\|\| current_user_can\( 'itmms_manage_announcements' \)\n\t\t\t\|\| current_user_can\( 'itmms_manage_prayers' \);\n\t\}/,
		`public function can_manage_khutbah(): bool {\n\t\treturn current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_khutbah' );\n\t}`
	);

	if ( ! src.includes( 'function public_cached_response' ) ) {
		const helper = `
	/**
	 * Public GET responses that may be cached briefly by browsers/CDNs.
	 *
	 * @param mixed $data Response data.
	 */
	private function public_cached_response( $data, int $max_age = 60 ): WP_REST_Response {
		$response = rest_ensure_response( $data );
		$response->header( 'Cache-Control', 'public, max-age=' . max( 0, $max_age ) );
		return $response;
	}
`;
		src = src.replace(
			/(public function get_prayer_widget\( WP_REST_Request \$request \): WP_REST_Response \{)/,
			`${ helper }\n\t$1`
		);
	}

	const cacheMap = [
		[
			/public function get_prayer_times_today\(\): WP_REST_Response \{\n\t\treturn rest_ensure_response\( ITMMS_SalahAPI::headless_day\( ITMMS_Prayer_Times::today\(\) \) \);\n\t\}/,
			`public function get_prayer_times_today(): WP_REST_Response {\n\t\treturn $this->public_cached_response( ITMMS_SalahAPI::headless_day( ITMMS_Prayer_Times::today() ), 60 );\n\t}`,
		],
		[
			/return rest_ensure_response\( ITMMS_SalahAPI::headless_day\( \$day \) \);/,
			`return $this->public_cached_response( ITMMS_SalahAPI::headless_day( $day ), 120 );`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public monthly timetable widget/,
			`return $this->public_cached_response( [ 'html' => $html ], 60 );\n\t}\n\n\t/**\n\t * Return a public monthly timetable widget`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public Jumuah widget/,
			`return $this->public_cached_response( [ 'html' => $html ], 60 );\n\t}\n\n\t/**\n\t * Return a public Jumuah widget`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public announcements widget/,
			`return $this->public_cached_response( [ 'html' => $html ], 60 );\n\t}\n\n\t/**\n\t * Return a public announcements widget`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public events widget/,
			`return $this->public_cached_response( [ 'html' => $html ], 30 );\n\t}\n\n\t/**\n\t * Return a public events widget`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public Duas/,
			`return $this->public_cached_response( [ 'html' => $html ], 60 );\n\t}\n\n\t/**\n\t * Return a public Duas`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\t\/\*\*\n\t \* Return a public calendar widget/,
			`return $this->public_cached_response( [ 'html' => $html ], 120 );\n\t}\n\n\t/**\n\t * Return a public calendar widget`,
		],
		[
			/return rest_ensure_response\( \[ 'html' => \$html \] \);\n\t\}\n\n\tpublic function get_announcements/,
			`return $this->public_cached_response( [ 'html' => $html ], 60 );\n\t}\n\n\tpublic function get_announcements`,
		],
		[
			/return rest_ensure_response\( \[ 'announcements' => \$announcements \] \);\n\t\}\n\n\t\/\*\*\n\t \* @return WP_REST_Response\|WP_Error\n\t \*\/\n\tpublic function create_announcement/,
			`return $this->public_cached_response( [ 'announcements' => $announcements ], 30 );\n\t}\n\n\t/**\n\t * @return WP_REST_Response|WP_Error\n\t */\n\tpublic function create_announcement`,
		],
		[
			/return rest_ensure_response\( \[ 'events' => \$events \] \);\n\t\}\n\n\t\/\*\*\n\t \* @return WP_REST_Response\|WP_Error\n\t \*\/\n\tpublic function create_event/,
			`return $this->public_cached_response( [ 'events' => $events ], 60 );\n\t}\n\n\t/**\n\t * @return WP_REST_Response|WP_Error\n\t */\n\tpublic function create_event`,
		],
	];

	for ( const [ re, rep ] of cacheMap ) {
		src = src.replace( re, rep );
	}

	fs.writeFileSync( file, src, 'utf8' );
	console.log( 'Patched REST. has cache helper:', src.includes( 'function public_cached_response' ), 'khutbah tight:', ! src.includes( "itmms_manage_announcements' )\n\t\t\t|| current_user_can( 'itmms_manage_prayers'" ) );
}

patchPublic();
patchRest();
execFileSync( process.execPath, [ path.join( __dirname, 'split-public-traits.js' ) ], { stdio: 'inherit', cwd: root } );
execFileSync( process.execPath, [ path.join( __dirname, 'split-rest-traits.js' ) ], { stdio: 'inherit', cwd: root } );
console.log( 'Done.' );
