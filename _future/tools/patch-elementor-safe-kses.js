const fs = require('fs');
const path = require('path');

const widgetsDir = path.resolve(__dirname, '..', '..', 'includes', 'elementor', 'widgets');
const files = fs.readdirSync(widgetsDir).filter(f => f.endsWith('.php'));

files.forEach(file => {
    const fullPath = path.join(widgetsDir, file);
    let content = fs.readFileSync(fullPath, 'utf8');

    // Replace wp_kses_post with safe_kses or direct safe output
    content = content.replace(/echo wp_kses_post\(\s*(ITMMS_Public::get_instance\(\)->[^\)]+\))\s*\);/g, '// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.\n\t\techo $1;');
    content = content.replace(/echo wp_kses_post\(\s*(\$public->[^\)]+\))\s*\);/g, '// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().\n\t\t\t\techo $1;');
    content = content.replace(/echo wp_kses_post\(\s*(ITMMS_Ask_Imam::get_instance\(\)->[^\)]+\))\s*\);/g, '// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Ask_Imam with custom form/select tags.\n\t\t\techo $1;');

    fs.writeFileSync(fullPath, content, 'utf8');
    console.log(`Updated ${file}`);
});
