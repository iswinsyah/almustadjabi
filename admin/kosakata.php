<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Kosakata - Admin Qiroatul Kutub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F3F4F6; margin: 0; padding: 0; color: #333; }
        .navbar { background: #1E3A8A; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .navbar a { color: #BFDBFE; text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .navbar a:hover { color: #ffffff; }
        .container { max-width: 1000px; margin: 30px auto; background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        h2 { margin: 0; color: #1E3A8A; }
        
        .btn { background: #2563EB; color: white; padding: 10px 15px; border: none; border-radius: 8px; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 600; transition: 0.2s; }
        .btn:hover { background: #1D4ED8; transform: translateY(-2px); }
        .btn-danger { background: #EF4444; } .btn-danger:hover { background: #DC2626; }
        .btn-warning { background: #F59E0B; } .btn-warning:hover { background: #D97706; }
        
        .search-box { width: 100%; padding: 12px 15px; border: 1px solid #D1D5DB; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif; font-size: 0.95rem; margin-bottom: 20px; transition: border-color 0.2s; }
        .search-box:focus { outline: none; border-color: #2563EB; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { background: #EFF6FF; color: #1E3A8A; font-weight: 600; }
        tr:hover { background-color: #F9FAFB; }
        .arab-text { font-size: 1.5rem; font-weight: bold; color: #2563EB; }
        
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(3px); }
        .modal { background: white; width: 90%; max-width: 500px; border-radius: 12px; padding: 25px; box-sizing: border-box; animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; color: #4B5563; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #D1D5DB; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .form-control:focus { outline: none; border-color: #2563EB; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: bold; }
        .badge-isim { background: #D1FAE5; color: #065F46; }
        .badge-fiil { background: #DBEAFE; color: #1E40AF; }
        .badge-huruf { background: #FEF3C7; color: #92400E; }
        
        .loading { text-align: center; padding: 50px; color: #6B7280; display: none; font-weight: 500; }
    </style>
</head>
<body>
    <div class="navbar">
        <div style="font-size: 1.2rem; font-weight: bold;">Qiroatul Kutub Admin</div>
        <a href="index.php">← Kembali ke Dashboard</a>
    </div>

    <div class="container">
        <div class="header-flex">
            <h2>Bank Kosakata 📚</h2>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-warning" onclick="openModalBulk()">📋 Paste dari Excel</button>
                <button class="btn" onclick="openModal()">+ Tambah 1 Kata</button>
            </div>
        </div>
        
        <input type="text" id="searchInput" class="search-box" placeholder="🔍 Cari berdasarkan kata arab atau maknanya..." onkeyup="filterTable()">

        <div class="loading" id="loading">Memuat bank kosakata...</div>
        
        <div class="table-responsive">
            <table id="kosakataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Harokat Penuh</th>
                        <th width="20%">Harokat Sebagian</th>
                        <th width="20%">Tanpa Harokat</th>
                        <th width="20%">Arti</th>
                        <th width="15%" style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Data akan dirender menggunakan JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit 1 Kosakata -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <h3 id="modalTitle" style="margin-top: 0; color: #1E3A8A;">Tambah Kosakata</h3>
            <form id="kosakataForm">
                <input type="hidden" id="kata_id">
                
                <div class="form-group">
                    <label>Kata Berharokat Penuh</label>
                    <input type="text" id="kata_penuh" class="form-control arab-text" dir="rtl" required>
                </div>
                
                <div class="form-group">
                    <label>Kata Berharokat Sebagian (Boleh dikosongkan)</label>
                    <input type="text" id="kata_sebagian" class="form-control arab-text" dir="rtl">
                </div>
                
                <div class="form-group">
                    <label>Kata Tanpa Harokat (Gundul)</label>
                    <input type="text" id="kata_gundul" class="form-control arab-text" dir="rtl" required>
                </div>
                
                <div class="form-group">
                    <label>Arti / Makna (Bahasa Indonesia)</label>
                    <input type="text" id="arti" class="form-control" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-warning" onclick="closeModal()" style="background: #E5E7EB; color: #333;">Batal</button>
                    <button type="submit" class="btn" id="btnSubmit">Simpan Kosakata</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Paste Massal -->
    <div class="modal-overlay" id="modalOverlayBulk">
        <div class="modal" style="max-width: 700px;">
            <h3 style="margin-top: 0; color: #1E3A8A;">Paste Massal dari Excel</h3>
            <div style="background: #EFF6FF; padding: 15px; border-radius: 8px; font-size: 0.9rem; margin-bottom: 15px; color: #1E3A8A; border: 1px solid #BFDBFE;">
                <strong>⚠️ Cara Pakai:</strong> Copy 4 kolom data dari Excel secara berurutan, lalu Paste (Tempel) di kotak bawah ini:<br><br>
                <span style="font-weight: bold; color: #2563EB;">1. Harokat Penuh &nbsp;|&nbsp; 2. Harokat Sebagian &nbsp;|&nbsp; 3. Tanpa Harokat &nbsp;|&nbsp; 4. Arti</span><br><br>
                <em>*Abaikan Nomor Urut dari Excel, sistem ini akan otomatis memberikan nomornya sendiri.</em>
            </div>
            <textarea id="bulkData" rows="10" class="form-control" placeholder="Paste (Ctrl+V) data dari Excel Anda di sini..."></textarea>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-warning" onclick="closeModalBulk()" style="background: #E5E7EB; color: #333;">Batal</button>
                <button type="button" class="btn" id="btnSubmitBulk" onclick="submitBulk()">Simpan Massal</button>
            </div>
        </div>
    </div>

    <script>
        // Meminta Password Admin (Sama dengan keamanan halaman Dashboard Admin)
        let adminPass = sessionStorage.getItem('admin_password');
        if (!adminPass) {
            adminPass = prompt("Sesi admin terputus. Masukkan Password Super Admin:");
            if (!adminPass) {
                window.location.href = '../index.html';
            } else {
                sessionStorage.setItem('admin_password', adminPass);
            }
        }

        let kosakataData = [];
        document.addEventListener('DOMContentLoaded', loadData);

        // Fungsi Pemanggil API
        async function apiRequest(action, payload = {}) {
            payload.action = action;
            payload.password = adminPass;
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (result.status === 'error') {
                    if(result.message.includes('Password Salah')) {
                        sessionStorage.removeItem('admin_password');
                        alert("Sesi berakhir atau password salah.");
                        window.location.reload();
                    } else {
                        alert('Gagal: ' + result.message);
                    }
                    return null;
                }
                return result;
            } catch (error) {
                alert('Gagal menghubungi server. Pastikan Anda terhubung ke internet.');
                console.error(error);
                return null;
            }
        }

        // Memuat Data Kosakata ke Tabel
        async function loadData() {
            document.getElementById('loading').style.display = 'block';
            document.getElementById('tableBody').innerHTML = '';
            
            const res = await apiRequest('get_kosakata');
            document.getElementById('loading').style.display = 'none';
            
            if (res && res.data) {
                kosakataData = res.data;
                renderTable(kosakataData);
            }
        }

        function renderTable(data) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = '';
            
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #6B7280;">Bank kosakata masih kosong. Import dari Excel sekarang!</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td class="arab-text" dir="rtl">${item.kata_penuh}</td>
                    <td class="arab-text" dir="rtl">${item.kata_sebagian}</td>
                    <td class="arab-text" dir="rtl">${item.kata_gundul}</td>
                    <td>${item.arti}</td>
                    <td style="text-align: right;">
                        <button class="btn btn-warning" onclick='editKata(${JSON.stringify(item).replace(/'/g, "&#39;")})' style="padding: 6px 12px; font-size: 0.85rem; margin-right: 5px;">Edit</button>
                        <button class="btn btn-danger" onclick="deleteKata(${item.id})" style="padding: 6px 12px; font-size: 0.85rem;">Hapus</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Fitur Pencarian Real-time
        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const filtered = kosakataData.filter(item => 
                item.kata_penuh.toLowerCase().includes(query) || 
                item.kata_gundul.toLowerCase().includes(query) ||
                item.arti.toLowerCase().includes(query)
            );
            renderTable(filtered);
        }

        // Interaksi Modal Formulir
        const modal = document.getElementById('modalOverlay');
        
        function openModal() {
            document.getElementById('kosakataForm').reset();
            document.getElementById('kata_id').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Kosakata';
            modal.style.display = 'flex';
        }

        function closeModal() { modal.style.display = 'none'; }

        function editKata(item) {
            document.getElementById('kata_id').value = item.id;
            document.getElementById('kata_penuh').value = item.kata_penuh;
            document.getElementById('kata_sebagian').value = item.kata_sebagian;
            document.getElementById('kata_gundul').value = item.kata_gundul;
            document.getElementById('arti').value = item.arti;
            
            document.getElementById('modalTitle').textContent = 'Edit Kosakata';
            modal.style.display = 'flex';
        }

        // Aksi Simpan Data
        document.getElementById('kosakataForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnSubmit');
            btn.textContent = 'Menyimpan...'; btn.disabled = true;

            const payload = {
                id: document.getElementById('kata_id').value,
                kata_penuh: document.getElementById('kata_penuh').value,
                kata_sebagian: document.getElementById('kata_sebagian').value,
                kata_gundul: document.getElementById('kata_gundul').value,
                arti: document.getElementById('arti').value
            };

            const res = await apiRequest('save_kosakata', payload);
            if (res) {
                closeModal();
                loadData(); // Muat ulang tabel
            }
            
            btn.textContent = 'Simpan Kosakata'; btn.disabled = false;
        });

        // --- LOGIKA PASTE MASSAL (EXCEL) ---
        const modalBulk = document.getElementById('modalOverlayBulk');
        function openModalBulk() { document.getElementById('bulkData').value = ''; modalBulk.style.display = 'flex'; }
        function closeModalBulk() { modalBulk.style.display = 'none'; }

        async function submitBulk() {
            const btn = document.getElementById('btnSubmitBulk');
            const rawData = document.getElementById('bulkData').value.trim();
            
            if (!rawData) {
                alert("Kotak data masih kosong! Silakan paste (Ctrl+V) data dari Excel terlebih dahulu.");
                return;
            }

            btn.textContent = 'Menyimpan...'; btn.disabled = true;

            // Pecah berdasarkan baris, lalu pecah tiap baris berdasarkan spasi Tab (\t) bawaan Excel
            const rows = rawData.split('\\n').map(row => {
                const cols = row.split('\\t').map(c => c.trim());
                return [ cols[0]||'', cols[1]||'', cols[2]||'', cols[3]||'' ]; // Penuh, Sebagian, Gundul, Arti
            });

            const res = await apiRequest('save_bulk_kosakata', { rows: rows });
            if (res) {
                alert(res.message);
                closeModalBulk();
                loadData();
            }
            
            btn.textContent = 'Simpan Massal'; btn.disabled = false;
        }

        // Aksi Hapus Data
        async function deleteKata(id) {
            if(confirm("Yakin ingin menghapus kata ini dari daftar? Data yang hilang tidak bisa dikembalikan.")) {
                const res = await apiRequest('delete_kosakata', { id: id });
                if (res) loadData();
            }
        }
    </script>
</body>
</html>