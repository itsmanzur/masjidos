const fs = require('node:fs');
const path = require('node:path');
const { execSync } = require('node:child_process');

const rootDir = path.resolve(__dirname, '..', '..');
const releaseDir = path.join(rootDir, '_release');
const tempDir = path.join(releaseDir, 'masjidos');

// Get version from masjidos.php
const mainFileContent = fs.readFileSync(path.join(rootDir, 'masjidos.php'), 'utf8');
const versionMatch = mainFileContent.match(/define\(\s*'ITMMS_VERSION'\s*,\s*'([^']+)'\s*\)/);
if (!versionMatch) {
    console.error('Error: Could not find ITMMS_VERSION in masjidos.php');
    process.exit(1);
}
const version = versionMatch[1];

// Generate timestamp: YYYYMMDD-HHmmss
const now = new Date();
const pad = (num) => String(num).padStart(2, '0');
const timestamp = `${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}-${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}`;
const zipName = `masjidos-${version}-${timestamp}.zip`;
const zipPath = path.join(releaseDir, zipName);

console.log(`Preparing release ZIP for version ${version}...`);

// Cleanup temp dir if exists
if (fs.existsSync(tempDir)) {
    fs.rmSync(tempDir, { recursive: true, force: true });
}

// Create temp dir
fs.mkdirSync(tempDir, { recursive: true });

// Items to copy
const items = [
    'admin',
    'includes',
    'languages',
    'public',
    'masjidos.php',
    'uninstall.php',
    'readme.txt',
    'LICENSE'
];

for (const item of items) {
    const src = path.join(rootDir, item);
    const dest = path.join(tempDir, item);
    if (fs.existsSync(src)) {
        console.log(`Copying ${item}...`);
        fs.cpSync(src, dest, { recursive: true });
    } else {
        console.warn(`Warning: ${item} does not exist at root.`);
    }
}

// Run Compress-Archive via powershell
console.log(`Zipping to ${zipPath}...`);
try {
    execSync(`powershell -Command "Compress-Archive -Path _release/masjidos -DestinationPath _release/${zipName} -Force"`, {
        cwd: rootDir,
        stdio: 'inherit'
    });
} catch (err) {
    console.error('Error zipping archive:', err.message);
    process.exit(1);
}

// Cleanup temp dir
console.log('Cleaning up temporary files...');
fs.rmSync(tempDir, { recursive: true, force: true });

// Check ZIP existence and size
if (fs.existsSync(zipPath)) {
    const stats = fs.statSync(zipPath);
    console.log(`\nSuccess! Released zip built:`);
    console.log(`File: ${zipName}`);
    console.log(`Path: ${zipPath}`);
    console.log(`Size: ${stats.size} bytes (${(stats.size / 1024).toFixed(2)} KB)`);
} else {
    console.error('Error: ZIP file was not created.');
    process.exit(1);
}
