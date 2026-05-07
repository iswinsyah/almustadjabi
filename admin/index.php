<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Qiroatul Kutub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F3F4F6; margin: 0; padding: 0; color: #333; }
        .navbar { background: #1E3A8A; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .panel-hero { background: linear-gradient(135deg, #1E3A8A, #2563EB); color: white; padding: 30px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(37,99,235,0.3); }
        .panel-hero h2 { margin: 0 0 5px 0; font-size: 1.8rem; }
        .panel-hero p { margin: 0; color: #BFDBFE; font-size: 1rem; }
        
        .btn { background: #2563EB; color: white; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-family: 'Poppins', sans-serif; font-weight: 600; text-decoration: none; display: inline-block; transition: 0.2s; }
        .btn:hover { background: #1D4ED8; transform: translateY(-2px); }
        .btn-success { background: #10B981; font-size: 1.1rem; padding: 15px 25px; box-shadow: 0 4px 10px rgba(16,185,129,0.3); border: 2px solid white; }
        .btn-success:hover { background: #059669; }
        
        .grid-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; border-top: 4px solid #2563EB; }
        .stat-card h3 { margin: 0; font-size: 2.2rem; color: #1E3A8A; }
        .stat-card p { margin: 5px 0 0 0; color: #6B7280; font-weight: 500; }
        
        .panel { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; white-space: nowrap; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #E5E7EB; }
        th { background: #EFF6FF; color: #1E3A8A; font-weight: 600; }
        tr:hover { background-color: #F9FAFB; }
        select { padding: 8px; border-radius: 6px; border: 1px solid #D1D5DB; font-family: 'Poppins', sans-serif; font-weight: 600; cursor: pointer; outline: none; }
    </style>
</head>
<body>
    <div class="navbar">
        <div style="font-size: 1.2rem; font-weight: bold;">Qiroatul Kutub Admin</div>
        <a href="../index.html" style="color: #BFDBFE; text-decoration: none;" onclick="sessionStorage.clear()">Keluar (Logout)</a>
    </div>

    <div class="container">
        <div class="panel-hero">
            <div>
                <h2>Selamat Datang, Super Admin! 👑</h2>
                <p>Kelola pengguna, pantau statistik, dan perkaya materi aplikasi dari satu tempat.</p>
            </div>
            <a href="kosakata.php" class="btn btn-success">📚 Buka Bank Kosakata</a>
        </div>

        <div class="grid-stats">
            <div class="stat-card">
                <h3 id="stat-users">0</h3>
                <p>Total Santri</p>
            </div>
            <div class="stat-card" style="border-top-color: #10B981;">
                <h3 id="stat-l">0</h3>
                <p>Santri Laki-laki</p>
            </div>
            <div class="stat-card" style="border-top-color: #F59E0B;">
                <h3 id="stat-p">0</h3>
                <p>Santri Perempuan</p>
            </div>
            <div class="stat-card" style="border-top-color: #8B5CF6;">
                <h3 id="stat-wakaf">Rp 0</h3>
                <p>Total Sedekah</p>
            </div>
        </div>

        <div class="panel">
            <h3 style="margin-top: 0; color: #1E3A8A; margin-bottom: 20px;">Daftar Pengguna & Kelola Akses</h3>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>WhatsApp</th>
                            <th>Domisili</th>
                            <th>Status Akses</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr><td colspan="6" style="text-align: center; color: #6B7280; padding: 20px;">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let adminPass = sessionStorage.getItem('admin_password');
        if (!adminPass) {
            adminPass = prompt("Sesi Admin Terkunci.\nMasukkan Password Super Admin:");
            if (!adminPass) window.location.href = '../index.html';
            else sessionStorage.setItem('admin_password', adminPass);
        }

        async function apiRequest(action, payload = {}) {
            payload.action = action; payload.password = adminPass;
            try {
                const response = await fetch('api.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                const result = await response.json();
                if (result.status === 'error') {
                    if(result.message.includes('Password Salah')) { sessionStorage.removeItem('admin_password'); alert("Password salah!"); window.location.reload(); }
                    return null;
                }
                return result;
            } catch (error) { return null; }
        }

        async function loadDashboard() {
            const stats = await apiRequest('get_stats');
            if (stats && stats.data) {
                document.getElementById('stat-users').textContent = stats.data.total_users;
                document.getElementById('stat-l').textContent = stats.data.total_l;
                document.getElementById('stat-p').textContent = stats.data.total_p;
                document.getElementById('stat-wakaf').textContent = 'Rp ' + parseInt(stats.data.total_wakaf).toLocaleString('id-ID');
            }

            const users = await apiRequest('get_users');
            if (users && users.data) {
                const tbody = document.getElementById('userTableBody'); tbody.innerHTML = '';
                users.data.forEach((u, i) => {
                    let waLengkap = u.whatsapp && u.whatsapp !== '-' ? u.whatsapp.replace(/^0/, '62') : '';
                    let waLink = waLengkap ? `<a href="https://wa.me/${waLengkap}" target="_blank" style="color: #10B981; font-weight: 500; text-decoration: none;">💬 ${u.whatsapp}</a>` : '-';
                    let colorSelect = u.status_akun === 'super_admin' ? '#DBEAFE' : (u.status_akun === 'premium' ? '#D1FAE5' : '#F3F4F6');
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${i + 1}</td> <td><strong>${u.username}</strong></td> <td>${u.nama}</td> <td>${waLink}</td> <td>${u.domisili}</td>
                        <td>
                            <select onchange="updateRole('${u.username}', this.value)" style="background: ${colorSelect}; color: #1E3A8A;">
                                <option value="free" ${u.status_akun === 'free' ? 'selected' : ''}>Free</option> <option value="premium" ${u.status_akun === 'premium' ? 'selected' : ''}>Premium</option> <option value="super_admin" ${u.status_akun === 'super_admin' ? 'selected' : ''}>Super Admin</option>
                            </select>
                        </td>`;
                    tbody.appendChild(tr);
                });
            }
        }

        async function updateRole(username, newRole) {
            if(confirm("Yakin ingin mengubah akses " + username + " menjadi " + newRole.toUpperCase() + "?")) { const res = await apiRequest('update_role', { target_username: username, new_role: newRole }); if(res) { alert(res.message); loadDashboard(); } } else { loadDashboard(); }
        }
        loadDashboard();
    </script>
</body>
</html>