<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'JB Work Shop') }} — Premier Chartered Accountant & Financial Loan Consultancy</title>
    <meta name="description" content="Professional CA, financial advisory, and loan syndication services for MSMEs and corporate enterprises. CMA preparation, Detailed Project Reports (DPR), business loans, working capital, and subsidy support.">

    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|plus-jakarta-sans:500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5.3 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

    <!-- Design tokens & theme -->
    <link rel="stylesheet" href="{{ asset('css/jb-tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-theme.css') }}">

    @vite(['resources/js/app.js'])

    <style>
        :root {
            --brand-primary: #4338ca;
            --brand-primary-light: #6366f1;
            --brand-primary-dark: #312e81;
            --brand-accent: #0ea5e9;
            --brand-gradient: linear-gradient(135deg, #1e1b4b 0%, #312e81 45%, #4338ca 100%);
            --brand-card-bg: #ffffff;
            --brand-body-bg: #f8fafc;
            --text-heading: #0f172a;
            --text-muted: #64748b;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #334155;
            background-color: var(--brand-body-bg);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
        }

        /* Sticky Navigation */
        .landing-header {
            position: sticky;
            top: 0;
            z-index: 1040;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .landing-header.scrolled {
            box-shadow: 0 10px 30px -10px rgba(15, 23, 42, 0.08);
            background: rgba(255, 255, 255, 0.98);
        }
        .landing-nav-link {
            color: #475569;
            font-weight: 500;
            font-size: 0.925rem;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }
        .landing-nav-link:hover, .landing-nav-link:focus {
            color: var(--brand-primary);
            background-color: #eef2ff;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background: var(--brand-gradient);
            color: #ffffff;
            padding: 6.5rem 0 6rem;
            overflow: hidden;
        }
        .hero-mesh-blob-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.35) 0%, rgba(99, 102, 241, 0) 70%);
            filter: blur(50px);
            pointer-events: none;
        }
        .hero-mesh-blob-2 {
            position: absolute;
            bottom: -20%;
            right: -5%;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.25) 0%, rgba(14, 165, 233, 0) 70%);
            filter: blur(60px);
            pointer-events: none;
        }
        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1.15rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-size: 0.85rem;
            font-weight: 600;
            color: #e0e7ff;
            backdrop-filter: blur(10px);
            animation: fadeInDown 0.8s ease-out;
        }
        .hero-title {
            font-size: clamp(2.35rem, 5vw, 3.65rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.03em;
            color: #ffffff;
            animation: fadeInUp 0.8s ease-out 0.1s both;
        }
        .hero-lead {
            font-size: 1.15rem;
            line-height: 1.65;
            color: #c7d2fe;
            max-width: 600px;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        /* Hero Glass Card Preview */
        .hero-glass-panel {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 1.5rem;
            backdrop-filter: blur(20px);
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            animation: fadeInRight 0.9s ease-out 0.25s both;
        }
        .hero-metric-item {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 1rem;
            padding: 1.25rem;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .hero-metric-item:hover {
            transform: translateY(-3px);
            background: rgba(255, 255, 255, 0.14);
        }

        /* Section Components */
        .section-tag {
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--brand-primary);
            background: #eef2ff;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: clamp(2rem, 3.5vw, 2.65rem);
            font-weight: 800;
            color: var(--text-heading);
            letter-spacing: -0.025em;
            line-height: 1.25;
        }
        .section-subtitle {
            font-size: 1.05rem;
            color: var(--text-muted);
            max-width: 680px;
            margin: 0.5rem auto 0;
            line-height: 1.6;
        }

        /* Statistics Counter Section */
        .stats-bar-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.07);
            margin-top: -3.5rem;
            position: relative;
            z-index: 10;
        }
        .stat-counter-number {
            font-size: clamp(2.25rem, 4vw, 3rem);
            font-weight: 800;
            color: var(--brand-primary);
            letter-spacing: -0.03em;
            line-height: 1;
        }

        /* Cards and Grid */
        .premium-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 2rem;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .premium-card:hover {
            transform: translateY(-5px);
            border-color: #c7d2fe;
            box-shadow: 0 20px 35px -10px rgba(67, 56, 202, 0.12), 0 4px 12px -2px rgba(0, 0, 0, 0.04);
        }
        .card-icon-box {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2ff;
            color: var(--brand-primary);
            margin-bottom: 1.25rem;
            transition: all 0.25s ease;
        }
        .premium-card:hover .card-icon-box {
            background: var(--brand-primary);
            color: #ffffff;
            transform: scale(1.05);
        }

        /* Process Steps Bar */
        .process-steps-wrap {
            position: relative;
        }
        .process-steps-line {
            position: absolute;
            top: 2.25rem;
            left: 5%;
            right: 5%;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
        }
        .step-node {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .step-node-bubble {
            width: 4.5rem;
            height: 4.5rem;
            margin: 0 auto 1.25rem;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #e2e8f0;
            color: var(--brand-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 800;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        .step-node:hover .step-node-bubble {
            border-color: var(--brand-primary);
            background: var(--brand-primary);
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 12px 25px rgba(67, 56, 202, 0.25);
        }

        /* Timeline */
        .timeline-item {
            position: relative;
            padding-left: 3rem;
            margin-bottom: 2rem;
        }
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 2rem;
            bottom: -2rem;
            width: 2px;
            background: #e2e8f0;
        }
        .timeline-item:last-child::before {
            display: none;
        }
        .timeline-marker {
            position: absolute;
            left: 0;
            top: 0;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 50%;
            background: #eef2ff;
            border: 2px solid var(--brand-primary);
            color: var(--brand-primary);
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        /* Why Choose Us & Loan Products Grid */
        .benefit-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.75rem;
            transition: all 0.25s ease;
            height: 100%;
        }
        .benefit-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
            transform: translateY(-3px);
        }
        .product-pill-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            background: #f1f5f9;
            color: #475569;
        }

        /* CTA Section */
        .full-cta-section {
            background: var(--brand-gradient);
            color: #ffffff;
            border-radius: 1.75rem;
            padding: 4.5rem 2.5rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(49, 46, 129, 0.35);
        }

        /* Contact Section Form */
        .contact-form-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.07);
        }
        .contact-sidebar-card {
            background: linear-gradient(145deg, #1e1b4b 0%, #312e81 100%);
            color: #ffffff;
            border-radius: 1.5rem;
            padding: 2.5rem;
            height: 100%;
        }
        .contact-icon-pill {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #ffffff;
        }

        /* FAQ Accordion */
        .accordion-item {
            border: 1px solid #e2e8f0;
            border-radius: 0.875rem !important;
            margin-bottom: 0.875rem;
            overflow: hidden;
            background: #ffffff;
        }
        .accordion-button {
            font-weight: 600;
            color: var(--text-heading);
            padding: 1.25rem 1.5rem;
            background: #ffffff;
            box-shadow: none !important;
        }
        .accordion-button:not(.collapsed) {
            color: var(--brand-primary);
            background: #f8fafc;
        }
        .accordion-body {
            padding: 1.25rem 1.5rem;
            color: #475569;
            line-height: 1.7;
            background: #f8fafc;
        }

        /* Buttons & Actions */
        .btn-brand-primary {
            background-color: var(--brand-primary);
            border-color: var(--brand-primary);
            color: #ffffff;
            font-weight: 600;
            padding: 0.85rem 1.85rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(67, 56, 202, 0.35);
        }
        .btn-brand-primary:hover {
            background-color: var(--brand-primary-dark);
            border-color: var(--brand-primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 56, 202, 0.45);
        }
        .btn-brand-secondary {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            font-weight: 600;
            padding: 0.85rem 1.85rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(8px);
            transition: all 0.2s ease;
        }
        .btn-brand-secondary:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        /* Floating Interactive Widgets */
        .floating-cta-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1020;
            box-shadow: 0 10px 25px rgba(67, 56, 202, 0.4);
            border-radius: 9999px;
            padding: 0.75rem 1.35rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.25s ease;
        }
        .floating-cta-btn:hover {
            transform: scale(1.05);
        }
        .back-to-top-btn {
            position: fixed;
            bottom: 2rem;
            left: 2rem;
            z-index: 1020;
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        .back-to-top-btn.visible {
            opacity: 1;
            visibility: visible;
        }
        .back-to-top-btn:hover {
            background: var(--brand-primary);
            color: #ffffff;
            border-color: var(--brand-primary);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .reveal-on-scroll {
            opacity: 0;
            transform: translateY(25px);
            transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-on-scroll.is-revealed {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

    @php
        // 1. Configurable Statistics Array
        $statsConfig = [
            ['number' => 500, 'suffix' => '+', 'label' => 'Satisfied Clients', 'desc' => 'Corporates & MSMEs'],
            ['number' => 1000, 'suffix' => '+', 'label' => 'Applications Assisted', 'desc' => 'Successful Approvals'],
            ['number' => 10, 'suffix' => '+', 'label' => 'Loan Products', 'desc' => 'Bank Facilities'],
            ['number' => 5, 'suffix' => '+', 'label' => 'Years Experience', 'desc' => 'CA & Advisory'],
        ];

        // 2. Configurable Services Array
        $services = [
            [
                'title' => 'Bank Loan Syndication',
                'badge' => 'MSME & Corporate',
                'desc' => 'Unsecured and secured customized business loans to finance working capital, business expansion, equipment, and cash flow needs.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>',
            ],
            [
                'title' => 'Cash Credit / OD',
                'badge' => 'Working Capital',
                'desc' => 'High-limit Cash Credit (CC) and Overdraft (OD) facilities against stock, book debts, and receivables at competitive bank interest rates.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>',
            ],
            [
                'title' => 'Home Loan & LAP',
                'badge' => 'Property Finance',
                'desc' => 'Competitive residential home loans and Loan Against Property (LAP) for business expansion or liquidity with extended tenures.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
            ],
            [
                'title' => 'Term Loan & Machinery',
                'badge' => 'Capex Funding',
                'desc' => 'Structured project and machinery term loans for new factory setups, commercial installations, solar setups, and equipment upgrades.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25zm.75-12h9v9h-9v-9z"/>',
            ],
            [
                'title' => 'CMA Report Preparation',
                'badge' => 'Bank Modeling',
                'desc' => 'Credit Monitoring Arrangement (CMA) data preparation with multi-year financial ratios, MPBF benchmarking, and working capital projections.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>',
            ],
            [
                'title' => 'DPR Preparation',
                'badge' => 'Detailed Project Report',
                'desc' => 'Bank-compliant Detailed Project Reports (DPR) detailing project feasibility, technical viability, IRR/DSCR calculations, and sensitivity analysis.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
            ],
            [
                'title' => 'Government Subsidy',
                'badge' => 'State & Central',
                'desc' => 'Advisory and complete claim documentation for MSME subsidies, capital investment subsidies, CLCSS, and interest subvention benefits.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>',
            ],
            [
                'title' => 'Financial & CA Advisory',
                'badge' => 'Taxation & Audit',
                'desc' => 'Comprehensive Chartered Accountant services including ITR filing, GST compliance, financial audits, and strategic corporate tax planning.',
                'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            ],
        ];
    @endphp

    <!-- 1. STICKY HEADER -->
    <header class="landing-header">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between py-3">
                <a href="#home" class="d-flex align-items-center gap-2 text-decoration-none">
                    <span class="jb-brand-mark rounded-circle d-flex align-items-center justify-content-center" style="width:2.4rem;height:2.4rem;background:var(--brand-primary);color:#fff;box-shadow:0 4px 10px rgba(67,56,202,0.3)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.95"/>
                            <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                    </span>
                    <span class="fw-bold fs-5 tracking-tight font-heading" style="color:var(--text-heading)">{{ config('app.name', 'JB Work Shop') }}</span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="d-none d-lg-flex align-items-center gap-1">
                    <a href="#home" class="landing-nav-link">Home</a>
                    <a href="#about" class="landing-nav-link">About</a>
                    <a href="#services" class="landing-nav-link">Services</a>
                    <a href="#process" class="landing-nav-link">Process</a>
                    <a href="#why-us" class="landing-nav-link">Why Us</a>
                    <a href="#products" class="landing-nav-link">Loan Products</a>
                    <a href="#faq" class="landing-nav-link">FAQ</a>
                    <a href="#contact" class="landing-nav-link">Contact</a>
                </nav>

                <!-- Header Actions -->
                <div class="d-flex align-items-center gap-2">
                    <a href="#contact" class="btn btn-brand-primary btn-sm px-3 py-2 fw-semibold d-none d-sm-inline-flex">
                        Get Consultation
                    </a>

                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary btn-sm px-3 py-2 fw-semibold">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold">
                            Admin Login
                        </a>
                    @endauth

                    <!-- Mobile Hamburger -->
                    <button class="btn btn-light d-lg-none p-2 border" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNav" aria-expanded="false" aria-label="Toggle navigation">
                        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Nav Menu -->
            <div class="collapse d-lg-none pb-3 border-top" id="mobileNav">
                <nav class="d-flex flex-column gap-1 pt-2">
                    <a href="#home" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">Home</a>
                    <a href="#about" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">About</a>
                    <a href="#services" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">Services</a>
                    <a href="#process" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">Process</a>
                    <a href="#why-us" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">Why Choose Us</a>
                    <a href="#products" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">Loan Products</a>
                    <a href="#faq" class="landing-nav-link" data-bs-toggle="collapse" data-bs-target="#mobileNav">FAQ</a>
                    <a href="#contact" class="landing-nav-link text-primary fw-semibold" data-bs-toggle="collapse" data-bs-target="#mobileNav">Contact Us &rarr;</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- 2. HERO SECTION -->
    <section id="home" class="hero-section">
        <div class="hero-mesh-blob-1"></div>
        <div class="hero-mesh-blob-2"></div>
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-7 text-center text-lg-start">
                    <div class="hero-badge-pill mb-4">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Chartered Accountant & Financial Advisory
                    </div>

                    <h1 class="hero-title mb-3">
                        Strategic Financial Advisory &amp; Seamless Loan Syndication
                    </h1>

                    <p class="hero-lead mb-4">
                        Empowering corporate businesses and MSMEs with end-to-end Chartered Accountant solutions, bank-grade CMA data preparation, comprehensive Detailed Project Reports (DPR), and high-approval loan facilitation.
                    </p>

                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mb-5">
                        <a href="#contact" class="btn btn-brand-primary btn-lg">
                            Get Free Consultation
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="ms-1 d-inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                        <a href="#services" class="btn btn-brand-secondary btn-lg">
                            Explore Services
                        </a>
                    </div>

                    <!-- Small Trust Indicators -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-4 pt-2 border-top border-white border-opacity-15 small text-indigo-100">
                        <span class="d-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="currentColor" class="text-info" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Loan Assistance
                        </span>
                        <span class="d-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="currentColor" class="text-info" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Documentation Support
                        </span>
                        <span class="d-flex align-items-center gap-2">
                            <svg width="18" height="18" fill="currentColor" class="text-info" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Professional Guidance
                        </span>
                    </div>
                </div>

                <!-- Right Visual Glass Card -->
                <div class="col-lg-5">
                    <div class="hero-glass-panel">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-2 py-1">Bank Syndication Active</span>
                                <h4 class="h5 fw-bold text-white mt-2 mb-0">Financial Consultation Desk</h4>
                            </div>
                            <div class="rounded-circle bg-white bg-opacity-10 p-2 text-white">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="hero-metric-item">
                                    <div class="h3 fw-bold text-white mb-0">₹100Cr+</div>
                                    <div class="small" style="color: #a5b4fc">Loans Facilitated</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-metric-item">
                                    <div class="h3 fw-bold text-white mb-0">99.4%</div>
                                    <div class="small" style="color: #a5b4fc">Approval Rate</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-metric-item">
                                    <div class="h3 fw-bold text-white mb-0">500+</div>
                                    <div class="small" style="color: #a5b4fc">Corporate Clients</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="hero-metric-item">
                                    <div class="h3 fw-bold text-white mb-0">100%</div>
                                    <div class="small" style="color: #a5b4fc">Compliance Track</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.07); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <div class="d-flex align-items-center justify-content-between text-white small">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                                    Accepting New Applications
                                </span>
                                <a href="#contact" class="text-white text-decoration-none fw-semibold">Inquire Now &rarr;</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. TRUST / STATISTICS SECTION (ANIMATED COUNTERS) -->
    <div class="container">
        <div class="stats-bar-card p-4 p-md-5 reveal-on-scroll">
            <div class="row g-4 text-center">
                @foreach ($statsConfig as $stat)
                    <div class="col-6 col-lg-3">
                        <div class="p-2">
                            <div class="stat-counter-number">
                                <span class="counter-val" data-target="{{ $stat['number'] }}">0</span>{{ $stat['suffix'] }}
                            </div>
                            <div class="fw-bold mt-2" style="color: var(--text-heading); font-size: 1.05rem;">{{ $stat['label'] }}</div>
                            <div class="small text-muted">{{ $stat['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4. ABOUT SECTION -->
    <section id="about" class="py-5 my-4" style="background-color: #ffffff;">
        <div class="container py-4">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 reveal-on-scroll">
                    <span class="section-tag">About Our Advisory</span>
                    <h2 class="section-title mb-3">Your Strategic Partner in Financial Engineering &amp; Credit Solutions</h2>
                    <p style="color: var(--text-muted); line-height: 1.75; font-size: 1.05rem;">
                        We are a premier Chartered Accountant and financial syndication practice dedicated to helping businesses structure their finances, prepare flawless credit proposals, and secure funding from top banking institutions.
                    </p>
                    <p style="color: var(--text-muted); line-height: 1.75;">
                        From analyzing working capital requirements to drafting bank-compliant CMA data, project reports (DPR), and navigating government subsidy schemes, our multi-disciplinary team ensures you achieve optimal capital efficiency.
                    </p>

                    <div class="row g-3 mt-2">
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.925rem">Certified CA Advisory</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.925rem">Bankable CMA &amp; DPR</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.925rem">Multi-Bank Network</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="d-flex align-items-center gap-2 p-2 rounded bg-light border">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-1 d-flex">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <span class="fw-semibold text-dark" style="font-size: 0.925rem">Fast Disbursement</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 reveal-on-scroll">
                    <div class="p-4 p-md-5 rounded-4" style="background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%); border: 1px solid #cbd5e1;">
                        <h4 class="fw-bold mb-4 font-heading" style="color: var(--text-heading)">Our Execution Methodology</h4>

                        <div class="timeline-item">
                            <div class="timeline-marker">1</div>
                            <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Financial Feasibility Analysis</h6>
                            <p class="small text-muted mb-0">Detailed review of historical ITRs, balance sheets, turnover, and debt servicing capabilities.</p>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">2</div>
                            <h6 class="fw-bold mb-1" style="color: var(--text-heading)">CMA Data &amp; Project Formulation</h6>
                            <p class="small text-muted mb-0">Structuring MPBF calculations, cash flow models, and bank-ready Detailed Project Reports (DPR).</p>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">3</div>
                            <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Bank Syndication &amp; Valuation</h6>
                            <p class="small text-muted mb-0">Liaison with PSU and private banks, coordinating inspections, stock statements, and legal searches.</p>
                        </div>

                        <div class="timeline-item">
                            <div class="timeline-marker">4</div>
                            <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Sanction &amp; Fund Disbursement</h6>
                            <p class="small text-muted mb-0">Ensuring swift documentation, compliance clearance, and direct credit limit disbursement.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. SERVICES SECTION -->
    <section id="services" class="py-5" style="background-color: #f8fafc;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">Core Offerings</span>
                <h2 class="section-title">Comprehensive Financial &amp; Loan Services</h2>
                <p class="section-subtitle">Delivering high-accuracy financial solutions built to satisfy stringent banking norms and regulatory guidelines.</p>
            </div>

            <div class="row g-4">
                @foreach ($services as $service)
                    <div class="col-md-6 col-lg-3 reveal-on-scroll">
                        <div class="premium-card">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="card-icon-box">
                                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        {!! $service['icon'] !!}
                                    </svg>
                                </div>
                                <span class="product-pill-badge">{{ $service['badge'] }}</span>
                            </div>
                            <h4 class="h5 fw-bold mb-2 font-heading" style="color: var(--text-heading)">{{ $service['title'] }}</h4>
                            <p class="small text-muted mb-4" style="line-height: 1.6; flex-grow: 1;">
                                {{ $service['desc'] }}
                            </p>
                            <a href="#contact" class="fw-semibold small text-primary text-decoration-none d-inline-flex align-items-center gap-1">
                                Apply for this &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 6. LOAN / SERVICE HIGHLIGHT (STEP INDICATORS) -->
    <section id="process" class="py-5" style="background-color: #ffffff;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">End-to-End Assistance</span>
                <h2 class="section-title">From Requirement to Disbursement</h2>
                <p class="section-subtitle">We guide you through every milestone of your loan application with complete transparency and dedication.</p>
            </div>

            <div class="process-steps-wrap py-3 d-none d-lg-block reveal-on-scroll">
                <div class="process-steps-line"></div>
                <div class="row text-center position-relative">
                    <div class="col step-node">
                        <div class="step-node-bubble">1</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Requirement</h6>
                        <p class="small text-muted">Initial Consultation</p>
                    </div>
                    <div class="col step-node">
                        <div class="step-node-bubble">2</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Documentation</h6>
                        <p class="small text-muted">CMA &amp; Financials</p>
                    </div>
                    <div class="col step-node">
                        <div class="step-node-bubble">3</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Application</h6>
                        <p class="small text-muted">Bank Submission</p>
                    </div>
                    <div class="col step-node">
                        <div class="step-node-bubble">4</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Processing</h6>
                        <p class="small text-muted">Inspection &amp; Review</p>
                    </div>
                    <div class="col step-node">
                        <div class="step-node-bubble">5</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Sanction</h6>
                        <p class="small text-muted">Letter Issued</p>
                    </div>
                    <div class="col step-node">
                        <div class="step-node-bubble">6</div>
                        <div class="step-node-bubble bg-success text-white border-success">✓</div>
                        <h6 class="fw-bold mb-1" style="color: var(--text-heading)">Disbursement</h6>
                        <p class="small text-muted">Funds Released</p>
                    </div>
                </div>
            </div>

            <!-- Mobile Process View -->
            <div class="d-lg-none">
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="badge bg-primary rounded-circle mb-2">1</span>
                            <div class="fw-bold text-dark small">Requirement Analysis</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="badge bg-primary rounded-circle mb-2">2</span>
                            <div class="fw-bold text-dark small">Documentation &amp; CMA</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="badge bg-primary rounded-circle mb-2">3</span>
                            <div class="fw-bold text-dark small">Application Filing</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="badge bg-primary rounded-circle mb-2">4</span>
                            <div class="fw-bold text-dark small">Bank Processing</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light border text-center">
                            <span class="badge bg-primary rounded-circle mb-2">5</span>
                            <div class="fw-bold text-dark small">Sanction Letter</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-success text-white border text-center">
                            <span class="badge bg-white text-success rounded-circle mb-2">6</span>
                            <div class="fw-bold small">Fund Disbursement</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. WHY CHOOSE US -->
    <section id="why-us" class="py-5" style="background-color: #f8fafc;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">The Competitive Advantage</span>
                <h2 class="section-title">Why Choose Our Financial Consultancy</h2>
                <p class="section-subtitle">We bridge the gap between borrowers and banking institutions through technical accuracy and dedicated relationship management.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Personalized Guidance</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            Every business model is unique. We tailor loan structures, repayment tenures, and banking facilities to match your cash cycle.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Documentation Assistance</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            From balance sheet alignment to bank CMA and DPR generation, our CA team handles all complex compliance paperwork.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Multiple Loan Options</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            Direct access to leading PSU banks, private commercial lenders, and NBFCs ensuring competitive interest rates and conditions.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Application Support</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            Active follow-up with bank credit committees, field valuation officers, and legal teams to expedite final sanction.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Transparent Process</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            Zero hidden terms, clear consultation milestones, and realistic timeline estimations from day one.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 reveal-on-scroll">
                    <div class="benefit-card">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 text-primary p-2">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                            </div>
                            <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">Dedicated Assistance</h5>
                        </div>
                        <p class="small text-muted mb-0" style="line-height: 1.65;">
                            A dedicated loan advisor and CA associate assigned to your case for instant updates and queries.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. LOAN PRODUCTS / CATEGORIES (DYNAMIC + CURATED) -->
    <section id="products" class="py-5" style="background-color: #ffffff;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">Loan Categories</span>
                <h2 class="section-title">Available Loan Products &amp; Credit Lines</h2>
                <p class="section-subtitle">Explore our diverse financial product portfolios structured to fulfill short-term and long-term capital needs.</p>
            </div>

            @php
                // Display active products from database if available, otherwise enriched default array
                $displayProducts = isset($loanProducts) && $loanProducts->isNotEmpty()
                    ? $loanProducts
                    : collect([
                        (object)['name' => 'Business Loan', 'description' => 'Unsecured & secured funding for business operations and inventory.'],
                        (object)['name' => 'Working Capital (CC / OD)', 'description' => 'Revolving cash credit against stock, debtor receivables, and assets.'],
                        (object)['name' => 'Term Loan', 'description' => 'Long term capex funding for commercial premises and machinery.'],
                        (object)['name' => 'Home Loan', 'description' => 'Housing finance with minimum documentation and lowest interest rates.'],
                        (object)['name' => 'Loan Against Property (LAP)', 'description' => 'Unlock high value mortgage liquidity against your residential or commercial property.'],
                        (object)['name' => 'Government Subsidy Loan', 'description' => 'Linked credit schemes with government capital subsidies and interest subvention.'],
                    ]);
            @endphp

            <div class="row g-4">
                @foreach ($displayProducts as $product)
                    <div class="col-md-6 col-lg-4 reveal-on-scroll">
                        <div class="p-4 rounded-4 border bg-light h-100 d-flex flex-column justify-content-between transition-all">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="fw-bold mb-0 font-heading" style="color: var(--text-heading)">{{ $product->name }}</h5>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">Active Facility</span>
                                </div>
                                <p class="small text-muted mb-3" style="line-height: 1.6;">
                                    {{ $product->description ?: 'Comprehensive bank facility tailored for enterprise growth with structured repayment terms.' }}
                                </p>
                            </div>
                            <div class="pt-3 border-top d-flex align-items-center justify-content-between">
                                <span class="small text-muted">Up to ₹25+ Crore</span>
                                <a href="#contact" class="btn btn-sm btn-outline-primary fw-semibold">
                                    Apply Now
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 9. FULL-WIDTH CTA SECTION -->
    <div class="container py-5">
        <div class="full-cta-section text-center position-relative reveal-on-scroll">
            <div class="position-relative" style="z-index: 2;">
                <span class="badge bg-white bg-opacity-20 text-white px-3 py-2 rounded-pill mb-3 fw-semibold">Instant Loan Evaluation</span>
                <h2 class="display-5 fw-extrabold text-white mb-3 font-heading" style="letter-spacing: -0.02em;">
                    Need Help With Your Loan or Financial Requirement?
                </h2>
                <p class="lead text-indigo-100 mb-4 mx-auto" style="max-width: 650px; font-size: 1.15rem; color: #c7d2fe;">
                    Get expert Chartered Accountant consultation, bankable project reports, and seamless loan syndication with maximum approval probability.
                </p>
                <a href="#contact" class="btn btn-light btn-lg px-4 py-3 fw-bold text-primary shadow">
                    Get Free Consultation
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="ms-1 d-inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- 10. FAQ ACCORDION SECTION -->
    <section id="faq" class="py-5" style="background-color: #ffffff;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">Frequently Asked Questions</span>
                <h2 class="section-title">Common Queries &amp; Answers</h2>
                <p class="section-subtitle">Find immediate answers regarding loan documentation, CMA preparation, bank timelines, and our advisory workflow.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion" id="landingFaqAccordion">
                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                    What documents are required for business &amp; MSME loans?
                                </button>
                            </h2>
                            <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    Standard requirements include: 3 years of audited balance sheets &amp; ITRs, 12 months bank statements, GST returns (GSTR-3B &amp; GSTR-1), business KYC (PAN, Aadhaar, Udyam Registration, GST Certificate), and constitution documents (Partnership Deed, MOA/AOA). We assist in organizing and auditing all files before bank submission.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                    Which loan products and banking credit facilities do you assist with?
                                </button>
                            </h2>
                            <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    We assist with Unsecured Business Loans, Working Capital facilities (Cash Credit &amp; Overdraft), Term Loans, Machinery / Equipment financing, Loan Against Property (LAP), Home Loans, Letter of Credit (LC), and Bank Guarantees (BG) with leading nationalized and private banks.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                    Can you help with CMA Data and Detailed Project Report (DPR) preparation?
                                </button>
                            </h2>
                            <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    Yes, absolutely. We prepare institutional-grade Credit Monitoring Arrangement (CMA) models with detailed multi-year projections, MPBF calculations, DSCR analysis, and bank-compliant Detailed Project Reports (DPR) tailored specifically for your target lenders.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                    How does the initial consultation and feasibility review work?
                                </button>
                            </h2>
                            <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    You submit your details through our Contact Form or contact our desk. Our Chartered Accountants evaluate your financials, turnover, and credit eligibility to advise on the most suitable loan facility and lender with no upfront obligations.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                    How long does the end-to-end loan approval and disbursement take?
                                </button>
                            </h2>
                            <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    Unsecured business loans can be sanctioned within 3 to 7 business days. Secured facilities like Cash Credit, LAP, and Machinery Term Loans typically require 10 to 20 business days depending on property valuation, title search, and bank inspection timelines.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item reveal-on-scroll">
                            <h2 class="accordion-header" id="faqHeading6">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">
                                    Do you assist with Government Subsidies and interest subvention schemes?
                                </button>
                            </h2>
                            <div id="faqCollapse6" class="accordion-collapse collapse" aria-labelledby="faqHeading6" data-bs-parent="#landingFaqAccordion">
                                <div class="accordion-body">
                                    Yes. We provide complete advisory and application filing for Central and State government capital investment subsidies, textile/manufacturing incentives, MSME schemes, and interest subvention benefits.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 11. CONTACT SECTION -->
    <section id="contact" class="py-5" style="background-color: #f1f5f9;">
        <div class="container py-4">
            <div class="text-center mb-5 reveal-on-scroll">
                <span class="section-tag">Get in Touch</span>
                <h2 class="section-title">Request a Consultation</h2>
                <p class="section-subtitle">Fill out the form below and our Chartered Accountant financial advisory team will connect with you promptly.</p>
            </div>

            <div class="row g-4 align-items-stretch">
                <!-- Contact Information Card -->
                <div class="col-lg-5 reveal-on-scroll">
                    <div class="contact-sidebar-card d-flex flex-column justify-content-between">
                        <div>
                            <h3 class="h4 fw-bold text-white mb-2 font-heading">Connect with Our Experts</h3>
                            <p class="small mb-4" style="color: #c7d2fe; line-height: 1.6;">
                                Discuss your loan requirements, CMA preparations, or financial advisory needs directly with our consultants.
                            </p>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="contact-icon-pill">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </div>
                                <div>
                                    <div class="small fw-semibold text-uppercase" style="color: #a5b4fc">Phone Support</div>
                                    <div class="fw-semibold text-white mt-1">
                                        <a href="tel:+917490956914" class="text-white text-decoration-none">+91 74909 56914</a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="contact-icon-pill">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <div class="small fw-semibold text-uppercase" style="color: #a5b4fc">Email Advisory</div>
                                    <div class="fw-semibold text-white mt-1">
                                        <a href="mailto:jbworkshop01@gmail.com" class="text-white text-decoration-none">jbworkshop01@gmail.com</a>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="contact-icon-pill">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <div class="small fw-semibold text-uppercase" style="color: #a5b4fc">Office Timing</div>
                                    <div class="fw-semibold text-white mt-1">Mon - Sat: 9:30 AM - 7:30 PM</div>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3 mb-4">
                                <div class="contact-icon-pill">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                </div>
                                <div>
                                    <div class="small fw-semibold text-uppercase" style="color: #a5b4fc">Office Address</div>
                                    <div class="fw-semibold text-white mt-1" style="line-height: 1.5;">Shop no 121-122, Man Mandir Complex, Opp Bus Station, Mahuva Road, Savarkundla, Amreli- 364515</div>
                                </div>
                            </div>

                        </div>

                        <div class="mt-4 pt-3 border-top border-white border-opacity-10 small" style="color: #a5b4fc">
                            🔒 Complete confidentiality for personal, taxation, and company financial documents.
                        </div>
                    </div>
                </div>

                <!-- Contact Submission Form -->
                <div class="col-lg-7 reveal-on-scroll">
                    <div class="contact-form-card">
                        <h3 class="h4 fw-bold mb-3 font-heading" style="color: var(--text-heading)">Send Your Loan Enquiry</h3>

                        @if (session('contact_success'))
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4" role="alert">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <div>{{ session('contact_success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger mb-4" role="alert">
                                <div class="fw-semibold mb-1">Please correct the following:</div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('contact-enquiries.store') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="full_name" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        Full Name <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="full_name"
                                        name="full_name"
                                        value="{{ old('full_name') }}"
                                        class="form-control form-control-lg @error('full_name') is-invalid @enderror"
                                        placeholder="Full Name"
                                        required
                                        maxlength="150"
                                        style="font-size: 0.95rem;"
                                    >
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="mobile" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        Mobile Number <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="tel"
                                        id="mobile"
                                        name="mobile"
                                        value="{{ old('mobile') }}"
                                        class="form-control form-control-lg @error('mobile') is-invalid @enderror"
                                        placeholder="Mobile Number"
                                        required
                                        maxlength="25"
                                        style="font-size: 0.95rem;"
                                    >
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        Email Address
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                                        placeholder="Email Address"
                                        maxlength="255"
                                        style="font-size: 0.95rem;"
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="city" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        City
                                    </label>
                                    <input
                                        type="text"
                                        id="city"
                                        name="city"
                                        value="{{ old('city') }}"
                                        class="form-control form-control-lg @error('city') is-invalid @enderror"
                                        placeholder="City"
                                        maxlength="100"
                                        style="font-size: 0.95rem;"
                                    >
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="loan_requirement" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        Loan / Service Requirement
                                    </label>
                                    <input
                                        type="text"
                                        id="loan_requirement"
                                        name="loan_requirement"
                                        value="{{ old('loan_requirement') }}"
                                        class="form-control form-control-lg @error('loan_requirement') is-invalid @enderror"
                                        placeholder="Loan / Service Requirement"
                                        maxlength="150"
                                        style="font-size: 0.95rem;"
                                    >
                                    @error('loan_requirement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label fw-semibold" style="font-size: 0.9rem; color: #334155">
                                        Message / Requirement Details
                                    </label>
                                    <textarea
                                        id="message"
                                        name="message"
                                        rows="3"
                                        class="form-control form-control-lg @error('message') is-invalid @enderror"
                                        placeholder="Message / Requirement Details"
                                        style="font-size: 0.95rem;"
                                    >{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-brand-primary w-100 py-3 fw-bold" style="font-size: 1.05rem;">
                                        Submit Enquiry
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="ms-1 d-inline-block"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 12. FOOTER -->
    <footer class="py-5" style="background-color: #0f172a; color: #94a3b8; border-top: 1px solid #1e293b;">
        <div class="container">
            <div class="row g-4 justify-content-between mb-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="jb-brand-mark rounded-circle d-flex align-items-center justify-content-center" style="width:2.2rem;height:2.2rem;background:var(--brand-primary);color:#fff;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.95"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </span>
                        <span class="fw-bold text-white fs-5 font-heading">{{ config('app.name', 'JB Work Shop') }}</span>
                    </div>
                    <p class="small mb-3" style="line-height: 1.7; max-width: 440px; color: #94a3b8;">
                        Premier Chartered Accountant services, strategic loan syndication, bankable CMA reports, Detailed Project Reports (DPR), and tax compliance advisory empowering enterprises across India.
                    </p>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <h6 class="text-white fw-bold mb-3 font-heading">Quick Navigation</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="#home" class="text-decoration-none" style="color: #94a3b8;">Home</a></li>
                        <li><a href="#about" class="text-decoration-none" style="color: #94a3b8;">About Advisory</a></li>
                        <li><a href="#services" class="text-decoration-none" style="color: #94a3b8;">Our Services</a></li>
                        <li><a href="#process" class="text-decoration-none" style="color: #94a3b8;">How It Works</a></li>
                        <li><a href="#why-us" class="text-decoration-none" style="color: #94a3b8;">Why Choose Us</a></li>
                        <li><a href="#faq" class="text-decoration-none" style="color: #94a3b8;">FAQ</a></li>
                        <li><a href="#contact" class="text-decoration-none" style="color: #94a3b8;">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-sm-6 col-lg-3">
                    <h6 class="text-white fw-bold mb-3 font-heading">Services &amp; Solutions</h6>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><span style="color: #94a3b8;">Business Loan Syndication</span></li>
                        <li><span style="color: #94a3b8;">Cash Credit &amp; Overdraft</span></li>
                        <li><span style="color: #94a3b8;">CMA Data &amp; Projections</span></li>
                        <li><span style="color: #94a3b8;">Detailed Project Reports (DPR)</span></li>
                        <li><span style="color: #94a3b8;">Government Subsidies</span></li>
                        <li><span style="color: #94a3b8;">CA &amp; Tax Audit Compliance</span></li>
                    </ul>
                </div>
            </div>

            <div class="pt-4 border-top border-secondary border-opacity-25 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small" style="color: #64748b;">
                <div>&copy; {{ date('Y') }} {{ config('app.name', 'JB Work Shop') }}. All rights reserved.</div>
                <div>Chartered Accountant &amp; Financial Consultancy</div>
            </div>
        </div>
    </footer>

    <!-- 13. FLOATING WIDGETS -->
    <a href="#contact" class="btn btn-brand-primary floating-cta-btn d-none d-md-flex text-decoration-none">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.818-.818 5.97 5.97 0 011.057-3.035C4.306 15.65 3 13.935 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
        <span>Get Consultation</span>
    </a>

    <a href="#home" id="backToTopBtn" class="back-to-top-btn" title="Back to top">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/></svg>
    </a>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Script: Animated Counters, Scroll Reveal, Sticky Header, Back to Top -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Sticky Header Shadow on Scroll
            const header = document.querySelector('.landing-header');
            const backToTopBtn = document.getElementById('backToTopBtn');

            window.addEventListener('scroll', () => {
                if (window.scrollY > 40) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                if (window.scrollY > 400) {
                    backToTopBtn.classList.add('visible');
                } else {
                    backToTopBtn.classList.remove('visible');
                }
            }, { passive: true });

            // 2. Animated Counter via IntersectionObserver
            const counters = document.querySelectorAll('.counter-val');
            let hasAnimatedCounters = false;

            const animateCounter = (counter) => {
                const target = +counter.getAttribute('data-target');
                const duration = 1600;
                const startTime = performance.now();

                const updateCount = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    // easeOutExpo
                    const easeProgress = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
                    const currentCount = Math.floor(easeProgress * target);

                    counter.innerText = currentCount;

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    } else {
                        counter.innerText = target;
                    }
                };

                requestAnimationFrame(updateCount);
            };

            const counterObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !hasAnimatedCounters) {
                        counters.forEach(counter => animateCounter(counter));
                        hasAnimatedCounters = true;
                        observer.disconnect();
                    }
                });
            }, { threshold: 0.2 });

            const statsSection = document.querySelector('.stats-bar-card');
            if (statsSection) {
                counterObserver.observe(statsSection);
            }

            // 3. Scroll Reveal Animation for Sections
            const revealElements = document.querySelectorAll('.reveal-on-scroll');
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            revealElements.forEach(el => revealObserver.observe(el));
        });
    </script>
</body>
</html>
