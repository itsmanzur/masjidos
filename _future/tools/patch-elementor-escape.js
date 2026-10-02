const fs = require('fs');
const path = require('path');

const widgetsDir = path.resolve(__dirname, '..', '..', 'includes', 'elementor', 'widgets');
const files = fs.readdirSync(widgetsDir).filter(f => f.endsWith('.php'));

files.forEach(file => {
    const fullPath = path.join(widgetsDir, file);
    let content = fs.readFileSync(fullPath, 'utf8');

    // Remove old phpcs:ignore / phpcs:disable comments above echo
    content = content.replace(/\t\t\/\/ phpcs:ignore WordPress\.Security\.EscapeOutput\.OutputNotEscaped[^\n]*\n\t\techo ([^;]+);/g, '\t\techo wp_kses_post( $1 );');
    
    // In education widget switch cases
    content = content.replace(/\t\t\t\techo (\$public->[^;]+);/g, '\t\t\t\techo wp_kses_post( $1 );');
    content = content.replace(/\t\t\/\/ phpcs:disable WordPress\.Security\.EscapeOutput\.OutputNotEscaped[^\n]*\n/g, '');
    content = content.replace(/\t\t\/\/ phpcs:enable WordPress\.Security\.EscapeOutput\.OutputNotEscaped[^\n]*\n/g, '');

    // Any remaining raw echoes in render()
    content = content.replace(/\t\t\techo ITMMS_Ask_Imam::get_instance\(\)->([a-zA-Z0-9_]+)\( \$atts \);/g, '\t\t\techo wp_kses_post( ITMMS_Ask_Imam::get_instance()->$1( $atts ) );');

    fs.writeFileSync(fullPath, content, 'utf8');
    console.log(`Updated ${file}`);
});
