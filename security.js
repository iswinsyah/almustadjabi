// --- SISTEM KEAMANAN HAKI (HAK KEKAYAAN INTELEKTUAL) ---

// 1. Suntikkan CSS Anti-Copas & Anti-Print
const style = document.createElement('style');
style.innerHTML = `
    body {
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        user-select: none !important;
    }
    @media print {
        html, body { display: none !important; }
    }
`;
document.head.appendChild(style);

// 2. Cegah Klik Kanan
document.addEventListener('contextmenu', e => e.preventDefault());

// 3. Cegah Shortcut Keyboard (PrintScreen, Copy, Print, dll)
document.addEventListener('keydown', e => {
    if (e.key === 'PrintScreen') {
        navigator.clipboard.writeText(''); // Kosongkan clipboard
        alert('Tindakan Screenshot dilarang untuk melindungi Hak Kekayaan Intelektual (HAKI) Pesantren Villa Quran.');
        e.preventDefault();
    }
    if (e.ctrlKey && ['c','p','s','u','a','C','P','S','U','A'].includes(e.key)) {
        e.preventDefault();
    }
    if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i'))) {
        e.preventDefault();
    }
});

// 4. Blur layar saat aplikasi kehilangan fokus (Menggagalkan Snipping Tool OS)
window.addEventListener('blur', () => document.body.style.filter = 'blur(15px)');
window.addEventListener('focus', () => document.body.style.filter = 'none');