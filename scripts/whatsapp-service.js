/**
 * SIGADIS - WhatsApp Web JS Microservice
 *
 * Microservice Node.js untuk mengirimkan pesan & kode verifikasi OTP
 * melalui WhatsApp Web JS (wwebjs/whatsapp-web.js).
 *
 * Cara Menjalankan:
 * 1. Install dependencies: npm install whatsapp-web.js qrcode-terminal express cors
 * 2. Jalankan service: npm run whatsapp:service (atau node scripts/whatsapp-service.js)
 * 3. Scan QR code yang muncul di terminal menggunakan aplikasi WhatsApp di HP Anda.
 * 4. Set OTP_GATEWAY=wwebjs di file .env Laravel Anda.
 *
 * Cara Disconnect / Forget Session:
 * - Lewat HP: Buka WA -> Linked Devices -> Klik perangkat -> Log Out.
 * - Lewat Terminal: npm run whatsapp:logout (atau node scripts/whatsapp-service.js --logout)
 * - Lewat HTTP Endpoint: POST http://127.0.0.1:3000/logout
 */

const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const express = require('express');
const cors = require('cors');
const fs = require('fs');
const path = require('path');

const AUTH_PATH = path.resolve(process.cwd(), '.wwebjs_auth');

// Handle CLI Flag: --logout (Hapus sesi lokal & keluar)
if (process.argv.includes('--logout')) {
    console.log('[SIGADIS] Menghapus sesi WhatsApp Web lokal (.wwebjs_auth)...');
    try {
        if (fs.existsSync(AUTH_PATH)) {
            fs.rmSync(AUTH_PATH, { recursive: true, force: true });
            console.log('✅ Sesi WhatsApp Web berhasil dihapus (Forgotten / Disconnected).');
        } else {
            console.log('ℹ️ Tidak ada sesi aktif yang tersimpan.');
        }
    } catch (err) {
        console.error('❌ Gagal menghapus folder sesi:', err.message);
    }
    process.exit(0);
}

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

let isReady = false;
let qrCodeString = null;

// Inisialisasi WhatsApp Client dengan sesi persisten (LocalAuth)
const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: './.wwebjs_auth',
    }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
        ],
    },
});

client.on('qr', (qr) => {
    qrCodeString = qr;
    isReady = false;
    console.log('\n=============================================================');
    console.log(' [SIGADIS] Silakan Scan QR Code berikut dengan WhatsApp Anda:');
    console.log('=============================================================\n');
    qrcode.generate(qr, { small: true });
    console.log('\nMenunggu scan...\n');
});

client.on('ready', () => {
    isReady = true;
    qrCodeString = null;
    console.log('\n=============================================================');
    console.log(' [SIGADIS] WhatsApp Web JS Client SIAP! Layanan aktif di port ' + PORT);
    console.log('=============================================================\n');
});

client.on('authenticated', () => {
    console.log('[SIGADIS] WhatsApp Web Berhasil Terotentikasi!');
});

client.on('auth_failure', (msg) => {
    isReady = false;
    console.error('[SIGADIS] Autentikasi Gagal:', msg);
});

client.on('disconnected', (reason) => {
    isReady = false;
    console.log('[SIGADIS] WhatsApp Client Terputus (Disconnected):', reason);
    // Hapus sesi lokal jika di-disconnect dari HP
    try {
        if (fs.existsSync(AUTH_PATH)) {
            fs.rmSync(AUTH_PATH, { recursive: true, force: true });
        }
    } catch (e) {}
    console.log('[SIGADIS] Menginisialisasi ulang client untuk scan baru...');
    client.initialize().catch(() => {});
});

// Format nomor Indonesia (08xx -> 628xx@c.us)
function formatToChatId(phone) {
    let clean = phone.replace(/\D/g, '');
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (clean.startsWith('8')) {
        clean = '62' + clean;
    }
    return clean + '@c.us';
}

// Endpoint Status
app.get('/status', (req, res) => {
    res.json({
        ready: isReady,
        hasQr: !!qrCodeString,
        timestamp: new Date().toISOString(),
    });
});

// Endpoint Logout / Forget Session secara remote
app.post('/logout', async (req, res) => {
    try {
        if (isReady) {
            await client.logout();
        }
        if (fs.existsSync(AUTH_PATH)) {
            fs.rmSync(AUTH_PATH, { recursive: true, force: true });
        }
        isReady = false;
        qrCodeString = null;
        console.log('[SIGADIS] Sesi WhatsApp Web berhasil di-logout dan dihapus.');

        // Re-init agar siap menerima QR scan baru jika diperlukan
        setTimeout(() => {
            client.initialize().catch(() => {});
        }, 1000);

        return res.json({
            success: true,
            message: 'Sesi WhatsApp berhasil di-logout dan dihapus (Forgotten).',
        });
    } catch (err) {
        console.error('[SIGADIS] Gagal logout sesi:', err);
        return res.status(500).json({ success: false, error: err.message });
    }
});

// Endpoint Kirim OTP / Pesan
app.post('/send-otp', async (req, res) => {
    const { phone, code, message } = req.body;

    if (!phone) {
        return res.status(400).json({ success: false, error: 'Nomor telepon (phone) wajib diisi.' });
    }

    if (!isReady) {
        return res.status(503).json({
            success: false,
            error: 'WhatsApp Web Client belum siap atau belum di-scan. Silakan scan QR code di terminal server.',
        });
    }

    try {
        const chatId = formatToChatId(phone);
        const textMessage = message || `*SIGADIS - Sistem Informasi Gawat Darurat Ibu-Selamat*\n\nKode OTP verifikasi Anda: *${code}*\n\nKode berlaku selama 5 menit. Jangan berikan kepada siapapun.`;

        await client.sendMessage(chatId, textMessage);

        console.log(`[SIGADIS] OTP berhasil dikirim ke: ${phone} (${chatId})`);

        return res.json({
            success: true,
            message: 'OTP berhasil dikirim ke WhatsApp.',
            phone: phone,
        });
    } catch (err) {
        console.error(`[SIGADIS] Gagal kirim OTP ke ${phone}:`, err);
        return res.status(500).json({
            success: false,
            error: err.message || 'Gagal mengirim pesan WhatsApp.',
        });
    }
});

// Jalankan HTTP Server & WhatsApp Client
app.listen(PORT, () => {
    console.log(`[SIGADIS] WhatsApp Service HTTP Server running on http://127.0.0.1:${PORT}`);
    client.initialize().catch((err) => {
        console.error('[SIGADIS] Error initializing WhatsApp client:', err);
    });
});
