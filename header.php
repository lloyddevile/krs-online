<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KRS Online — STMIK SZ NW Anjani Lombok Timur</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0b6b50;
            --primary-dark: #064c3b;
            --primary-light: #dff4ea;
            --accent: #f59e0b;
            --bg: #f4f7f5;
            --card: #ffffff;
            --text: #172033;
            --muted: #667085;
            --border: #e4e9e6;
            --danger: #dc2626;
            --success: #17935f;
            --shadow: 0 14px 40px rgba(22, 63, 49, 0.08);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Manrope', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background:
                radial-gradient(circle at 100% 0%, rgba(245, 158, 11, 0.06), transparent 22rem),
                var(--bg);
            color: var(--text);
            line-height: 1.5;
            letter-spacing: -0.01em;
        }
        button, input, select, textarea { font: inherit; }
        h1, h2, h3, h4 { letter-spacing: -0.035em; }
        body::before {
            display: none;
        }
        .topbar {
            background:
                linear-gradient(120deg, rgba(2, 44, 34, 0.96), rgba(11, 107, 80, 0.94)),
                var(--primary-dark);
            color: #fff;
            padding: 15px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 18px rgba(6, 78, 59, 0.12);
            flex-wrap: wrap;
            gap: 10px;
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand-logo {
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            overflow: hidden;
            border-radius: 8px;
        }
        .brand-logo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }
        .brand-badge {
            background: #f59e0b;
            color: #1e293b;
            font-weight: 800;
            padding: 8px 12px;
            border-radius: 12px;
            font-size: 15px;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 16px rgba(245, 158, 11, 0.22);
        }
        .brand h1 { font-size: 17px; font-weight: 700; }
        .brand p { font-size: 12px; opacity: 0.88; }
        .campus-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border: 1px solid rgba(255,255,255,0.28);
            border-radius: 999px;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            transition: background .2s, transform .2s;
        }
        .campus-link:hover { background: rgba(255,255,255,0.14); transform: translateY(-1px); }
        .nav-links { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 13px;
            border-radius: 999px;
            background: rgba(255,255,255,0.12);
            transition: 0.2s;
        }
        .nav-links a:hover, .nav-links a.active { background: rgba(255,255,255,0.28); }
        .nav-links a.logout { background: #dc2626; }
        .container { max-width: 1180px; margin: 24px auto; padding: 0 18px 60px; }
        .card {
            background: var(--card);
            border-radius: 18px;
            padding: 22px 24px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
            gap: 10px;
        }
        .card-header h2 { font-size: 18px; color: var(--primary-dark); }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 18px; }
        .grid-3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; }
        .auth-layout {
            display: grid;
            grid-template-columns: minmax(280px, 1.18fr) minmax(320px, 0.82fr);
            gap: 20px;
            align-items: stretch;
        }
        .hero-card, .auth-card { min-height: 460px; }
        .auth-card { display: flex; flex-direction: column; }
        .stat-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            padding: 16px;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(22, 63, 49, 0.05);
        }
        .stat-box .label { font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; }
        .stat-box .val { font-size: 20px; font-weight: 800; color: var(--text); margin-top: 4px; }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        th, td { padding: 11px 12px; text-align: left; border-bottom: 1px solid var(--border); }
        th { background: #f8fafc; color: var(--primary-dark); font-weight: 700; }
        tr:hover td { background: #f8fafc; }
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-outline { background: #fff; border: 1px solid var(--border); color: var(--text); }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        .btn { position: relative; overflow: hidden; }
        .btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px rgba(22, 63, 49, 0.12); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: linear-gradient(135deg, #0b8060, var(--primary)); }
        .btn-accent { background: linear-gradient(135deg, #fbbf24, #d97706); }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 5px; }
        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 14px;
            background: #fbfcfb;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(11, 107, 80, 0.11);
            background: #fff;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 500;
        }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-warning { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .card { animation: card-in .55s ease-out both; }
        .stat-box { transition: transform .2s ease, box-shadow .2s ease; }
        .stat-box:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(22, 63, 49, 0.09); }
        table { display: table; }
        @keyframes card-in {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--muted);
            font-size: 12.5px;
            border-top: 1px solid var(--border);
            background: #fff;
        }
        @media (max-width: 760px) {
            .topbar { padding: 13px 16px; }
            .brand h1 { font-size: 14px; }
            .brand p { font-size: 11px; }
            .brand-badge { padding: 7px 9px; font-size: 12px; }
            .brand-logo { width: 44px; height: 44px; flex-basis: 44px; }
            .campus-link { padding: 7px 11px; font-size: 11px; }
            .container { margin-top: 14px !important; padding: 0 12px 40px; }
            .card { padding: 18px 16px; border-radius: 15px; }
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
            .nav-links { width: 100%; }
            .nav-links a { flex: 1; text-align: center; }
            .topbar > div:last-child:not(.brand):not(.nav-links) { display: none; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .feature-grid { grid-template-columns: 1fr 1fr !important; }
            .hero-title { font-size: 23px !important; }
            .auth-layout { grid-template-columns: 1fr; }
            .hero-card, .auth-card { min-height: auto; }
        }
        @media print {
            .topbar, .no-print, .footer { display: none !important; }
            .card { box-shadow: none; border: 1px solid #000; }
        }
    </style>
</head>
<body>
