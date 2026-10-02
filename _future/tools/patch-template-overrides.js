const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..', '..');
const files = [
    path.join(root, 'public', 'class-itmms-public.php'),
    path.join(root, 'public', 'trait-itmms-public-display.php')
];

let totalReplacements = 0;

files.forEach(file => {
    let content = fs.readFileSync(file, 'utf8');
    const updated = content.replace(/ITMMS_PLUGIN_DIR \. 'public\/templates\/([^']+)'/g, (match, templateName) => {
        totalReplacements++;
        return `$this->get_template_path( '${templateName}' )`;
    });
    fs.writeFileSync(file, updated, 'utf8');
});

console.log(`Successfully updated ${totalReplacements} template path occurrences.`);
