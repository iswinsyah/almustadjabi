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
            <button class="btn" onclick="openModal()">+ Tambah Kosakata</button>
        </div>
        
        <input type="text" id="searchInput" class="search-box" placeholder="🔍 Cari berdasarkan kata arab atau maknanya..." onkeyup="filterTable()">

        <div class="loading" id="loading">Memuat bank kosakata...</div>
        
        <div class="table-responsive">
            <table id="kosakataTable">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Kata Arab</th>
                        <th width="30%">Arti / Makna</th>
                        <th width="15%">Jenis Kata</th>
                        <th width="10%">Jilid Min.</th>
                        <th width="15%" style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Data akan dirender menggunakan JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Kosakata -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <h3 id="modalTitle" style="margin-top: 0; color: #1E3A8A;">Tambah Kosakata</h3>
            <form id="kosakataForm">
                <input type="hidden" id="kata_id">
                
                <div class="form-group">
                    <label>Kata Arab (Gunakan Harokat Lengkap)</label>
                    <input type="text" id="kata_arab" class="form-control arab-text" dir="rtl" placeholder="Contoh: مَدْرَسَةٌ" required>
                </div>
                
                <div class="form-group">
                    <label>Arti / Makna</label>
                    <input type="text" id="arti" class="form-control" placeholder="Contoh: Sekolah" required>
                </div>
                
                <div class="form-group">
                    <label>Jenis Kata</label>
                    <select id="jenis_kata" class="form-control" required>
                        <option value="isim">Isim (Kata Benda/Sifat)</option>
                        <option value="fiil">Fi'il (Kata Kerja)</option>
                        <option value="huruf">Huruf (Kata Tugas/Sambung)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Jilid Minimal (Akan keluar mulai jilid keberapa?)</label>
                    <input type="number" id="jilid_minimal" class="form-control" min="1" max="6" value="1" required>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn btn-warning" onclick="closeModal()" style="background: #E5E7EB; color: #333;">Batal</button>
                    <button type="submit" class="btn" id="btnSubmit">Simpan Kosakata</button>
                </div>
            </form>
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
            
            if(data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align:center; padding: 30px; color: #6B7280;">Bank kosakata masih kosong. Mulai menabung kata sekarang!</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const badgeClass = 'badge-' + item.jenis_kata;
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td class="arab-text" dir="rtl">${item.kata_arab}</td>
                    <td>${item.arti}</td>
                    <td><span class="badge ${badgeClass}">${item.jenis_kata.toUpperCase()}</span></td>
                    <td>Jilid ${item.jilid_minimal}</td>
                    <td style="text-align: right;">
                        <button class="btn btn-warning" onclick='editKata(${JSON.stringify(item)})' style="padding: 6px 12px; font-size: 0.85rem; margin-right: 5px;">Edit</button>
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
                item.kata_arab.toLowerCase().includes(query) || 
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
            document.getElementById('kata_arab').value = item.kata_arab;
            document.getElementById('arti').value = item.arti;
            document.getElementById('jenis_kata').value = item.jenis_kata;
            document.getElementById('jilid_minimal').value = item.jilid_minimal;
            
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
                kata_arab: document.getElementById('kata_arab').value,
                arti: document.getElementById('arti').value,
                jenis_kata: document.getElementById('jenis_kata').value,
                jilid_minimal: document.getElementById('jilid_minimal').value
            };

            const res = await apiRequest('save_kosakata', payload);
            if (res) {
                closeModal();
                loadData(); // Muat ulang tabel
            }
            
            btn.textContent = 'Simpan Kosakata'; btn.disabled = false;
        });

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