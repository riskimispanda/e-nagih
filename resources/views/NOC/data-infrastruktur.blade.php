@extends('layouts.contentNavbarLayout')
@section('title', 'Data Infrastruktur FTTH')

@section('page-style')
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: {
            preflight: false
        }
    };
</script>
<style>
    /* ==========================================================================
       MODERN CLEAN DESIGN SYSTEM & CUSTOM SCROLLBARS
       ========================================================================== */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.6);
        border-radius: 99px;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 99px;
    }
    .custom-scroll::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Stats Quick Cards */
    .stat-badge-card {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
    }
    .stat-badge-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
    }

    /* Tab Buttons Alignment & Styling */
    .node-tab-container {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 4px;
        background: #f1f5f9;
        padding: 4px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .node-tab-btn {
        padding: 8px 6px;
        text-align: center;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 600;
        color: #475569;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        cursor: pointer;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 5px !important;
        line-height: 1 !important;
        width: 100%;
        outline: none !important;
    }

    .node-tab-btn i {
        font-size: 14px !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 !important;
    }

    .node-tab-btn span {
        line-height: 1 !important;
        display: inline-block !important;
    }

    .node-tab-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .node-tab-btn.active {
        background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
        color: #ffffff !important;
        border-color: #1d4ed8 !important;
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35) !important;
        font-weight: 700 !important;
    }

    /* Banner Alignment */
    .type-banner-box {
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 11.5px;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
        line-height: 1.4;
    }

    .type-banner-box i {
        font-size: 18px;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Input Field Styling */
    .form-input-custom {
        width: 100%;
        padding: 8px 12px;
        font-size: 12px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        background-color: #ffffff;
        color: #1e293b;
        outline: none;
        transition: all 0.15s ease;
    }
    .form-input-custom:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }
    .form-input-custom::placeholder {
        color: #94a3b8;
    }

    /* ==========================================================================
       TOPOLOGY CANVAS & GRID BACKGROUND
       ========================================================================== */
    .topology-wrapper {
        min-height: 580px;
        max-height: 820px;
        overflow: auto;
        padding: 22px 18px;
        background-color: #f8fafc;
        background-image: radial-gradient(circle at 1px 1px, #cbd5e1 1.1px, transparent 0);
        background-size: 20px 20px;
        border-radius: 0 0 16px 16px;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    /* ==========================================================================
       MODERN MINIMALIST TREE NODE CARD & HIERARCHY
       ========================================================================== */
    .node-card-wrapper {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        position: relative;
        width: 100%;
    }

    .node-box {
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        width: auto;
        min-width: 270px;
        max-width: 460px;
        padding: 7px 11px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        z-index: 2;
    }

    .node-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
        z-index: 10;
    }

    /* Clean Left Border Type Accents */
    .node-server { border-left: 3.5px solid #64748b; margin-bottom: 0.5rem; }
    .node-olt { border-left: 3.5px solid #2563eb; }
    .node-odc { border-left: 3.5px solid #7c3aed; }
    .node-splitter { border-left: 3.5px solid #d97706; background: #fffdfa; }
    .node-odp { border-left: 3.5px solid #16a34a; }

    /* Compact Node Icon */
    .node-icon-bg {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        font-size: 15px;
        flex-shrink: 0;
    }
    .node-server .node-icon-bg { background: #f1f5f9; color: #475569; }
    .node-olt .node-icon-bg { background: #eff6ff; color: #2563eb; }
    .node-odc .node-icon-bg { background: #f5f3ff; color: #7c3aed; }
    .node-splitter .node-icon-bg { background: #fef3c7; color: #d97706; }
    .node-odp .node-icon-bg { background: #ecfdf5; color: #16a34a; }

    /* Node Text & Metadata */
    .node-title-text {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 220px;
    }
    .node-type-pill {
        display: inline-flex;
        align-items: center;
        padding: 1px 5px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .node-meta-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        font-size: 10.5px;
        color: #64748b;
        margin-top: 2px;
        line-height: 1.2;
    }
    .node-meta-item {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    /* Minimalist Action Toolbar in Node */
    .node-action-group {
        display: flex;
        align-items: center;
        gap: 3px;
        opacity: 0.35;
        transition: opacity 0.15s ease;
        flex-shrink: 0;
    }
    .node-box:hover .node-action-group {
        opacity: 1;
    }

    .node-btn-action {
        width: 24px;
        height: 24px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 12px;
        color: #64748b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
        outline: none;
    }
    .node-btn-action:hover {
        background: #334155;
        color: #ffffff;
        border-color: #334155;
    }
    .node-btn-action.btn-add {
        color: #2563eb;
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .node-btn-action.btn-add:hover {
        background: #2563eb;
        color: #ffffff;
        border-color: #2563eb;
    }
    .node-btn-action.btn-splitter {
        color: #d97706;
        background: #fffbeb;
        border-color: #fde68a;
    }
    .node-btn-action.btn-splitter:hover {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
    }
    .node-btn-action.btn-delete:hover {
        background: #e11d48;
        color: #ffffff;
        border-color: #e11d48;
    }

    /* Smooth Tree Connectors */
    .children-container {
        margin-left: 18px;
        padding-left: 14px;
        border-left: 1.5px solid #cbd5e1;
        margin-top: 5px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: all 0.2s ease;
        position: relative;
    }
    .children-container.collapsed {
        display: none !important;
    }

    .child-branch {
        position: relative;
        padding: 2px 0 2px 8px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .child-branch::before {
        content: '';
        position: absolute;
        left: -14px;
        top: 17px;
        width: 12px;
        border-top: 1.5px solid #cbd5e1;
        border-bottom-left-radius: 4px;
    }

    /* Port Badge */
    .port-badge {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        font-size: 9.5px;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 700;
        color: #92400e;
        background: #fef3c7;
        border: 1px solid #fde68a;
        padding: 1.5px 6px;
        border-radius: 5px;
    }

    /* Splitter Empty Ports Compact Hub */
    /* Splitter Empty Ports Compact Hub */
    .splitter-empty-hub {
        display: inline-flex;
        flex-direction: column;
        gap: 5px;
        background: #fffdf5;
        border: 1px dashed #fcd34d;
        border-radius: 9px;
        padding: 6px 10px;
        max-width: 520px;
    }
    .splitter-empty-title {
        font-size: 10px;
        font-weight: 700;
        color: #b45309;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .splitter-empty-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    .empty-port-group {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        background: #ffffff;
        border: 1px solid #fde68a;
        border-radius: 6px;
        padding: 2px 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    .empty-port-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 700;
        color: #92400e;
        padding: 1px 4px;
        background: #fef3c7;
        border-radius: 4px;
    }
    .empty-port-subbtn {
        font-size: 9px;
        font-weight: 600;
        padding: 2px 5px;
        border-radius: 4px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.12s ease;
        display: inline-flex;
        align-items: center;
        gap: 2px;
        line-height: 1;
        outline: none;
    }
    .empty-port-subbtn.btn-sub-splitter {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }
    .empty-port-subbtn.btn-sub-splitter:hover {
        background: #d97706;
        color: #ffffff;
        border-color: #d97706;
    }
    .empty-port-subbtn.btn-sub-odp {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .empty-port-subbtn.btn-sub-odp:hover {
        background: #16a34a;
        color: #ffffff;
        border-color: #16a34a;
    }
    .empty-port-subbtn.btn-sub-odc {
        background: #faf5ff;
        color: #7e22ce;
        border-color: #e9d5ff;
    }
    .empty-port-subbtn.btn-sub-odc:hover {
        background: #9333ea;
        color: #ffffff;
        border-color: #9333ea;
    }
    .empty-port-btn {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 5px;
        background: #ffffff;
        color: #92400e;
        border: 1px solid #fde68a;
        cursor: pointer;
        transition: all 0.12s ease;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }
    .empty-port-btn:hover {
        background: #fef3c7;
        border-color: #d97706;
        color: #78350f;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(217, 119, 6, 0.15);
    }

    /* OLT PON Port Hub & Grid */
    .olt-pon-hub {
        background: #f0fdf4;
        border: 1px dashed #86efac;
        border-radius: 8px;
        padding: 5px 8px;
        margin-top: 5px;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .olt-pon-title {
        font-size: 10px;
        font-weight: 700;
        color: #15803d;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .olt-pon-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 2px;
    }
    .pon-port-chip {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 9.5px;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 5px;
        transition: all 0.12s ease;
        display: inline-flex;
        align-items: center;
        gap: 3px;
        text-decoration: none;
        border: none;
        outline: none;
    }
    .pon-port-chip.filled {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
        cursor: pointer;
    }
    .pon-port-chip.filled:hover {
        background: #bfdbfe;
        border-color: #3b82f6;
        color: #1d4ed8;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(37, 99, 235, 0.2);
    }
    .pon-port-chip.has-splitter {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fcd34d;
        cursor: pointer;
    }
    .pon-port-chip.has-splitter:hover {
        background: #fde68a;
        border-color: #f59e0b;
        color: #78350f;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(217, 119, 6, 0.2);
    }
    .pon-port-chip.empty {
        background: #ffffff;
        color: #166534;
        border: 1px solid #bbf7d0;
        cursor: pointer;
    }
    .pon-port-chip.empty:hover {
        background: #dcfce7;
        border-color: #22c55e;
        color: #14532d;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(34, 197, 94, 0.15);
    }

    /* Search match highlight */
    .node-box.search-match {
        outline: 2px solid #2563eb !important;
        background-color: #eff6ff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18) !important;
    }

    /* Search Floating Autocomplete */
    .search-dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 4px;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        max-height: 320px;
        overflow-y: auto;
        z-index: 1050;
        padding: 6px;
    }
    .search-result-item {
        padding: 7px 10px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        flex-direction: column;
        gap: 2px;
        border: 1px solid transparent;
    }
    .search-result-item:hover, .search-result-item.active {
        background: #f8fafc;
        border-color: #e2e8f0;
    }
    .search-result-item:hover .search-item-title {
        color: #2563eb;
    }
    .search-item-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }
    .search-item-title {
        font-size: 11.5px;
        font-weight: 700;
        color: #1e293b;
    }
    .search-item-path {
        font-size: 10px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Node Highlight Pulse Animation */
    @keyframes nodePulseGlow {
        0% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.8);
            border-color: #2563eb;
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 0 12px rgba(37, 99, 235, 0.25);
            border-color: #1d4ed8;
            transform: scale(1.04);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
            border-color: #2563eb;
            transform: scale(1);
        }
    }
    .node-highlight-pulse {
        animation: nodePulseGlow 0.9s ease-in-out 4 !important;
        border-color: #2563eb !important;
        outline: 3px solid #3b82f6 !important;
        outline-offset: 3px !important;
        z-index: 50 !important;
    }

    /* Toggle Collapse Button */
    .toggle-btn {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
        min-height: 20px !important;
        border-radius: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #f1f5f9 !important;
        color: #64748b !important;
        border: 1px solid #e2e8f0 !important;
        cursor: pointer !important;
        transition: all 0.15s !important;
        flex-shrink: 0 !important;
        padding: 0 !important;
        outline: none !important;
    }
    .toggle-btn:hover {
        background: #334155 !important;
        color: #ffffff !important;
        border-color: #334155 !important;
    }
    .toggle-btn i {
        font-size: 14px !important;
        line-height: 1 !important;
    }

    /* ==========================================================================
       NATIVE HTML5 <dialog> MODAL (TOP LAYER: TRUE)
       ========================================================================== */
    dialog#modal-dialog {
        padding: 0;
        margin: auto;
        border: none;
        border-radius: 18px;
        background: transparent;
        max-width: 780px;
        width: calc(100% - 32px);
        max-height: 90vh;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35);
        overflow: visible;
        color: inherit;
        outline: none;
        /* Smooth entry animation */
        opacity: 0;
        transform: scale(0.95) translateY(10px);
        transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), display 0.22s allow-discrete, overlay 0.22s allow-discrete;
    }

    dialog#modal-dialog[open] {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    @starting-style {
        dialog#modal-dialog[open] {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }
    }

    dialog#modal-dialog::backdrop {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        opacity: 0;
        transition: opacity 0.22s ease, display 0.22s allow-discrete, overlay 0.22s allow-discrete;
    }

    dialog#modal-dialog[open]::backdrop {
        opacity: 1;
    }

    @starting-style {
        dialog#modal-dialog[open]::backdrop {
            opacity: 0;
        }
    }

    /* Add-Node Dialog (Centered Modal for "Tambah Node") */
    dialog#add-node-dialog {
        padding: 0;
        margin: auto;
        border: none;
        border-radius: 18px;
        background: transparent;
        max-width: 780px;
        width: calc(100% - 32px);
        max-height: 90vh;
        box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35);
        overflow: visible;
        color: inherit;
        outline: none;
        opacity: 0;
        transform: scale(0.95) translateY(10px);
        transition: opacity 0.22s ease, transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), display 0.22s allow-discrete, overlay 0.22s allow-discrete;
    }

    dialog#add-node-dialog[open] {
        opacity: 1;
        transform: scale(1) translateY(0);
    }

    @starting-style {
        dialog#add-node-dialog[open] {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
        }
    }

    dialog#add-node-dialog::backdrop {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        opacity: 0;
        transition: opacity 0.22s ease, display 0.22s allow-discrete, overlay 0.22s allow-discrete;
    }

    dialog#add-node-dialog[open]::backdrop {
        opacity: 1;
    }

    @starting-style {
        dialog#add-node-dialog[open]::backdrop {
            opacity: 0;
        }
    }

    #add-node-panel,
    #modal-panel {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    #infraForm,
    #drawerDynamicForm {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .modal-header {
        padding: 16px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #ffffff;
        flex-shrink: 0;
    }

    .modal-body {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        padding: 20px 24px;
    }

    .modal-footer {
        padding: 14px 24px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        flex-shrink: 0;
        background: #f8fafc;
    }

    /* Responsive adjustments */
    @media (max-width: 640px) {
        .modal-footer {
            padding: 12px 16px;
            gap: 8px;
        }
        .modal-footer button {
            flex: 1;
            justify-content: center;
        }
        .node-tab-container {
            grid-template-columns: repeat(2, 1fr);
        }
        .node-box {
            min-width: 100%;
            max-width: 100%;
        }
        .children-container {
            margin-left: 14px;
            padding-left: 12px;
        }
        .child-branch::before {
            left: -12px;
            width: 10px;
        }
    }

    /* ====== TOMSELECT CUSTOM STYLE ====== */
    .ts-wrapper.ts-server-filter .ts-control {
        border-radius: 12px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        padding: 7px 12px !important;
        min-height: 38px !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02) !important;
    }
    .ts-wrapper.ts-server-filter .ts-control.focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }

    /* Modal Form TomSelect Styling */
    .modal-body .ts-wrapper {
        width: 100% !important;
        display: block !important;
    }
    .modal-body .ts-wrapper .ts-control {
        border-radius: 10px !important;
        border: 1px solid #cbd5e1 !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        padding: 8px 12px !important;
        min-height: 38px !important;
        background-color: #ffffff !important;
        color: #1e293b !important;
        box-shadow: none !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.15s ease !important;
    }
    .modal-body .ts-wrapper.focus .ts-control,
    .modal-body .ts-wrapper .ts-control.focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
    }
    .modal-body .ts-wrapper.disabled .ts-control {
        background-color: #f1f5f9 !important;
        color: #94a3b8 !important;
        cursor: not-allowed !important;
    }
    .modal-body .ts-wrapper .ts-control input {
        font-size: 12px !important;
        color: #1e293b !important;
    }
    .modal-body .ts-wrapper .ts-control .item {
        font-size: 12px !important;
        color: #1e293b !important;
    }
    .modal-body .ts-dropdown {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 15px 30px -5px rgba(0, 0, 0, 0.18) !important;
        font-size: 12px !important;
        z-index: 9999 !important;
        background: #ffffff !important;
        max-height: 240px !important;
        overflow-y: auto !important;
    }
    .modal-body .ts-dropdown .option {
        padding: 8px 12px !important;
        font-size: 12px !important;
        color: #334155 !important;
        cursor: pointer !important;
    }
    .modal-body .ts-dropdown .option.active,
    .modal-body .ts-dropdown .option:hover {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
    }
    .modal-body .ts-dropdown .option[data-selectable="false"],
    .modal-body .ts-dropdown .option.disabled {
        color: #94a3b8 !important;
        cursor: not-allowed !important;
    }
    .ts-dropdown {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        font-size: 12px !important;
        z-index: 1050 !important;
    }
    .ts-dropdown .option {
        padding: 8px 12px !important;
    }
    .ts-dropdown .option.active,
    .ts-dropdown .option:hover {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
    }
