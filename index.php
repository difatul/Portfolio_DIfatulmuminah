<?php
// Folder & File Configuration for Local PHP Storage
$data_dir = __DIR__ . '/data';
$upload_dir = __DIR__ . '/uploads';
$data_file = $data_dir . '/data.json';

// Automatically create directories if they don't exist
if (!file_exists($data_dir)) {
    @mkdir($data_dir, 0777, true);
}
if (!file_exists($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
}

// Default initial dataset
$default_data = [
    'profile' => [
        'name' => "Difatul Mu'Minah",
        'jurusan' => "Rekayasa Perangkat Lunak (RPL)",
        'heroBio' => "Dokumentasi perjalanan belajar, proyek kejuruan, dan catatan pemahaman materi kejuruan selama 3 tahun di SMK.",
        'aboutBio' => "Halo! Saya Difatul Mu'Minah, seorang siswi SMK jurusan Rekayasa Perangkat Lunak yang bersemangat tentang teknologi dan pengembangan perangkat lunak. Portofolio ini saya buat untuk mendokumentasikan hasil karya dan progres belajar saya.",
        'photo' => "assets/hero-photo.jpg",
        'skills' => [
            ['title' => 'Frontend Web', 'desc' => 'HTML, CSS, JS, Tailwind'],
            ['title' => 'Backend Basics', 'desc' => 'PHP, MySQL, REST API'],
            ['title' => 'UI/UX Design', 'desc' => 'Figma, Wireframing'],
            ['title' => 'Tools & Git', 'desc' => 'Git, VS Code, Linux']
        ]
    ],
    'projects' => [
        [
            'id' => 1710000001,
            'mapel' => 'Pemrograman Web',
            'title' => 'Website Portfolio Responsive PHP',
            'desc' => 'Membuat sistem portofolio berbasis PHP dan Tailwind CSS yang mendukung pengunggahan file tugas serta jurnal catatan materi.',
            'link' => 'https://github.com',
            'docFile' => null,
            'imgFile' => null,
            'date' => date('d/m/Y')
        ],
        [
            'id' => 1710000002,
            'mapel' => 'Basis Data',
            'title' => 'Rancangan ERD Perpustakaan Digital',
            'desc' => 'Perancangan struktur basis data relational menggunakan MySQL, pembuatan ERD, dan skrip DDL/DML lengkap.',
            'link' => '',
            'docFile' => null,
            'imgFile' => null,
            'date' => date('d/m/Y', strtotime('-5 days'))
        ]
    ],
    'notes' => [
        [
            'id' => 2710000001,
            'mapel' => 'Basis Data',
            'topic' => 'Pemahaman Query JOIN & Relasi Tabel',
            'content' => "INNER JOIN: Mengambil record yang memiliki nilai cocok di kedua tabel.\nLEFT JOIN: Mengambil semua record dari tabel kiri dan record yang cocok dari tabel kanan.",
            'date' => date('d/m/Y', strtotime('-2 days'))
        ],
        [
            'id' => 2710000002,
            'mapel' => 'Pemrograman Web',
            'topic' => 'Arsitektur PHP Native & Handle File Upload',
            'content' => "Penggunaan \$_FILES di PHP untuk memproses unggahan file, memvalidasi tipe mime, dan menyimpannya menggunakan move_uploaded_file().",
            'date' => date('d/m/Y', strtotime('-6 days'))
        ]
    ]
];

// Load existing data or write defaults
if (!file_exists($data_file)) {
    file_put_contents($data_file, json_encode($default_data, JSON_PRETTY_PRINT));
    $app_data = $default_data;
} else {
    $raw_content = file_get_contents($data_file);
    $app_data = json_decode($raw_content, true) ?: $default_data;
}

$flash_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Handle Edit Profile
    if ($action === 'save_profile') {
        $app_data['profile']['name'] = trim($_POST['prof_name'] ?? '');
        $app_data['profile']['jurusan'] = trim($_POST['prof_jurusan'] ?? '');
        $app_data['profile']['heroBio'] = trim($_POST['prof_heroBio'] ?? '');
        $app_data['profile']['aboutBio'] = trim($_POST['prof_aboutBio'] ?? '');

        // Update skills
        for ($i = 0; $i < 4; $i++) {
            if (isset($_POST["sk_title_$i"])) {
                $app_data['profile']['skills'][$i] = [
                    'title' => trim($_POST["sk_title_$i"]),
                    'desc' => trim($_POST["sk_desc_$i"])
                ];
            }
        }

        // Handle Photo Upload if present
        if (isset($_FILES['prof_photo']) && $_FILES['prof_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['prof_photo']['name'], PATHINFO_EXTENSION);
            $new_photo_name = 'hero-photo-' . time() . '.' . $ext;
            $target_photo = $upload_dir . '/' . $new_photo_name;
            if (move_uploaded_file($_FILES['prof_photo']['tmp_name'], $target_photo)) {
                $app_data['profile']['photo'] = 'uploads/' . $new_photo_name;
            }
        }

        file_put_contents($data_file, json_encode($app_data, JSON_PRETTY_PRINT));
        header('Location: index.php?msg=profile_updated');
        exit;
    }

    // Handle Upload Project
    if ($action === 'add_project') {
        $doc_info = null;
        $img_info = null;

        // Process Doc Upload
        if (isset($_FILES['p_doc']) && $_FILES['p_doc']['error'] === UPLOAD_ERR_OK) {
            $doc_name = time() . '_doc_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['p_doc']['name']);
            if (move_uploaded_file($_FILES['p_doc']['tmp_name'], $upload_dir . '/' . $doc_name)) {
                $doc_info = [
                    'name' => $_FILES['p_doc']['name'],
                    'path' => 'uploads/' . $doc_name
                ];
            }
        }

        // Process Image Upload
        if (isset($_FILES['p_img']) && $_FILES['p_img']['error'] === UPLOAD_ERR_OK) {
            $img_name = time() . '_img_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['p_img']['name']);
            if (move_uploaded_file($_FILES['p_img']['tmp_name'], $upload_dir . '/' . $img_name)) {
                $img_info = [
                    'name' => $_FILES['p_img']['name'],
                    'path' => 'uploads/' . $img_name
                ];
            }
        }

        $new_project = [
            'id' => time(),
            'mapel' => trim($_POST['p_mapel'] ?? 'Lainnya'),
            'title' => trim($_POST['p_title'] ?? ''),
            'desc' => trim($_POST['p_desc'] ?? ''),
            'link' => trim($_POST['p_link'] ?? ''),
            'docFile' => $doc_info,
            'imgFile' => $img_info,
            'date' => date('d/m/Y')
        ];

        array_unshift($app_data['projects'], $new_project);
        file_put_contents($data_file, json_encode($app_data, JSON_PRETTY_PRINT));
        header('Location: index.php?msg=project_added#projects');
        exit;
    }

    // Handle Delete Project
    if ($action === 'delete_project') {
        $pid = intval($_POST['project_id'] ?? 0);
        $app_data['projects'] = array_filter($app_data['projects'], function ($p) use ($pid) {
            return $p['id'] !== $pid;
        });
        file_put_contents($data_file, json_encode($app_data, JSON_PRETTY_PRINT));
        header('Location: index.php?msg=project_deleted#projects');
        exit;
    }

    // Handle Add Note
    if ($action === 'add_note') {
        $new_note = [
            'id' => time(),
            'mapel' => trim($_POST['n_mapel'] ?? ''),
            'topic' => trim($_POST['n_topic'] ?? ''),
            'content' => trim($_POST['n_content'] ?? ''),
            'date' => date('d/m/Y')
        ];

        array_unshift($app_data['notes'], $new_note);
        file_put_contents($data_file, json_encode($app_data, JSON_PRETTY_PRINT));
        header('Location: index.php?msg=note_added#notes');
        exit;
    }

    // Handle Delete Note
    if ($action === 'delete_note') {
        $nid = intval($_POST['note_id'] ?? 0);
        $app_data['notes'] = array_filter($app_data['notes'], function ($n) use ($nid) {
            return $n['id'] !== $nid;
        });
        file_put_contents($data_file, json_encode($app_data, JSON_PRETTY_PRINT));
        header('Location: index.php?msg=note_deleted#notes');
        exit;
    }
}

$profile = $app_data['profile'];
$projects = $app_data['projects'];
$notes = $app_data['notes'];
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Siswa SMK | <?= htmlspecialchars($profile['name']) ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        handwriting: ['Caveat', 'cursive'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                        },
                        neon: {
                            cyan: '#06b6d4',
                            magenta: '#ec4899',
                            purple: '#a855f7'
                        },
                        dark: {
                            bg: '#0f172a',
                            card: '#1e293b',
                        }
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'fade-in-up': 'fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #0f172a;
            color: #f8fafc;
            overflow-x: hidden;
        }

        .glass {
            background: rgba(30, 41, 59, 0.65);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-card {
            background: linear-gradient(145deg, rgba(30,41,59,0.85) 0%, rgba(15,23,42,0.95) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(14, 165, 233, 0.15);
            border-color: rgba(14, 165, 233, 0.35);
        }

        .kinetic-text {
            background: linear-gradient(to right, #38bdf8, #a855f7, #ec4899, #38bdf8);
            background-size: 200% auto;
            color: transparent;
            -webkit-background-clip: text;
            background-clip: text;
            animation: gradient-move 5s linear infinite;
        }

        @keyframes gradient-move {
            to { background-position: 200% center; }
        }

        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        input, textarea, select {
            background: rgba(15, 23, 42, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: white;
            transition: all 0.3s ease;
        }
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #0ea5e9;
            box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.25);
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-500 selection:text-white">

    <div class="fixed inset-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-0 -left-10 w-96 h-96 bg-cyan-500/20 rounded-full mix-blend-screen filter blur-[128px] animate-blob"></div>
        <div class="absolute top-1/4 -right-10 w-96 h-96 bg-purple-500/20 rounded-full mix-blend-screen filter blur-[128px] animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-10 left-1/3 w-96 h-96 bg-pink-500/15 rounded-full mix-blend-screen filter blur-[128px] animate-blob animation-delay-4000"></div>
    </div>

    <nav class="fixed top-4 inset-x-0 z-50 transition-all duration-300 px-4" id="navbar">
        <div class="max-w-5xl mx-auto">
            <div class="glass rounded-full px-5 py-2.5 flex justify-between items-center border border-white/10 shadow-2xl backdrop-blur-xl">
                <a href="#" class="text-base sm:text-lg font-bold tracking-tight text-white flex items-center gap-2 pl-2">
                    <i class="ph ph-code text-brand-400 text-xl"></i>
                    <span><?= htmlspecialchars(explode(' ', $profile['name'])[0]) ?><span class="text-brand-400">.Portfolio</span></span>
                </a>
                
                <div class="hidden md:flex items-center space-x-6 text-xs sm:text-sm font-medium text-slate-300">
                    <a href="#home" class="hover:text-white transition-colors">Beranda</a>
                    <a href="#about" class="hover:text-white transition-colors">Tentang</a>
                    <a href="#projects" class="hover:text-white transition-colors">Proyek</a>
                    <a href="#notes" class="hover:text-white transition-colors">Catatan</a>
                </div>

                <div class="flex items-center gap-2">
                    <button onclick="openProfileModal()" class="flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white px-3 py-1.5 rounded-full text-xs sm:text-sm font-medium transition-all duration-300 border border-white/15" title="Edit Profil Saya">
                        <i class="ph ph-user-gear text-brand-400 font-bold"></i>
                        <span class="hidden sm:inline">Edit Profil</span>
                    </button>
                    <button onclick="openModal()" class="flex items-center gap-1.5 bg-brand-500 hover:bg-brand-600 text-white px-4 py-1.5 rounded-full text-xs sm:text-sm font-semibold transition-all duration-300 shadow-md">
                        <span>Upload Tugas</span>
                        <i class="ph ph-plus-circle font-bold"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main>
        <section id="home" class="min-h-screen pt-32 pb-16 px-6 flex flex-col justify-center items-center relative overflow-hidden">
            <div class="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center z-10">
                
                <!-- Left Text Side -->
                <div class="lg:col-span-7 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-brand-500/10 text-brand-300 border border-brand-500/20 text-xs sm:text-sm font-semibold mb-4">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                        <span><?= htmlspecialchars($profile['jurusan']) ?></span>
                    </div>

                    <div class="mb-2">
                        <span class="font-handwriting text-3xl sm:text-4xl text-brand-400 tracking-wide font-bold">
                            Halo, saya 👋
                        </span>
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white mb-6 leading-tight">
                        <span class="kinetic-text"><?= htmlspecialchars($profile['name']) ?></span>
                    </h1>

                    <p class="text-slate-300 text-base sm:text-lg max-w-xl mx-auto lg:mx-0 mb-8 leading-relaxed font-normal">
                        <?= htmlspecialchars($profile['heroBio']) ?>
                    </p>

                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                        <a href="#projects" class="px-7 py-3.5 bg-brand-500 hover:bg-brand-600 text-white rounded-full font-semibold transition-all shadow-[0_0_25px_rgba(14,165,233,0.4)] hover:shadow-[0_0_35px_rgba(14,165,233,0.6)] flex items-center gap-2 text-sm sm:text-base">
                            Eksplor Proyek <i class="ph ph-arrow-down-right text-lg"></i>
                        </a>
                        <button onclick="openProfileModal()" class="px-6 py-3.5 glass hover:bg-white/10 text-brand-300 border border-brand-500/30 rounded-full font-semibold transition-all flex items-center gap-2 text-sm sm:text-base">
                            <i class="ph ph-user-gear text-lg text-brand-400"></i> Edit Profil
                        </button>
                    </div>
                </div>

                <!-- Right Photo Side with Neon Ambient Aura -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative group max-w-md w-full">
                        <!-- Neon Glow Backlight matching the photo's cyan/magenta lighting -->
                        <div class="absolute -inset-1.5 bg-gradient-to-r from-cyan-500 via-purple-500 to-pink-500 rounded-3xl blur-xl opacity-60 group-hover:opacity-90 transition duration-700"></div>
                        
                        <!-- Photo Frame Container -->
                        <div class="relative rounded-3xl overflow-hidden border border-white/20 glass shadow-2xl aspect-[4/5] flex items-center justify-center bg-slate-900">
                            <?php 
                                $photo_path = $profile['photo'];
                                if (!file_exists($photo_path) && strpos($photo_path, 'http') !== 0) {
                                    $photo_path = "https://placehold.co/600x750/0f172a/38bdf8?text=Foto+Siswa";
                                }
                            ?>
                            <img src="<?= htmlspecialchars($photo_path) ?>" 
                                 alt="Foto <?= htmlspecialchars($profile['name']) ?>" 
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">

                            <!-- Bottom Floating Glass Badge -->
                            <div class="absolute bottom-4 left-4 right-4 glass rounded-2xl p-3.5 border border-white/15 backdrop-blur-md flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <div>
                                        <p class="text-xs text-slate-300 font-medium">Siswa SMK Aktif</p>
                                        <p class="text-xs font-bold text-white"><?= htmlspecialchars($profile['name']) ?></p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-lg bg-brand-500/20 text-brand-300 text-[11px] font-semibold border border-brand-500/30">
                                    Official
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <section id="about" class="py-24 px-6 relative border-t border-white/5">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-brand-500/10 text-brand-300 border border-brand-500/20 text-xs font-medium mb-3">
                            <i class="ph ph-user-circle"></i> Profil Siswa
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold">Tentang Saya</h2>
                    </div>
                    <button onclick="openProfileModal()" class="shrink-0 flex items-center gap-2 bg-brand-500/20 hover:bg-brand-500 text-brand-300 hover:text-white border border-brand-500/30 px-4 py-2.5 rounded-xl text-sm font-medium transition-all">
                        <i class="ph ph-pencil-simple text-lg"></i> Edit Informasi Diri
                    </button>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <div class="lg:col-span-5">
                        <div class="glass-card rounded-2xl p-6 border border-white/10 space-y-4">
                            <div class="flex items-center gap-4">
                                <img src="<?= htmlspecialchars($photo_path) ?>" class="w-16 h-16 rounded-xl object-cover border border-brand-500/30">
                                <div>
                                    <h3 class="font-bold text-white text-lg"><?= htmlspecialchars($profile['name']) ?></h3>
                                    <p class="text-xs text-brand-400 font-medium"><?= htmlspecialchars($profile['jurusan']) ?></p>
                                </div>
                            </div>
                            <p class="text-sm text-slate-300 leading-relaxed border-t border-white/5 pt-4">
                                <?= nl2br(htmlspecialchars($profile['aboutBio'])) ?>
                            </p>
                        </div>
                    </div>

                    <div class="lg:col-span-7">
                        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                            <i class="ph ph-lightbulb text-brand-400 text-xl"></i> Keahlian Utama Kejuruan
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($profile['skills'] as $index => $sk): ?>
                                <div class="glass-card p-4 rounded-xl border border-white/5">
                                    <div class="w-9 h-9 rounded-lg bg-brand-500/10 border border-brand-500/20 flex items-center justify-center mb-3">
                                        <i class="ph ph-check-circle text-brand-400 text-xl"></i>
                                    </div>
                                    <h4 class="font-semibold text-white text-sm mb-1"><?= htmlspecialchars($sk['title']) ?></h4>
                                    <p class="text-xs text-slate-400"><?= htmlspecialchars($sk['desc']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="projects" class="py-24 px-6 relative z-10 bg-slate-900/40 border-t border-white/5">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 text-xs font-medium mb-3">
                            <i class="ph ph-folder-open"></i> Arsip Tugas Siswa
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-2">Arsip Tugas & Proyek</h2>
                        <p class="text-slate-400 max-w-2xl text-sm">
                            Kumpulan hasil pengerjaan tugas mata pelajaran kejuruan. Guru dapat melihat dan mengunduh berkas di sini.
                        </p>
                    </div>
                    <button onclick="openModal()" class="shrink-0 flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all shadow-lg shadow-brand-500/25">
                        <i class="ph ph-plus-circle text-lg"></i>
                        Tambah Tugas Baru
                    </button>
                </div>

                <!-- Projects List Grid -->
                <?php if (empty($projects)): ?>
                    <div class="flex flex-col items-center justify-center py-20 text-center glass rounded-2xl">
                        <i class="ph-duotone ph-folder-open text-6xl text-slate-500 mb-4"></i>
                        <h3 class="text-xl font-medium text-white mb-2">Belum ada tugas disetor</h3>
                        <p class="text-slate-400 max-w-md mb-6 text-sm">Tambahkan tugas atau proyek pertama Anda agar guru dapat mengoreksinya.</p>
                        <button onclick="openModal()" class="text-brand-400 hover:text-brand-300 font-medium flex items-center gap-2 text-sm">
                            <i class="ph ph-plus"></i> Upload Tugas Sekarang
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($projects as $project): ?>
                            <div class="glass-card rounded-2xl p-6 group relative overflow-hidden flex flex-col h-full">
                                <div class="relative z-10 flex flex-col h-full">
                                    
                                    <?php if (!empty($project['imgFile']['path'])): ?>
                                        <div class="mb-4 rounded-xl overflow-hidden border border-white/10 max-h-48 bg-slate-900/50 flex items-center justify-center">
                                            <img src="<?= htmlspecialchars($project['imgFile']['path']) ?>" alt="<?= htmlspecialchars($project['title']) ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex justify-between items-start mb-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-brand-500/10 text-brand-300 border border-brand-500/20 text-xs font-semibold">
                                            <i class="ph ph-book-open"></i> <?= htmlspecialchars($project['mapel']) ?>
                                        </span>
                                        
                                        <!-- Form PHP for deleting project -->
                                        <form method="POST" onsubmit="return confirm('Hapus tugas ini?');" class="inline">
                                            <input type="hidden" name="action" value="delete_project">
                                            <input type="hidden" name="project_id" value="<?= $project['id'] ?>">
                                            <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100 p-1" title="Hapus Tugas">
                                                <i class="ph ph-trash text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <h3 class="text-lg font-bold text-white mb-2 group-hover:text-brand-300 transition-colors">
                                        <?= htmlspecialchars($project['title']) ?>
                                    </h3>
                                    
                                    <p class="text-slate-400 text-xs sm:text-sm line-clamp-3 mb-4 flex-grow leading-relaxed">
                                        <?= nl2br(htmlspecialchars($project['desc'])) ?>
                                    </p>

                                    <?php if (!empty($project['docFile']['path'])): ?>
                                        <div class="mb-3 p-2.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2 overflow-hidden text-xs">
                                                <i class="ph ph-file-pdf text-red-400 text-lg shrink-0"></i>
                                                <span class="text-slate-300 font-medium truncate"><?= htmlspecialchars($project['docFile']['name']) ?></span>
                                            </div>
                                            <a href="<?= htmlspecialchars($project['docFile']['path']) ?>" download class="shrink-0 p-1.5 rounded-lg bg-brand-500/20 hover:bg-brand-500 text-brand-300 hover:text-white transition-colors" title="Download Dokumen">
                                                <i class="ph ph-download-simple text-sm"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-auto pt-4 border-t border-white/5 flex items-center justify-between text-xs">
                                        <span class="text-slate-500 flex items-center gap-1">
                                            <i class="ph ph-calendar-blank"></i> <?= htmlspecialchars($project['date']) ?>
                                        </span>
                                        <?php if (!empty($project['link'])): ?>
                                            <a href="<?= htmlspecialchars($project['link']) ?>" target="_blank" class="text-brand-400 hover:underline flex items-center gap-1 font-semibold">
                                                Demo <i class="ph ph-arrow-up-right"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section id="notes" class="py-24 px-6 relative z-10 border-t border-white/5">
            <div class="max-w-6xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-500/10 text-purple-300 border border-purple-500/20 text-xs font-medium mb-3">
                            <i class="ph ph-brain"></i> Jurnal Belajar & Refleksi
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold">Catatan Pemahaman Materi</h2>
                        <p class="text-slate-400 max-w-2xl text-sm mt-2">
                            Dokumentasi pemahaman konsep dan ringkasan materi dari setiap mata pelajaran kejuruan.
                        </p>
                    </div>
                    <button onclick="openNoteModal()" class="shrink-0 flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all shadow-lg shadow-purple-600/25">
                        <i class="ph ph-plus-circle text-lg"></i>
                        Tambah Catatan
                    </button>
                </div>

                <?php if (empty($notes)): ?>
                    <div class="flex flex-col items-center justify-center py-16 text-center glass rounded-2xl">
                        <i class="ph-duotone ph-notebook text-5xl text-slate-500 mb-3"></i>
                        <h3 class="text-lg font-medium text-white mb-1">Belum ada catatan pemahaman</h3>
                        <p class="text-slate-400 text-sm max-w-md mb-4">Tuliskan pemahamanmu terkait topik materi seperti SQL JOIN, OOP, atau Responsive Web.</p>
                        <button onclick="openNoteModal()" class="text-purple-400 hover:text-purple-300 text-sm font-medium flex items-center gap-1.5">
                            <i class="ph ph-plus"></i> Buat Catatan Pertama
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($notes as $note): ?>
                            <div class="glass-card rounded-2xl p-6 relative group border-purple-500/20 hover:border-purple-500/40">
                                <div class="flex justify-between items-start mb-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-500/10 text-purple-300 border border-purple-500/20 text-xs font-semibold">
                                        <i class="ph ph-book-bookmark"></i> <?= htmlspecialchars($note['mapel']) ?>
                                    </span>
                                    
                                    <form method="POST" onsubmit="return confirm('Hapus catatan ini?');" class="inline">
                                        <input type="hidden" name="action" value="delete_note">
                                        <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                                        <button type="submit" class="text-slate-500 hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100 p-1" title="Hapus Catatan">
                                            <i class="ph ph-trash text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                                <h4 class="text-lg font-bold text-white mb-2 group-hover:text-purple-300 transition-colors"><?= htmlspecialchars($note['topic']) ?></h4>
                                <p class="text-slate-300 text-xs sm:text-sm mb-4 leading-relaxed whitespace-pre-line"><?= htmlspecialchars($note['content']) ?></p>
                                <div class="pt-3 border-t border-white/5 flex justify-between items-center text-xs text-slate-500">
                                    <span class="flex items-center gap-1"><i class="ph ph-clock"></i> Ditambahkan <?= htmlspecialchars($note['date']) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <footer class="py-8 px-6 border-t border-white/10 text-center text-slate-500 text-sm">
            <div class="max-w-6xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
                <p>&copy; <?= date('Y') ?> <span><?= htmlspecialchars($profile['name']) ?></span> — Portofolio Siswa SMK PHP.</p>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-white transition-colors"><i class="ph ph-github-logo text-xl"></i></a>
                    <a href="https://www.instagram.com/difatul12/" class="hover:text-white transition-colors"><i class="ph ph-instagram-logo text-xl"></i></a>
                    <a href="#" class="hover:text-white transition-colors"><i class="ph ph-linkedin-logo text-xl"></i></a>
                </div>
            </div>
        </footer>
    </main>

    <!-- Edit Profile Modal -->
    <div id="profileModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeProfileModal()"></div>
        <div class="glass-card relative w-full max-w-2xl rounded-2xl p-6 md:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <button onclick="closeProfileModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white bg-white/5 rounded-full p-2">
                <i class="ph ph-x text-lg"></i>
            </button>
            <h3 class="text-2xl font-bold text-white mb-1">Edit Profil & Informasi Diri</h3>
            <p class="text-slate-400 text-sm mb-6">Ubah informasi identitas dan foto siswa.</p>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="save_profile">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap</label>
                        <input type="text" name="prof_name" value="<?= htmlspecialchars($profile['name']) ?>" required class="w-full px-4 py-2.5 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Jurusan / Sub-Keahlian</label>
                        <input type="text" name="prof_jurusan" value="<?= htmlspecialchars($profile['jurusan']) ?>" required class="w-full px-4 py-2.5 rounded-xl text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ganti Foto Siswa (JPG/PNG)</label>
                    <input type="file" name="prof_photo" accept="image/*" class="w-full text-xs text-slate-400 border border-white/10 rounded-xl p-2 bg-slate-900/50">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Ringkasan Bio Hero</label>
                    <textarea name="prof_heroBio" rows="2" required class="w-full px-4 py-2.5 rounded-xl text-sm resize-none"><?= htmlspecialchars($profile['heroBio']) ?></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi "Tentang Saya"</label>
                    <textarea name="prof_aboutBio" rows="3" required class="w-full px-4 py-2.5 rounded-xl text-sm resize-none"><?= htmlspecialchars($profile['aboutBio']) ?></textarea>
                </div>

                <div class="pt-2 border-t border-white/10">
                    <h4 class="text-xs font-bold text-brand-400 mb-3">4 Keahlian Utama</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php for ($i = 0; $i < 4; $i++): 
                            $sk = $profile['skills'][$i] ?? ['title' => '', 'desc' => ''];
                        ?>
                            <div class="glass p-2.5 rounded-xl border border-white/5 space-y-2">
                                <input type="text" name="sk_title_<?= $i ?>" value="<?= htmlspecialchars($sk['title']) ?>" placeholder="Skill #<?= $i+1 ?>" class="w-full px-3 py-1.5 rounded-lg text-xs">
                                <input type="text" name="sk_desc_<?= $i ?>" value="<?= htmlspecialchars($sk['desc']) ?>" placeholder="Detail / Tool" class="w-full px-3 py-1.5 rounded-lg text-xs">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <div class="pt-3">
                    <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-medium transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload Project Modal -->
    <div id="uploadModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeModal()"></div>
        <div class="glass-card relative w-full max-w-lg rounded-2xl p-6 md:p-8 z-10 max-h-[90vh] overflow-y-auto">
            <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white bg-white/5 rounded-full p-2">
                <i class="ph ph-x text-lg"></i>
            </button>
            <h3 class="text-2xl font-bold text-white mb-2">Upload Tugas Baru</h3>
            <p class="text-slate-400 text-xs mb-6">File akan tersimpan ke folder <code class="text-brand-400">uploads/</code> di server lokal Anda.</p>
            
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" value="add_project">
                
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Mata Pelajaran</label>
                    <input type="text" name="p_mapel" required placeholder="Contoh: Pemrograman Web / Basis Data" class="w-full px-4 py-2.5 rounded-xl text-sm">
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Judul Tugas / Proyek</label>
                    <input type="text" name="p_title" required placeholder="Contoh: Aplikasi Pengelolaan Inventory" class="w-full px-4 py-2.5 rounded-xl text-sm">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Deskripsi Singkat</label>
                    <textarea name="p_desc" required rows="3" placeholder="Jelaskan ringkasan pengerjaan tugas ini..." class="w-full px-4 py-2.5 rounded-xl text-sm resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">File Dokumen (PDF/Word)</label>
                        <input type="file" name="p_doc" accept=".pdf,.doc,.docx,.txt" class="w-full text-xs text-slate-400 border border-white/10 rounded-xl p-2 bg-slate-900/50">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-300 mb-1">File Gambar (JPG/PNG)</label>
                        <input type="file" name="p_img" accept="image/*" class="w-full text-xs text-slate-400 border border-white/10 rounded-xl p-2 bg-slate-900/50">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Link Demo / GitHub (Opsional)</label>
                    <input type="url" name="p_link" placeholder="https://github.com/..." class="w-full px-4 py-2.5 rounded-xl text-sm">
                </div>
                
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white rounded-xl font-medium transition-colors">
                        Simpan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Note Modal -->
    <div id="noteModal" class="fixed inset-0 z-[100] hidden flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeNoteModal()"></div>
        <div class="glass-card relative w-full max-w-lg rounded-2xl p-6 md:p-8 z-10">
            <button onclick="closeNoteModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white bg-white/5 rounded-full p-2">
                <i class="ph ph-x text-lg"></i>
            </button>
            <h3 class="text-2xl font-bold text-white mb-1">Tambah Catatan Pemahaman</h3>
            <p class="text-slate-400 text-xs mb-6">Tuliskan jurnal atau ringkasan materi pelajaran kejuruan.</p>
            
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_note">
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Mata Pelajaran</label>
                    <input type="text" name="n_mapel" required placeholder="Contoh: Basis Data" class="w-full px-4 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Topik / Judul Materi</label>
                    <input type="text" name="n_topic" required placeholder="Contoh: Normalisasi Tabel 1NF - 3NF" class="w-full px-4 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-300 mb-1">Ringkasan Pemahaman</label>
                    <textarea name="n_content" required rows="4" placeholder="Tuliskan poin-poin yang dipahami..." class="w-full px-4 py-2.5 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-medium transition-colors">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('uploadModal').classList.remove('hidden'); }
        function closeModal() { document.getElementById('uploadModal').classList.add('hidden'); }

        function openNoteModal() { document.getElementById('noteModal').classList.remove('hidden'); }
        function closeNoteModal() { document.getElementById('noteModal').classList.add('hidden'); }

        function openProfileModal() { document.getElementById('profileModal').classList.remove('hidden'); }
        function closeProfileModal() { document.getElementById('profileModal').classList.add('hidden'); }
    </script>
</body>
</html>