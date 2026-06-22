<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Pembukuan') }}</title>
    <link rel="icon" href="{{ asset('barcode_scanner.svg') }}">
    @livewireStyles
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --text: #162033;
            --muted: #667085;
            --line: #d9e2f2;
            --accent: #1d4ed8;
            --accent-weak: #dbeafe;
            --danger: #dc2626;
            --success: #16a34a;
            --shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.10), transparent 30%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 100%);
            color: var(--text);
        }

        a { color: inherit; text-decoration: none; }
        .shell {
            /* max-width: 1200px; */
            margin: 0 auto;
            padding: 24px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.2px;
        }
        .nav {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav a {
            padding: 10px 14px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
        }
        .nav a:hover {
            border-color: var(--accent);
            color: var(--accent);
        }
        .nav a.active {
            border-color: var(--accent);
            background: var(--accent);
            color: #fff;
            box-shadow: 0 8px 20px rgba(29, 78, 216, 0.18);
        }
        .nav a.active:hover {
            color: #fff;
        }
        .panel {
            background: var(--panel);
            border: 1px solid rgba(217, 226, 242, 0.85);
            border-radius: 20px;
            box-shadow: var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        .grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 20px;
        }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (max-width: 900px) {
            .grid { display: block; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .nav {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                padding-bottom: 4px;
            }
            .nav a,
            .nav form {
                flex: 0 0 auto;
            }
            .panel { padding: 16px; }
            .toolbar,
            .actions {
                width: 100%;
            }
            .actions > * {
                flex: 1 1 160px;
            }
            .button,
            .nav a,
            .nav button {
                min-height: 44px;
            }
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        input, textarea, button {
            font: inherit;
        }
        .field, .button, .input, .textarea {
            width: 100%;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: #fff;
            padding: 12px 14px;
        }
        form>div {
            margin-bottom: 20px;
        }
        .textarea { min-height: 112px; resize: vertical; }
        .button {
            cursor: pointer;
            background: var(--accent);
            color: white;
            font-weight: 700;
            border: 1px solid var(--accent);
        }
        .button.secondary {
            background: #fff;
            color: var(--text);
            border-color: var(--line);
        }
        .button.danger {
            background: var(--danger);
            border-color: var(--danger);
        }
        .button.success {
            background: var(--success);
            border-color: var(--success);
        }
        .button.small {
            width: auto;
            padding: 8px 10px;
            min-width: 38px;
            font-size: 13px;
        }
        .button.icon {
            width: auto;
            padding: 8px;
            min-width: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .button.icon svg {
            width: 14px;
            height: 14px;
        }
        /* .stack {
            display: grid;
            gap: 14px;
        } */
        .muted { color: var(--muted); }
        form .muted { color: var(--danger); }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
        }
        th, td {
            text-align: left;
            padding: 12px 10px;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }
        th {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--accent-weak);
            color: var(--accent);
            font-size: 13px;
            font-weight: 700;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 20px;
        }
        @media (max-width: 700px) {
            .summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .card {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            background: linear-gradient(180deg, #fff 0%, #f8fbff 100%);
        }
        .card .label { font-size: 13px; color: var(--muted); }
        .card .value { font-size: 20px; font-weight: 800; margin-top: 4px; }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        @media (max-width: 700px) {
            .summary { grid-template-columns: 1fr; }
            .card .value { font-size: 18px; }
            th, td { padding: 10px 8px; }
            .toolbar {
                align-items: stretch;
            }
            .toolbar .actions {
                width: 100%;
            }
            .toolbar .actions > * {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <div class="topbar">
            <div>
                <div class="brand">Pembukuan Toko</div>
                <div class="muted"></div>
            </div>
            <div class="nav">
                <a href="/" class="{{ request()->path() === '/' ? 'active' : '' }}">Dashboard</a>
                <a href="/penjualan" class="{{ request()->is('penjualan*') ? 'active' : '' }}">Penjualan</a>
                @if (auth()->user()?->role === 'owner' || auth()->user()?->role === 'admin')
                    <a href="/transaksi" class="{{ request()->is('transaksi*') ? 'active' : '' }}">Transaksi</a>
                @endif
                <a href="/produk" class="{{ request()->is('produk*') ? 'active' : '' }}">Produk</a>
                @if (auth()->user()?->role === 'owner' || auth()->user()?->role === 'admin')
                    <a href="/users" class="{{ request()->is('users*') ? 'active' : '' }}">Users</a>
                @endif
                <a href="/laporan" class="{{ request()->is('laporan*') ? 'active' : '' }}">Laporan</a>
                <a href="/pengeluaran" class="{{ request()->is('pengeluaran*') ? 'active' : '' }}">Pengeluaran</a>
                <form method="POST" action="/logout" style="display:inline;">
                    @csrf
                    <button type="submit" class="button secondary" style="width:auto;">Logout</button>
                </form>
            </div>
        </div>

        <div class="panel">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: '{{ session('success') }}',
            timer: 2000,
            showConfirmButton: false
        });
    </script>
    @endif
</body>
</html>