</style>
@endsection

@section('content')
<div class="flex-grow-1 container-p-y">
    <!-- Breadcrumb Navigation (Pure Tailwind) -->
    <nav aria-label="breadcrumb" class="mb-3.5">
        <ol class="flex items-center gap-1.5 text-xs text-slate-500 mb-0 p-0 list-none">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 font-medium transition-colors no-underline">
                    <i class="bx bx-home-alt text-sm"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="text-slate-300">/</li>
            <li class="text-slate-500 font-medium">NOC</li>
            <li class="text-slate-300">/</li>
            <li class="text-blue-600 font-semibold">Data Infrastruktur FTTH</li>
        </ol>
    </nav>

    <!-- HERO HEADER (Pure Tailwind CSS) -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 p-4 sm:p-5 text-white shadow-lg shadow-blue-500/20 mb-4">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -right-8 -top-8 h-44 w-44 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
        <div class="absolute -left-8 -bottom-8 h-44 w-44 rounded-full bg-indigo-300/20 blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-3.5">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 backdrop-blur-md border border-white/25 text-white text-2xl shadow-sm shrink-0">
                    <i class="bx bx-network-chart"></i>
                </div>
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-white tracking-tight leading-tight mb-0.5">
                        Data Infrastruktur FTTH
                    </h1>
                    <p class="text-xs text-blue-100/90 leading-relaxed max-w-xl mb-0">
                        Kelola hierarki jaringan fiber optik (<span class="font-semibold text-white">Server &rarr; OLT &rarr; ODC &rarr; Splitter &rarr; ODP</span>) secara fleksibel & terintegrasi.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap shrink-0">
                <button type="button" onclick="loadTree()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-semibold backdrop-blur-md border border-white/30 transition-all duration-150 shadow-xs hover:-translate-y-0.5 cursor-pointer outline-none">
                    <i class="bx bx-refresh text-base"></i>
                    <span>Segarkan Data</span>
                </button>
                <button type="button" onclick="openAddNodeDialog()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-blue-700 hover:text-blue-800 text-xs font-bold transition-all duration-150 shadow-md shadow-black/10 hover:-translate-y-0.5 cursor-pointer border-0 outline-none">
                    <i class="bx bx-plus-circle text-base"></i>
                    <span>Tambah Node</span>
                </button>
            </div>
        </div>
    </div>

    <!-- STATS CARDS GRID (Pure Tailwind CSS) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 mb-4">
        <!-- Server Card -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-slate-200 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 text-lg shrink-0">
                <i class="bx bx-server"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10.5px] font-bold text-slate-400 uppercase tracking-wider">Server</div>
                <div id="stat-server" class="text-lg font-extrabold text-slate-800 leading-tight">0</div>
                <div class="text-[10px] font-medium text-slate-400 truncate">Node Pusat</div>
            </div>
        </div>

        <!-- OLT Card -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-blue-100 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 text-lg shrink-0">
                <i class="bx bx-chip"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10.5px] font-bold text-blue-600 uppercase tracking-wider">OLT</div>
                <div id="stat-olt" class="text-lg font-extrabold text-slate-800 leading-tight">0</div>
                <div class="text-[10px] font-medium text-slate-400 truncate">Terminal OLT</div>
            </div>
        </div>

        <!-- ODC Card -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-purple-100 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 text-lg shrink-0">
                <i class="bx bx-cabinet"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10.5px] font-bold text-purple-600 uppercase tracking-wider">ODC</div>
                <div id="stat-odc" class="text-lg font-extrabold text-slate-800 leading-tight">0</div>
                <div class="text-[10px] font-medium text-slate-400 truncate">Kabinet ODC</div>
            </div>
        </div>

        <!-- Splitter Card -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-amber-100 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 text-lg shrink-0">
                <i class="bx bx-git-repo-forked"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10.5px] font-bold text-amber-600 uppercase tracking-wider">Splitter</div>
                <div id="stat-splitter" class="text-lg font-extrabold text-slate-800 leading-tight">0</div>
                <div class="text-[10px] font-medium text-slate-400 truncate">Rasio Pembagi</div>
            </div>
        </div>

        <!-- ODP Card -->
        <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-emerald-100 shadow-xs hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 text-lg shrink-0">
                <i class="bx bx-plug"></i>
            </div>
            <div class="min-w-0">
                <div class="text-[10.5px] font-bold text-emerald-600 uppercase tracking-wider">ODP</div>
                <div id="stat-odp" class="text-lg font-extrabold text-slate-800 leading-tight">0</div>
                <div class="text-[10px] font-medium text-slate-400 truncate">Titik Distribusi</div>
            </div>
        </div>
    </div>

    <!-- Main Content (Full-Width Topology) -->
    <div>

        <!-- ============================== TOPOLOGY VIEWER ================================ -->
        <div class="w-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-slate-100 bg-slate-50/70 flex flex-col gap-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-0">
                            <i class="bx bx-sitemap text-blue-600"></i>
                            <span>Visualisasi Topologi Jaringan</span>
                        </h2>
                        <p class="text-[11px] text-slate-400 mt-0.5 mb-0">Struktur & konektivitas jaringan real-time dengan aksi interaktif pada tiap node.</p>
                    </div>

                    <!-- Toolbar Action Buttons -->
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <button onclick="syncOdcPonPorts()" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Sinkronkan Otomatis Port PON OLT dari Nama ODC (misal: 'ODC PON 1' -> PON-01)" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-600 hover:text-white text-indigo-700 border border-indigo-200 text-[11px] font-semibold rounded-lg transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                            <i class="bx bx-sync text-xs"></i> Sync PON ODC
                        </button>
                        <button onclick="toggleExpandAll(true)" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Buka Semua Sub-tree Jaringan" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                            <i class="bx bx-expand-alt text-xs"></i> Buka Semua
                        </button>
                        <button onclick="toggleExpandAll(false)" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Tutup Semua Sub-tree Jaringan" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                            <i class="bx bx-collapse-alt text-xs"></i> Tutup
                        </button>
                        <button onclick="loadTree()" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Segarkan Data Topologi" class="px-2.5 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-semibold rounded-lg transition-all flex items-center gap-1 cursor-pointer">
                            <i class="bx bx-refresh text-xs"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Server Filter Bar & Real-time Search -->
                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5 pt-1">
                    <div class="sm:col-span-6">
                        <label class="block text-[11px] font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                            <i class="bx bx-server text-blue-600 text-sm"></i> Filter Server:
                        </label>
                        <div class="w-full">
                            <select id="serverFilterSelect" class="ts-server-filter w-full" placeholder="Tampilkan Semua Server...">
                                <option value="" selected>Semua Server (Tampilkan Seluruh Topologi)</option>
                                @foreach ($serverList as $s)
                                    <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="sm:col-span-6 relative" id="globalSearchContainer">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1.5 mb-0">
                                <i class="bx bx-search-alt text-blue-600 text-sm"></i> Cari Cepat (ODP / ODC / Splitter):
                            </label>
                            <span class="text-[9.5px] font-mono text-slate-400 bg-slate-100 px-1.5 py-0.2 rounded border border-slate-200">Ctrl + K</span>
                        </div>
                        <div class="relative">
                            <input type="text" id="globalSearchInput" oninput="handleGlobalSearch(this.value)" placeholder="Ketik nama ODP / ODC / IP / Port..." autocomplete="off" class="form-input-custom !pl-8 !pr-8 !py-[7px] text-xs font-medium focus:ring-2 focus:ring-blue-400">
                            <i class="bx bx-search absolute left-2.5 top-2.5 text-slate-400 text-sm"></i>
                            <button type="button" id="btnSearchClear" onclick="clearGlobalSearch()" style="display:none;" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 border-0 bg-transparent cursor-pointer p-0">
                                <i class="bx bx-x text-base"></i>
                            </button>
                        </div>

                        <!-- Floating Search Autocomplete Dropdown -->
                        <div id="searchResultsDropdown" class="search-dropdown-menu custom-scroll" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <!-- Toast Alert Placeholder -->
            <div id="form-alert" class="mx-4 mt-3 p-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 shadow-sm" style="display:none">
                <span id="form-alert-msg"></span>
                <button type="button" onclick="this.parentElement.style.display='none'" class="text-white/80 hover:text-white bg-transparent border-0 cursor-pointer"><i class="bx bx-x text-lg"></i></button>
            </div>

            <!-- Canvas Tree Container -->
            <div class="topology-wrapper custom-scroll">
                <div id="topology">
                    <div class="text-center py-16 text-slate-400">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 grid place-items-center mx-auto mb-3 text-3xl shadow-xs border border-blue-100">
                            <i class="bx bx-server"></i>
                        </div>
                        <p class="text-sm font-bold text-slate-700 mb-1">Pilih Salah Satu Server</p>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto mb-0">Silakan pilih salah satu server pada pilihan filter di atas untuk menampilkan visualisasi topologi jaringannya.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<dialog id="add-node-dialog">
    <div id="add-node-panel">
        <div class="modal-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl grid place-items-center text-lg flex-shrink-0" style="background: linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8;">
                    <i class="bx bx-plus-circle"></i>
                </div>
                <div>
                    <h3 id="add-node-title" class="text-sm font-bold text-slate-800 leading-tight mb-0">Tambah Node Baru</h3>
                    <p id="add-node-subtitle" class="text-[11px] text-slate-400 mt-0.5 mb-0">Tambahkan node Server / OLT / Splitter / ODC / ODP</p>
                </div>
            </div>
            <button type="button" onclick="closeAddNodeDialog()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 grid place-items-center transition-all cursor-pointer border-0 outline-none">
                <i class="bx bx-x text-xl"></i>
            </button>
        </div>
        <form id="infraForm" autocomplete="off" novalidate>
            <div class="modal-body custom-scroll space-y-4">
                <!-- Tab Type Selector -->
                <div class="node-tab-container">
                    <button type="button" class="node-tab-btn active" data-type="Server" onclick="selectType('Server', this)">
                        <i class="bx bx-server"></i> <span>Server</span>
                    </button>
                    <button type="button" class="node-tab-btn" data-type="OLT" onclick="selectType('OLT', this)">
                        <i class="bx bx-chip"></i> <span>OLT</span>
                    </button>
                    <button type="button" class="node-tab-btn" data-type="Splitter" onclick="selectType('Splitter', this)">
                        <i class="bx bx-git-repo-forked"></i> <span>Splitter</span>
                    </button>
                    <button type="button" class="node-tab-btn" data-type="ODC" onclick="selectType('ODC', this)">
                        <i class="bx bx-cabinet"></i> <span>ODC</span>
                    </button>
                    <button type="button" class="node-tab-btn" data-type="ODP" onclick="selectType('ODP', this)">
                        <i class="bx bx-plug"></i> <span>ODP</span>
                    </button>
                </div>

                <!-- Type Information Banner -->
                <div id="type-banner" class="type-banner-box bg-slate-100 text-slate-700 border border-slate-200">
                    <i id="type-banner-icon" class="bx bx-server"></i>
                    <span id="type-banner-text">Server merupakan node pusat (Level 1) tempat OLT terhubung.</span>
                </div>

                <!-- Form Dynamic Groups -->

                <!-- SERVER -->
                <div id="group-Server" class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Server <span class="text-rose-500">*</span></label>
                        <input type="text" id="f_server_name" name="lokasi_server" class="form-input-custom" placeholder="Contoh: Server Pusat Gedong Songo">
                    </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">IP Address</label>
                            <input type="text" id="f_server_ip" name="ip" class="form-input-custom" placeholder="Contoh: 192.168.1.1">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">GPS / Link Map <span class="text-rose-500">*</span></label>
                            <input type="text" id="f_server_gps" name="gps" class="form-input-custom" placeholder="Link Maps atau Koordinat GPS">
                        </div>
                    </div>

                    <!-- OLT -->
                    <div id="group-OLT" class="space-y-3" style="display:none">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Parent (Server) <span class="text-rose-500">*</span></label>
                            <select id="parent-OLT" name="lokasi_server" class="form-input-custom bg-white">
                                <option value="" selected disabled>Pilih Server</option>
                                @foreach ($serverList as $s)
                                    <option value="{{ $s->id }}">{{ $s->lokasi_server }} @if($s->ip_address) ({{ $s->ip_address }}) @endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama OLT <span class="text-rose-500">*</span></label>
                            <input type="text" id="f_olt_name" name="olt" class="form-input-custom" placeholder="Contoh: OLT EPON Dondong">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Port PON <span class="text-rose-500">*</span></label>
                            <select id="f_olt_pon" name="jumlah_pon" class="form-input-custom bg-white font-medium">
                                <option value="2">2 Port PON</option>
                                <option value="4">4 Port PON</option>
                                <option value="8" selected>8 Port PON (Standard)</option>
                                <option value="16">16 Port PON</option>
                                <option value="32">32 Port PON</option>
                                <option value="64">64 Port PON</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">GPS / Link Map <span class="text-rose-500">*</span></label>
                            <input type="text" id="f_olt_gps" name="gps" class="form-input-custom" placeholder="Link Maps atau GPS">
                        </div>

                        <!-- Integrasi Stok Logistik OLT -->
                        <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2.5 my-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-blue-900 flex items-center gap-1.5 mb-0">
                                    <i class="bx bx-package text-blue-600 text-sm"></i> Integrasi Stok Logistik (OLT)
                                </label>
                                <span class="text-[10px] text-blue-700 bg-blue-100 font-semibold px-2 py-0.5 rounded-full">Otomatis Potong Stok</span>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-700">Model / Jenis OLT</label>
                                <select id="f_olt_perangkat_id" onchange="window.onHardwarePerangkatChange('olt', this, 'f_olt_modem_detail_id')" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700">
                                    <option value="">-- Pilih OLT dari Logistik (Opsional) --</option>
                                    @if(isset($perangkatOlt))
                                        @foreach ($perangkatOlt as $po)
                                            <option value="{{ $po->id }}">📦 {{ $po->nama_perangkat }} (Tersedia: {{ $po->stok_tersedia }} unit)</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-700">Serial Number (SN)</label>
                                <select id="f_olt_modem_detail_id" name="modem_detail_id" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700 font-mono text-xs">
                                    <option value="">-- Pilih Unit / Serial Number --</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mb-0">Stok unit di logistik akan otomatis terpotong saat disimpan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- SPLITTER -->
                    <div id="group-Splitter" class="space-y-3" style="display:none">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Target Pemasangan Splitter <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2 mb-1">
                                <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 text-xs font-medium text-slate-700">
                                    <input type="radio" name="splitter_target_type" value="pon" checked onchange="onSidebarSplitterTargetChange(this)" class="text-blue-600 focus:ring-0">
                                    <span class="font-bold">Port PON OLT</span>
                                </label>
                                <label class="flex items-center gap-1.5 p-2 rounded-xl border border-slate-200 bg-white cursor-pointer hover:bg-slate-50 text-xs font-medium text-slate-700">
                                    <input type="radio" name="splitter_target_type" value="cascade" onchange="onSidebarSplitterTargetChange(this)" class="text-blue-600 focus:ring-0">
                                    <span>Splitter (Cascade)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Target PON OLT -->
                        <div id="wrapper_sidebar_splitter_pon_target" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih OLT <span class="text-rose-500">*</span></label>
                                <select id="f_sidebar_splitter_olt" name="olt" onchange="onSidebarSplitterOltChange(this)" class="form-input-custom bg-white">
                                    <option value="" selected disabled>-- Pilih OLT --</option>
                                    @foreach ($oltList as $o)
                                        <option value="{{ $o->id }}" data-pon="{{ $o->jumlah_pon ?? 8 }}">{{ $o->nama_lokasi }} ({{ $o->jumlah_pon ?? 8 }} PON)</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Port PON OLT <span class="text-rose-500">*</span></label>
                                <select id="f_sidebar_splitter_pon_port" name="pon_port" class="form-input-custom bg-white">
                                    <option value="">-- Pilih Port PON --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Target Cascade -->
                        <div id="wrapper_sidebar_splitter_cascade_target" style="display:none;" class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Splitter Induk (Parent) <span class="text-rose-500">*</span></label>
                                <select id="parent-Splitter" name="parent_splitter_id" onchange="onSidebarParentSplitterChange(this)" class="form-input-custom bg-white">
                                    <option value="" selected disabled>-- Pilih Splitter Induk --</option>
                                    @if(isset($activeSplitters))
                                        @foreach ($activeSplitters as $as)
                                            <option value="{{ $as['id'] }}" data-rasio="{{ $as['rasio'] }}">{{ $as['nama'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div id="wrapper_sidebar_parent_port" style="display:none;">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Port Output pada Splitter Induk</label>
                                <select id="f_sidebar_splitter_parent_port" name="parent_port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                                    <option value="">-- Otomatis (Port kosong pertama) --</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Perangkat dari Logistik <span class="text-rose-500">*</span></label>
                            <select id="f_sidebar_splitter_logistik" name="logistik_id" onchange="onSidebarSplitterDevChange(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                                <option value="" disabled selected>-- Pilih Splitter dari Logistik --</option>
                                @if(isset($perangkatSplitter) && $perangkatSplitter->count() > 0)
                                    @foreach ($perangkatSplitter as $ps)
                                        <option value="{{ $ps->id }}" data-rasio="{{ $ps->rasio_default ?? '1:4' }}" data-nama="{{ $ps->nama_perangkat }}" data-stok="{{ $ps->stok_tersedia }}">
                                            📦 {{ $ps->nama_perangkat }} (Tersedia: {{ $ps->stok_tersedia }} unit)
                                        </option>
                                    @endforeach
                                @elseif(isset($splitterList) && $splitterList->count() > 0)
                                    @foreach ($splitterList as $sp)
                                        <option value="{{ $sp->logistik_id }}" data-rasio="{{ $sp->rasio ?? '1:4' }}" data-nama="{{ $sp->perangkat->nama_perangkat ?? 'Splitter' }}">
                                            📦 {{ $sp->perangkat->nama_perangkat ?? 'Splitter' }} (Rasio: {{ $sp->rasio ?? '-' }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Rasio Port Output Splitter <span class="text-rose-500">*</span></label>
                            <select id="f_sidebar_splitter_rasio" name="rasio" onchange="onSidebarSplitterRatioChange(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                                <option value="1:2">1:2 (2 Output Port)</option>
                                <option value="1:4" selected>1:4 (4 Output Port)</option>
                                <option value="1:8">1:8 (8 Output Port)</option>
                                <option value="1:16">1:16 (16 Output Port)</option>
                                <option value="1:32">1:32 (32 Output Port)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Serial Number (SN) dari Logistik (Opsional)</label>
                            <select id="f_sidebar_splitter_modem_detail_id" name="modem_detail_id" onchange="window.onSplitterSnSelectChange(this, 'f_sidebar_splitter_sn')" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700 font-mono text-xs">
                                <option value="">-- Tanpa SN / Pilih Unit Nanti --</option>
                            </select>
                            <input type="hidden" id="f_sidebar_splitter_sn" name="serial_number" value="">
                            <p class="text-[10px] text-slate-500 mb-0">Memilih unit fisik dari logistik akan otomatis memotong stok unit tersebut.</p>
                        </div>
                        <div id="sidebar_splitter_preview" style="display:none" class="mt-2"></div>
                    </div>

                    <!-- ODC -->
                    <div id="group-ODC" class="space-y-3" style="display:none">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Parent (OLT) <span class="text-rose-500">*</span></label>
                            <select id="parent-ODC" name="olt" onchange="onOltSelectedForOdc(this)" class="form-input-custom bg-white">
                                <option value="" selected disabled>Pilih OLT</option>
                                @foreach ($oltList as $o)
                                    <option value="{{ $o->id }}" data-pon="{{ $o->jumlah_pon ?? 8 }}">{{ $o->nama_lokasi }} ({{ $o->jumlah_pon ?? 8 }} PON)</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="wrapper-odc-pon">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Port PON OLT <span class="text-rose-500">*</span></label>
                            <select id="f_odc_pon" name="pon_port" onchange="onOdcPonSelected(this)" class="form-input-custom bg-white">
                                <option value="">-- Pilih Port PON --</option>
                            </select>
                        </div>

                        <!-- Info: splitter induk sudah terpasang di PON ini -->
                        <div id="wrapper_odc_splitter_info" style="display:none;" class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2">
                            <i class="bx bx-git-repo-forked text-lg text-amber-600 mt-0.5"></i>
                            <div>
                                <div class="font-bold">Splitter Induk di PON Sudah Terpasang</div>
                                <div id="lbl_odc_splitter_info_text" class="text-[11px] text-amber-800 mb-0"></div>
                            </div>
                        </div>

                        <!-- LOGISTICS SPLITTER SINGLE SELECT DROPDOWN -->
                        <div id="wrapper-odc-splitter" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-0">
                                    <i class="bx bx-git-repo-forked text-amber-600"></i> <span id="lbl_odc_splitter_title">Pasang Splitter Induk di PON</span>
                                </label>
                                <span class="text-[10px] text-amber-700 bg-amber-100 font-semibold px-2 py-0.5 rounded-full">Opsional</span>
                            </div>
                            <select id="f_odc_splitter" name="logistik_id" onchange="onSplitterSelected(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                                <option value="" data-rasio="">-- Pilih Splitter dari Logistik (atau kosongkan = ODC langsung ke PON) --</option>
                                @if(isset($perangkatSplitter) && $perangkatSplitter->count() > 0)
                                    @foreach ($perangkatSplitter as $ps)
                                        <option value="{{ $ps->id }}" data-rasio="{{ $ps->rasio_default ?? '1:4' }}" data-nama="{{ $ps->nama_perangkat }}" data-stok="{{ $ps->stok_tersedia }}">
                                            📦 {{ $ps->nama_perangkat }} (Tersedia: {{ $ps->stok_tersedia }} unit)
                                        </option>
                                    @endforeach
                                @elseif(isset($splitterList) && $splitterList->count() > 0)
                                    @foreach ($splitterList as $sp)
                                        <option value="{{ $sp->logistik_id }}" data-rasio="{{ $sp->rasio ?? '1:4' }}" data-nama="{{ $sp->perangkat->nama_perangkat ?? 'Splitter' }}">
                                            📦 {{ $sp->perangkat->nama_perangkat ?? 'Splitter' }} (Rasio: {{ $sp->rasio ?? '-' }})
                                        </option>
                                    @endforeach
                                @else
                                    <option disabled>Tidak ada splitter tersedia di logistik</option>
                                @endif
                            </select>

                            <div id="wrapper_odc_splitter_rasio" style="display:none;" class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-700">Rasio Port Output Splitter</label>
                                <select id="f_odc_splitter_rasio" name="rasio" onchange="onSplitterRatioChanged(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                                    <option value="1:2">1:2 (2 Output Port)</option>
                                    <option value="1:4">1:4 (4 Output Port)</option>
                                    <option value="1:8">1:8 (8 Output Port)</option>
                                    <option value="1:16">1:16 (16 Output Port)</option>
                                    <option value="1:32">1:32 (32 Output Port)</option>
                                </select>
                            </div>

                            <div id="wrapper_odc_splitter_sn" style="display:none;" class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-700">Serial Number (SN) Splitter (Opsional)</label>
                                <select id="f_odc_splitter_modem_detail_id" name="splitter_modem_detail_id" onchange="window.onSplitterSnSelectChange(this, 'f_odc_splitter_sn')" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700 font-mono text-xs">
                                    <option value="">-- Tanpa SN / Pilih Unit Nanti --</option>
                                </select>
                                <input type="hidden" id="f_odc_splitter_sn" name="splitter_serial_number" value="">
                            </div>

                            <p class="text-[10.5px] text-amber-700 mb-0">Jika splitter dipilih, splitter menjadi <strong>splitter induk di PON port</strong> di atas ODC ini. Jika dikosongkan, ODC <strong>langsung terhubung ke PON</strong>.</p>
                            <div id="splitterOutputsPreview" style="display:none" class="mt-2"></div>
                        </div>

                        <div id="wrapper_odc_port_out" style="display:none;" class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Port Output di Splitter Induk untuk ODC Ini</label>
                            <select id="f_odc_port_out" name="port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                                <option value="">-- Otomatis (port kosong pertama) --</option>
                            </select>
                            <p class="text-[10.5px] text-amber-700 mb-0">Kosongkan agar memakai <strong>port kosong pertama</strong> yang tersedia di splitter induk.</p>
                        </div>

                        <!-- Pilihan ODC Ada vs Baru -->
                        <div class="space-y-2 pt-1 border-t border-slate-200">
                            <label class="block text-xs font-bold text-slate-700">Pilihan ODC <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label id="sidebar_lbl_odc_existing" class="flex items-center gap-2 p-2.5 rounded-xl border-2 border-purple-500 bg-purple-50/80 cursor-pointer text-xs font-bold text-purple-900 transition-all shadow-xs">
                                    <input type="radio" name="sidebar_odc_source" value="existing" onchange="onOdcSourceChange(this, 'sidebar')" checked class="text-purple-600 focus:ring-purple-500">
                                    <span class="flex items-center gap-1.5"><i class="bx bx-list-check text-base text-purple-700"></i> Pilih ODC Ada</span>
                                </label>
                                <label id="sidebar_lbl_odc_new" class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all">
                                    <input type="radio" name="sidebar_odc_source" value="new" onchange="onOdcSourceChange(this, 'sidebar')" class="text-purple-600 focus:ring-purple-500">
                                    <span class="flex items-center gap-1.5"><i class="bx bx-plus-circle text-base text-slate-500"></i> Input Manual Baru</span>
                                </label>
                            </div>

                            <!-- Wrapper Pilih ODC Ada -->
                            <div id="sidebar_wrapper_odc_existing" class="space-y-1">
                                <label class="block text-xs font-semibold text-slate-700">Pilih ODC Terdaftar <span class="text-rose-500">*</span></label>
                                <select id="f_sidebar_odc_existing" name="odc_id" class="form-input-custom border-purple-300 focus:border-purple-500 bg-white font-medium text-slate-800">
                                    <option value="" disabled selected>-- Pilih ODC yang Sudah Ada --</option>
                                    @foreach ($odcList as $odc)
                                        <option value="{{ $odc->id }}" data-name="{{ $odc->nama_odc }}" data-gps="{{ $odc->gps }}">
                                            🏢 {{ $odc->nama_odc }} (Saat ini: {{ $odc->olt->nama_lokasi ?? 'Tanpa OLT' }} - {{ $odc->pon_port ?? 'Tanpa PON' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Wrapper Input Manual ODC Baru -->
                            <div id="sidebar_wrapper_odc_new" style="display:none;" class="space-y-2">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama ODC Baru <span class="text-rose-500">*</span></label>
                                    <input type="text" id="f_odc_name" name="nama_odc" class="form-input-custom" placeholder="Contoh: ODC Dondong 2">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">GPS / Link Map <span class="text-rose-500">*</span></label>
                                    <input type="text" id="f_odc_gps" name="gps" class="form-input-custom" placeholder="Link Maps atau GPS">
                                </div>

                                <!-- Integrasi Stok Logistik Box ODC -->
                                <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2.5 my-2">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-blue-900 flex items-center gap-1.5 mb-0">
                                            <i class="bx bx-package text-blue-600 text-sm"></i> Integrasi Stok Logistik (Box ODC)
                                        </label>
                                        <span class="text-[10px] text-blue-700 bg-blue-100 font-semibold px-2 py-0.5 rounded-full">Otomatis Potong Stok</span>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-slate-700">Model / Box ODC</label>
                                        <select id="f_odc_perangkat_id" onchange="window.onHardwarePerangkatChange('odc', this, 'f_odc_modem_detail_id')" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700">
                                            <option value="">-- Pilih Box ODC dari Logistik (Opsional) --</option>
                                            @if(isset($perangkatOdc))
                                                @foreach ($perangkatOdc as $podc)
                                                    <option value="{{ $podc->id }}">📦 {{ $podc->nama_perangkat }} (Tersedia: {{ $podc->stok_tersedia }} unit)</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-semibold text-slate-700">Serial Number (SN)</label>
                                        <select id="f_odc_modem_detail_id" name="modem_detail_id" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700 font-mono text-xs">
                                            <option value="">-- Pilih Unit / Serial Number --</option>
                                        </select>
                                        <p class="text-[10px] text-slate-500 mb-0">Stok box ODC di logistik akan otomatis terpotong saat disimpan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Panjang Kabel (PON → ODC) <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.01" min="0" name="panjang_kabel" id="f_odc_panjang_kabel" class="form-input-custom" placeholder="Contoh: 350">
                                    <span class="text-xs font-semibold text-slate-400 shrink-0">m</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Redaman (dBm) <span class="text-rose-500">*</span></label>
                                <div class="flex items-center gap-2">
                                    <input type="text" name="redaman" id="f_odc_redaman" class="form-input-custom" placeholder="Contoh: -16.5">
                                    <span class="text-xs font-semibold text-slate-400 shrink-0">dBm</span>
                                </div>
                            </div>
                        </div>

                    <!-- ODP -->
                    <div id="group-ODP" class="space-y-3" style="display:none">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Parent (ODC) <span class="text-rose-500">*</span></label>
                            <select id="parent-ODP" name="odc" onchange="onOdcSelectedForOdp(this)" class="form-input-custom bg-white">
                                <option value="" selected disabled>Pilih ODC</option>
                                @foreach ($odcList as $odc)
                                    <option value="{{ $odc->id }}" data-splitters="{{ json_encode($odc->splitters_data ?? []) }}">{{ $odc->nama_odc }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- DYNAMIC ODC SPLITTER SELECTION -->
                        <div id="wrapper-odp-splitter" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2.5" style="display: none;">
                            <div>
                                <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-1">
                                    <i class="bx bx-git-repo-forked text-amber-600"></i> Pilih Splitter (ODC)
                                </label>
                                <select id="f_odp_splitter" name="splitter_id" onchange="onOdpSplitterChange(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                                    <option value="">-- Pilih Splitter dari ODC --</option>
                                </select>
                            </div>

                            <!-- DYNAMIC PORT OUTPUT SELECTION -->
                            <div id="wrapper-odp-port" style="display: none;">
                                <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-1">
                                    <i class="bx bx-log-out-circle text-amber-600"></i> Pilih Port Output Splitter
                                </label>
                                <select id="f_odp_port" name="port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                                    <option value="">-- Pilih Port Output --</option>
                                </select>
                            </div>

                            <p class="text-[10.5px] text-amber-700 mb-0">Rasio & Port ODP akan otomatis disesuaikan dengan Splitter ODC yang dipilih.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama ODP <span class="text-rose-500">*</span></label>
                            <input type="text" id="f_odp_name" name="nama_odp" class="form-input-custom" placeholder="Contoh: ODP Dondong RW 04">
                        </div>

                        <!-- Integrasi Stok Logistik Box ODP -->
                        <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2.5 my-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-blue-900 flex items-center gap-1.5 mb-0">
                                    <i class="bx bx-package text-blue-600 text-sm"></i> Integrasi Stok Logistik (Box ODP)
                                </label>
                                <span class="text-[10px] text-blue-700 bg-blue-100 font-semibold px-2 py-0.5 rounded-full">Otomatis Potong Stok</span>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-700">Model / Box ODP</label>
                                <select id="f_odp_perangkat_id" onchange="window.onHardwarePerangkatChange('odp', this, 'f_odp_modem_detail_id')" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700">
                                    <option value="">-- Pilih Box ODP dari Logistik (Opsional) --</option>
                                    @if(isset($perangkatOdp))
                                        @foreach ($perangkatOdp as $podp)
                                            <option value="{{ $podp->id }}">📦 {{ $podp->nama_perangkat }} (Tersedia: {{ $podp->stok_tersedia }} unit)</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-[11px] font-semibold text-slate-700">Serial Number (SN)</label>
                                <select id="f_odp_modem_detail_id" name="modem_detail_id" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700 font-mono text-xs">
                                    <option value="">-- Pilih Unit / Serial Number --</option>
                                </select>
                                <p class="text-[10px] text-slate-500 mb-0">Stok box ODP di logistik akan otomatis terpotong saat disimpan.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">GPS / Link Map <span class="text-rose-500">*</span></label>
                            <input type="text" id="f_odp_gps" name="gps" class="form-input-custom" placeholder="Link Maps atau GPS">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Panjang Kabel (ODC → ODP) <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="number" step="0.01" min="0" name="panjang_kabel" id="f_odp_panjang_kabel" class="form-input-custom" placeholder="Contoh: 120">
                                <span class="text-xs font-semibold text-slate-400 shrink-0">m</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Redaman (dBm) <span class="text-rose-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="redaman" id="f_odp_redaman" class="form-input-custom" placeholder="Contoh: -19.2">
                                <span class="text-xs font-semibold text-slate-400 shrink-0">dBm</span>
                            </div>
                        </div>
                    </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" onclick="closeAddNodeDialog()" class="py-2.5 px-5 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer border border-slate-200 outline-none">
                    Batal
                </button>
                <button type="submit" id="btn-submit" class="py-2.5 px-6 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl border-0 outline-none shadow-sm hover:shadow transition-all flex items-center justify-center gap-2 cursor-pointer">
                    <i class="bx bx-plus-circle text-base"></i>
                    <span id="btn-submit-text">Tambahkan ke Topologi</span>
                </button>
            </div>
        </form>
    </div>
</dialog>
<!-- ==================================================================================== -->
<!-- NATIVE TOP LAYER MODAL DIALOG (HTML5 <dialog> with TopLayer: true)                 -->
<!-- ==================================================================================== -->
<dialog id="modal-dialog">
    <div id="modal-panel">

        <!-- Modal Header -->
        <div class="modal-header">
            <div class="flex items-center gap-3">
                <div id="modal-icon-bg" class="w-9 h-9 rounded-xl grid place-items-center text-lg flex-shrink-0" style="background: linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1d4ed8;">
                    <i id="modal-icon" class="bx bx-layer"></i>
                </div>
                <div>
                    <h3 id="modal-title" class="text-sm font-bold text-slate-800 leading-tight mb-0">Form Infrastruktur</h3>
                    <p id="modal-subtitle" class="text-[11px] text-slate-400 mt-0.5 mb-0">Perbarui rincian node data</p>
                </div>
            </div>
            <button type="button" onclick="closeDrawer()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 grid place-items-center transition-all cursor-pointer border-0 outline-none">
                <i class="bx bx-x text-xl"></i>
            </button>
        </div>

        <!-- Modal Body (Dynamic Fields) -->
        <form id="drawerDynamicForm" onsubmit="handleDrawerSubmit(event)" autocomplete="off" novalidate>
            <input type="hidden" id="drawer_action_method" value="POST">
            <input type="hidden" id="drawer_target_url" value="">
            <input type="hidden" id="drawer_node_id" value="">

            <div id="drawer-form-body" class="modal-body custom-scroll space-y-3.5">
                <!-- Fields injected by JavaScript -->
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button type="button" onclick="closeDrawer()" class="py-2.5 px-5 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer border border-slate-200 outline-none">
                    Batal
                </button>
                <button type="submit" id="btn-drawer-submit" class="py-2.5 px-6 text-white text-xs font-bold rounded-xl transition-all inline-flex items-center justify-center gap-1.5 cursor-pointer border-0 outline-none shadow-sm hover:shadow" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                    <i class="bx bx-check-circle text-base"></i>
                    <span id="btn-drawer-submit-text">Simpan Perubahan</span>
                </button>
            </div>
        </form>

    </div>
</dialog>
@endsection

@section('page-script')
<script>
    window.InfraConfig = {
        csrfToken: '{{ csrf_token() }}',
        routes: {
            serverStore: '{{ route("server.store") }}',
            oltStore: '{{ route("olt.store") }}',
            splitterStore: '{{ route("splitter.store") }}',
            odcStore: '{{ route("odc.store") }}',
            odpStore: '{{ route("odp.store") }}',
            infrastrukturJson: '{{ route("infrastruktur.json") }}',
            infrastrukturOptions: '{{ route("infrastruktur.form-options") }}',
            syncOdcPon: '{{ route("odc.sync-pon") }}'
        },
        initialData: {
            serverList: @json($serverList ?? []),
            oltList: @json($oltList ?? []),
            odcList: @json($odcList ?? []),
            splitterList: @json($splitterList ?? []),
            perangkatSplitter: @json($perangkatSplitter ?? []),
            perangkatOlt: @json($perangkatOlt ?? []),
            perangkatOdc: @json($perangkatOdc ?? []),
            perangkatOdp: @json($perangkatOdp ?? []),
            ponSplitters: @json($ponSplitters ?? []),
            activeSplitters: @json($activeSplitters ?? [])
        }
    };
</script>
<script src="{{ asset('assets/js/data-infrastruktur.js') }}"></script>
@endsection
