import fs from 'fs';
import path from 'path';
import sharp from 'sharp';
import { execSync } from 'child_process';

async function generateAssets() {
    console.log('🚀 Preparing source assets from public directory...');
    const assetsDir = path.resolve('assets');
    if (!fs.existsSync(assetsDir)) {
        fs.mkdirSync(assetsDir, { recursive: true });
    }

    const iconSource = path.resolve('public/icon-mobile.png');
    const splashSource = path.resolve('public/assets/mobile/splash.webp');

    if (!fs.existsSync(iconSource)) {
        console.error(`❌ Icon file not found at: ${iconSource}`);
        process.exit(1);
    }
    if (!fs.existsSync(splashSource)) {
        console.error(`❌ Splash file not found at: ${splashSource}`);
        process.exit(1);
    }

    // 1. Prepare logo / icon files in assets/
    console.log('📦 Converting & copying icon -> assets/logo.png, icon-only.png, icon-foreground.png');
    await sharp(iconSource).png().toFile(path.join(assetsDir, 'logo.png'));
    await sharp(iconSource).png().toFile(path.join(assetsDir, 'icon-only.png'));
    await sharp(iconSource).png().toFile(path.join(assetsDir, 'icon-foreground.png'));

    // 2. Prepare splash file in assets/
    console.log('📦 Converting & copying splash -> assets/splash.png');
    await sharp(splashSource).png().toFile(path.join(assetsDir, 'splash.png'));

    // 3. Run capacitor-assets generate for Android
    console.log('✨ Generating Android icons and splash screens via @capacitor/assets...');
    execSync('npx capacitor-assets generate --android', { stdio: 'inherit' });

    // 4. Sync Android
    console.log('🔄 Syncing Android project via Capacitor...');
    execSync('npx cap sync android', { stdio: 'inherit' });

    console.log('✅ Android App Icon & Splash Screen successfully generated & synced!');
}

generateAssets().catch((err) => {
    console.error('❌ Error generating assets:', err);
    process.exit(1);
});
