/**
 * Data Infrastruktur FTTH JavaScript Module
 */
const CONFIG = window.InfraConfig || {};
const ROUTES = CONFIG.routes || {};
const CSRF = CONFIG.csrfToken || '';

const TYPE_ACTION = {
    Server: ROUTES.serverStore || '/server/add',
    OLT: ROUTES.oltStore || '/olt/add',
    Splitter: ROUTES.splitterStore || '/splitter/add',
    ODC: ROUTES.odcStore || '/odc/add',
    ODP: ROUTES.odpStore || '/odp/add'
};

let currentType = 'Server';
let treeDataGlobal = [];

let serverListGlobal = CONFIG.initialData?.serverList || [];
let oltListGlobal = CONFIG.initialData?.oltList || [];
let odcListGlobal = CONFIG.initialData?.odcList || [];
let splitterListGlobal = CONFIG.initialData?.splitterList || [];
let perangkatSplitterGlobal = CONFIG.initialData?.perangkatSplitter || [];
let ponSplittersGlobal = CONFIG.initialData?.ponSplitters || [];
let activeSplittersGlobal = CONFIG.initialData?.activeSplitters || [];
let perangkatOltGlobal = CONFIG.initialData?.perangkatOlt || [];
let perangkatOdcGlobal = CONFIG.initialData?.perangkatOdc || [];
let perangkatOdpGlobal = CONFIG.initialData?.perangkatOdp || [];

window.getPerangkatCatalog = function(category) {
    if (category === 'olt') return perangkatOltGlobal;
    if (category === 'odc') return perangkatOdcGlobal;
    if (category === 'odp') return perangkatOdpGlobal;
    if (category === 'splitter') return perangkatSplitterGlobal;
    return [];
};

/* ================================================================================= */
/* TOMSELECT HELPERS FOR MODAL FORMS (TAMBAH & EDIT)                                 */
/* ================================================================================= */

function initModalTomSelect(el) {
    if (!el || typeof TomSelect === 'undefined') return null;
    if (el.tomselect) return el.tomselect;
    if (el.tagName !== 'SELECT') return null;

    const firstOpt = el.querySelector('option');
    const placeholderText = el.getAttribute('placeholder') || (firstOpt && firstOpt.value === '' ? firstOpt.text : '-- Pilih --');
    const allowCreate = el.getAttribute('data-create') === 'true';

    try {
        const ts = new TomSelect(el, {
            create: allowCreate,
            allowEmptyOption: true,
            maxOptions: null,
            controlInput: '<input>',
            placeholder: placeholderText,
            sortField: false,
            render: {
                no_results: function(data, escape) {
                    return '<div class="no-results p-2.5 text-slate-400 text-xs text-center">Tidak ada hasil untuk "<strong>' + escape(data.input) + '</strong>"</div>';
                }
            }
        });
        return ts;
    } catch (e) {
        console.warn('TomSelect init error on:', el.id || el.name, e);
        return null;
    }
}

function syncTomSelect(el) {
    if (!el) return;
    if (el.tomselect) {
        const curVal = el.value;
        el.tomselect.clearOptions();
        el.tomselect.sync();
        if (curVal) {
            el.tomselect.setValue(curVal, true);
        }
    } else {
        initModalTomSelect(el);
    }
}

function initAddNodeTomSelects() {
    if (typeof TomSelect === 'undefined') return;
    const form = document.getElementById('infraForm');
    if (!form) return;
    form.querySelectorAll('select').forEach(sel => {
        initModalTomSelect(sel);
    });
}

function initDrawerTomSelects() {
    if (typeof TomSelect === 'undefined') return;
    const form = document.getElementById('drawerDynamicForm');
    if (!form) return;
    form.querySelectorAll('select').forEach(sel => {
        initModalTomSelect(sel);
    });
}

function destroyDrawerTomSelects() {
    const form = document.getElementById('drawerDynamicForm');
    if (!form) return;
    form.querySelectorAll('select').forEach(sel => {
        if (sel.tomselect) {
            try { sel.tomselect.destroy(); } catch (e) {}
        }
    });
}

window.onHardwarePerangkatChange = function(category, devSelectEl, snSelectId, preselectedMdId = null, preselectedSn = '') {
    const snSelect = document.getElementById(snSelectId);
    if (!snSelect) return;
    const devId = devSelectEl.value;
    const catalog = window.getPerangkatCatalog(category);
    const dev = catalog.find(d => String(d.id) === String(devId));

    let options = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
    if (dev && dev.available_serials && dev.available_serials.length > 0) {
        let foundPreselected = false;
        dev.available_serials.forEach(s => {
            const isSel = (preselectedMdId && String(s.id) === String(preselectedMdId)) ? 'selected' : '';
            if (isSel) foundPreselected = true;
            options += `<option value="${s.id}" data-sn="${escapeHtml(s.serial_number)}" ${isSel}>🔹 SN: ${escapeHtml(s.serial_number)}${s.mac_address ? ` (MAC: ${escapeHtml(s.mac_address)})` : ''}</option>`;
        });
        if (preselectedMdId && !foundPreselected && preselectedSn) {
            options += `<option value="${preselectedMdId}" data-sn="${escapeHtml(preselectedSn)}" selected>🔹 SN: ${escapeHtml(preselectedSn)} (Unit Saat Ini)</option>`;
        }
    } else if (preselectedMdId && preselectedSn) {
        options += `<option value="${preselectedMdId}" data-sn="${escapeHtml(preselectedSn)}" selected>🔹 SN: ${escapeHtml(preselectedSn)} (Unit Saat Ini)</option>`;
    } else if (dev) {
        options = '<option value="">⚠️ Stok fisik berseri belum tersedia</option>';
    }
    snSelect.innerHTML = options;
    syncTomSelect(snSelect);
};

window.onSplitterSnSelectChange = function(selectEl, hiddenInputId) {
    const hiddenInput = document.getElementById(hiddenInputId);
    if (!hiddenInput) return;
    if (!selectEl || !selectEl.value) {
        hiddenInput.value = '';
        return;
    }
    const opt = selectEl.options[selectEl.selectedIndex];
    if (opt) {
        hiddenInput.value = opt.getAttribute('data-sn') || opt.text.replace(/^🔹\s*SN:\s*/i, '').split(' ')[0] || '';
    }
};

function buildHardwareLogistikFields(category, prefix, selectedPerangkatId = null, selectedMdId = null, selectedSn = '') {
    const catalog = window.getPerangkatCatalog(category);
    const catUpper = category.toUpperCase();
    const devSelectId = `${prefix}_perangkat_id`;
    const snSelectId = `${prefix}_modem_detail_id`;

    let devOptions = `<option value="">-- Pilih Model / Barang ${catUpper} dari Logistik (Opsional) --</option>`;
    catalog.forEach(p => {
        const isSel = (selectedPerangkatId && String(p.id) === String(selectedPerangkatId)) ? 'selected' : '';
        devOptions += `<option value="${p.id}" ${isSel}>📦 ${escapeHtml(p.nama_perangkat)} (Tersedia: ${p.stok_tersedia} unit)</option>`;
    });

    let snOptions = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
    if (selectedPerangkatId) {
        const dev = catalog.find(d => String(d.id) === String(selectedPerangkatId));
        let foundPreselected = false;
        if (dev && dev.available_serials && dev.available_serials.length > 0) {
            dev.available_serials.forEach(s => {
                const isSel = (selectedMdId && String(s.id) === String(selectedMdId)) ? 'selected' : '';
                if (isSel) foundPreselected = true;
                snOptions += `<option value="${s.id}" ${isSel}>🔹 SN: ${escapeHtml(s.serial_number)}${s.mac_address ? ` (MAC: ${escapeHtml(s.mac_address)})` : ''}</option>`;
            });
        }
        if (selectedMdId && !foundPreselected && selectedSn) {
            snOptions += `<option value="${selectedMdId}" selected>🔹 SN: ${escapeHtml(selectedSn)} (Unit Saat Ini)</option>`;
        }
    } else if (selectedMdId && selectedSn) {
        snOptions += `<option value="${selectedMdId}" selected>🔹 SN: ${escapeHtml(selectedSn)} (Unit Saat Ini)</option>`;
    }

    return `
        <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-xl space-y-2.5 my-2">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold text-blue-900 flex items-center gap-1.5 mb-0">
                    <i class="bx bx-package text-blue-600 text-sm"></i> Integrasi Stok Logistik (${catUpper})
                </label>
                <span class="text-[10px] text-blue-700 bg-blue-100 font-semibold px-2 py-0.5 rounded-full">Otomatis Potong Stok</span>
            </div>
            <div class="space-y-1">
                <label class="block text-[11px] font-semibold text-slate-700">Model / Jenis Perangkat</label>
                <select id="${devSelectId}" onchange="window.onHardwarePerangkatChange('${category}', this, '${snSelectId}', '${selectedMdId || ''}', '${escapeHtml(selectedSn || '')}')" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700">
                    ${devOptions}
                </select>
            </div>
            <div class="space-y-1">
                <label class="block text-[11px] font-semibold text-slate-700">Pilih Unit Fisik / Serial Number (SN)</label>
                <select id="${snSelectId}" name="modem_detail_id" class="form-input-custom border-blue-300 focus:border-blue-500 bg-white font-medium text-slate-700 font-mono text-xs">
                    ${snOptions}
                </select>
                <p class="text-[10px] text-slate-500 mb-0">Memilih Serial Number akan otomatis mengubah status barang menjadi <strong>TERPAKAI</strong> dan memotong stok logistik.</p>
            </div>
        </div>
    `;
}

const odcSplittersMap = {};
const ponSplitterIndex = {};

function rebuildIndexes() {
    for (let key in odcSplittersMap) delete odcSplittersMap[key];
    (odcListGlobal || []).forEach(odc => {
        odcSplittersMap[odc.id] = odc.splitters_data || [];
    });

    for (let key in ponSplitterIndex) delete ponSplitterIndex[key];
    (ponSplittersGlobal || []).forEach(sp => {
        if (sp.lokasi_id && sp.pon_port) {
            ponSplitterIndex[`${sp.lokasi_id}|${sp.pon_port}`] = sp;
        }
    });
}
rebuildIndexes();

    const TAB_INFO = {
        Server:   { bannerBg: 'bg-slate-100 text-slate-700 border border-slate-200', icon: 'bx-server', text: 'Server merupakan node pusat (Level 1) tempat OLT terhubung.' },
        OLT:      { bannerBg: 'bg-blue-50 text-blue-800 border border-blue-200', icon: 'bx-chip', text: 'OLT terhubung ke Server. Menampung distribusi ODC & Splitter PON.' },
        Splitter: { bannerBg: 'bg-amber-50 text-amber-800 border border-amber-200', icon: 'bx-git-repo-forked', text: 'Splitter membagi sinyal optik (1:2, 1:4, 1:8, 1:16, 1:32) dan dapat dihubungkan bertingkat (cascaded).' },
        ODC:      { bannerBg: 'bg-purple-50 text-purple-800 border border-purple-200', icon: 'bx-cabinet', text: 'ODC terhubung ke PON port OLT. Opsional pasang splitter induk di PON: PON → Splitter → ODC, atau langsung PON → ODC.' },
        ODP:      { bannerBg: 'bg-emerald-50 text-emerald-800 border border-emerald-200', icon: 'bx-plug', text: 'ODP terhubung ke ODC (atau splitter cascade di dalam ODC). Titik distribusi ke pelanggan.' }
    };

    function selectType(type, el) {
        currentType = type;
        document.querySelectorAll('.node-tab-btn').forEach(btn => btn.classList.remove('active'));
        if (el) el.classList.add('active');

        const info = TAB_INFO[type];
        const banner = document.getElementById('type-banner');
        if (banner && info) {
            banner.className = `type-banner-box ${info.bannerBg}`;
            document.getElementById('type-banner-icon').className = `bx ${info.icon}`;
            document.getElementById('type-banner-text').textContent = info.text;
        }

        ['Server', 'OLT', 'Splitter', 'ODC', 'ODP'].forEach(t => {
            const g = document.getElementById('group-' + t);
            if (g) g.style.display = (t === type) ? 'block' : 'none';
        });

        // Initialize and sync TomSelect controls in active group
        setTimeout(() => {
            const activeGroup = document.getElementById('group-' + type);
            if (activeGroup) {
                activeGroup.querySelectorAll('select').forEach(sel => {
                    if (sel.tomselect) {
                        sel.tomselect.sync();
                    } else {
                        initModalTomSelect(sel);
                    }
                });
            }
        }, 15);
    }

    function onOdcSourceChange(el, context) {
        const isExisting = el.value === 'existing';
        const prefix = context === 'drawer' ? 'drawer_' : 'sidebar_';
        
        const existingWrapper = document.getElementById(`${prefix}wrapper_odc_existing`);
        const newWrapper = document.getElementById(`${prefix}wrapper_odc_new`);
        const selectExisting = document.getElementById(context === 'drawer' ? 'drawer_odc_existing_id' : 'f_sidebar_odc_existing');
        const inputName = document.getElementById(context === 'drawer' ? 'drawer_f_odc_name' : 'f_odc_name');

        const lblExisting = document.getElementById(`${prefix}lbl_odc_existing`);
        const lblNew = document.getElementById(`${prefix}lbl_odc_new`);

        if (lblExisting) {
            lblExisting.className = isExisting 
                ? 'flex items-center gap-2 p-2.5 rounded-xl border-2 border-purple-500 bg-purple-50/80 cursor-pointer text-xs font-bold text-purple-900 transition-all shadow-xs'
                : 'flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all';
        }
        if (lblNew) {
            lblNew.className = !isExisting 
                ? 'flex items-center gap-2 p-2.5 rounded-xl border-2 border-purple-500 bg-purple-50/80 cursor-pointer text-xs font-bold text-purple-900 transition-all shadow-xs'
                : 'flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all';
        }

        if (existingWrapper) existingWrapper.style.display = isExisting ? 'block' : 'none';
        if (newWrapper) newWrapper.style.display = isExisting ? 'none' : 'block';

        if (selectExisting && isExisting) {
            setTimeout(() => {
                syncTomSelect(selectExisting);
            }, 10);
        }
    }

    function onSidebarSplitterTargetChange(el) {
        const isPon = el.value === 'pon';
        const ponTargetWrapper = document.getElementById('wrapper_sidebar_splitter_pon_target');
        const cascadeTargetWrapper = document.getElementById('wrapper_sidebar_splitter_cascade_target');
        const oltSelect = document.getElementById('f_sidebar_splitter_olt');
        const parentSpSelect = document.getElementById('parent-Splitter');

        if (ponTargetWrapper) ponTargetWrapper.style.display = isPon ? 'block' : 'none';
        if (cascadeTargetWrapper) cascadeTargetWrapper.style.display = isPon ? 'none' : 'block';

        if (isPon && oltSelect) syncTomSelect(oltSelect);
        if (!isPon && parentSpSelect) syncTomSelect(parentSpSelect);
    }

    function onSidebarSplitterOltChange(el) {
        const ponSelect = document.getElementById('f_sidebar_splitter_pon_port');
        if (!ponSelect) return;
        const opt = el.options[el.selectedIndex];
        const ponCount = opt ? parseInt(opt.getAttribute('data-pon') || 8) : 8;

        let html = '<option value="">-- Pilih Port PON --</option>';
        for (let i = 1; i <= ponCount; i++) {
            const pCode = `PON-${String(i).padStart(2, '0')}`;
            html += `<option value="${pCode}">${pCode}</option>`;
        }
        ponSelect.innerHTML = html;
        syncTomSelect(ponSelect);
    }

    function parseRatioNum(rasioStr, defaultRatio = 4) {
        if (!rasioStr) return defaultRatio;
        const str = String(rasioStr).trim();
        if (str.includes(':')) {
            const parts = str.split(':');
            const num = parseInt(parts[parts.length - 1]);
            if (!isNaN(num) && num > 0) return num;
        }
        if (str.includes('/')) {
            const parts = str.split('/');
            const num = parseInt(parts[parts.length - 1]);
            if (!isNaN(num) && num > 0) return num;
        }
        const num = parseInt(str.replace(/\D/g, ''));
        return (!isNaN(num) && num > 0) ? num : defaultRatio;
    }

    function escapeHtml(s) {
        return (s || '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function buildPortOutOptions(selected, num, placeholder) {
        const ph = placeholder || '-- Pilih Port Output (OUT) --';
        let html = `<option value="">${escapeHtml(ph)}</option>`;
        for (let i = 1; i <= num; i++) {
            const portName = `OUT-${String(i).padStart(2, "0")}`;
            html += `<option value="${portName}" ${selected === portName ? 'selected' : ''}>Port ${portName}</option>`;
        }
        return html;
    }

    function getNodeClass(type) {
        if (type === 'Server') return 'node-server';
        if (type === 'OLT') return 'node-olt';
        if (type === 'ODC') return 'node-odc';
        if (type === 'Splitter') return 'node-splitter';
        if (type === 'ODP') return 'node-odp';
        return '';
    }

    function getNodeIcon(type) {
        if (type === 'Server') return 'bx bx-server';
        if (type === 'OLT') return 'bx bx-chip';
        if (type === 'ODC') return 'bx bx-cabinet';
        if (type === 'Splitter') return 'bx bx-git-repo-forked';
        if (type === 'ODP') return 'bx bx-plug';
        return 'bx bx-cube';
    }

    function getNodeBadgeBg(type) {
        if (type === 'Server') return '#f1f5f9';
        if (type === 'OLT') return '#eff6ff';
        if (type === 'ODC') return '#f5f3ff';
        if (type === 'Splitter') return '#fffbeb';
        if (type === 'ODP') return '#f0fdf4';
        return '#f1f5f9';
    }

    function getNodeBadgeColor(type) {
        if (type === 'Server') return '#64748b';
        if (type === 'OLT') return '#2563eb';
        if (type === 'ODC') return '#7c3aed';
        if (type === 'Splitter') return '#d97706';
        if (type === 'ODP') return '#16a34a';
        return '#64748b';
    }

    function showAlert(msg, type = 'success') {
        const alertBox = document.getElementById('form-alert');
        alertBox.className = `mx-5 mt-4 p-3 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 ${
            type === 'success' ? 'bg-slate-900 text-white' : 'bg-rose-600 text-white'
        }`;
        document.getElementById('form-alert-msg').textContent = msg;
        alertBox.style.display = 'flex';
        setTimeout(() => { alertBox.style.display = 'none'; }, 5000);
    }

    function updateCounters(data) {
        let counts = { Server: 0, OLT: 0, ODC: 0, ODP: 0, Splitter: 0 };
        function traverse(nodes) {
            (nodes || []).forEach(n => {
                if (counts[n.type] !== undefined) counts[n.type]++;
                if (n.splitters) counts.Splitter += n.splitters.length;
                if (n.children) traverse(n.children);
            });
        }
        traverse(data);
        document.getElementById('stat-server').textContent = counts.Server;
        document.getElementById('stat-olt').textContent = counts.OLT;
        document.getElementById('stat-odc').textContent = counts.ODC;
        document.getElementById('stat-splitter').textContent = counts.Splitter;
        document.getElementById('stat-odp').textContent = counts.ODP;
    }

    /* ================================================================================= */
    /* TOP LAYER MODAL HANDLERS (<dialog> with TopLayer: true)                           */
    /* ================================================================================= */

    function openDrawer(mode, type, payload = {}) {
        const dialog    = document.getElementById('modal-dialog');
        const titleEl   = document.getElementById('modal-title');
        const subtitleEl = document.getElementById('modal-subtitle');
        const iconEl    = document.getElementById('modal-icon');
        const iconBg    = document.getElementById('modal-icon-bg');
        const formBody  = document.getElementById('drawer-form-body');
        const methodInput = document.getElementById('drawer_action_method');
        const urlInput  = document.getElementById('drawer_target_url');
        const footerSubmitBtn = document.getElementById('btn-drawer-submit');

        if (footerSubmitBtn) footerSubmitBtn.style.display = 'inline-flex';

        const ICON_BG_MAP = {
            Server:   { bg: 'linear-gradient(135deg,#f1f5f9,#e2e8f0)', color: '#475569' },
            OLT:      { bg: 'linear-gradient(135deg,#dbeafe,#bfdbfe)', color: '#1d4ed8' },
            ODC:      { bg: 'linear-gradient(135deg,#ede9fe,#ddd6fe)', color: '#6d28d9' },
            Splitter: { bg: 'linear-gradient(135deg,#fef3c7,#fde68a)', color: '#b45309' },
            ODP:      { bg: 'linear-gradient(135deg,#dcfce7,#bbf7d0)', color: '#15803d' },
        };
        const ibm = ICON_BG_MAP[type] || ICON_BG_MAP.OLT;
        iconBg.style.background = ibm.bg;
        iconBg.style.color      = ibm.color;
        iconEl.className        = getNodeIcon(type);

        let title = '', subtitle = '', targetUrl = '', method = 'POST';

        if (mode === 'add') {
            title    = `Tambah ${type} Baru`;
            subtitle = `Tambahkan node ${type} baru ke hierarki jaringan`;
            targetUrl = TYPE_ACTION[type];
        } else if (mode === 'edit') {
            title    = `Edit ${type}`;
            subtitle = `Perbarui rincian: ${payload.name || type}`;
            if (type === 'Splitter') {
                targetUrl = `/splitter/update/${payload.db_id}`;
            } else {
                targetUrl = `/${type.toLowerCase()}/update/${payload.db_id}`;
            }
        } else if (mode === 'attach_splitter') {
            if (payload.parent_type === 'ODP') {
                title    = 'Pasang Splitter ODP';
                subtitle = `Pasang splitter internal (1:16 / 1:8) pada box ODP #${payload.parent_id}`;
                targetUrl = `/odp/splitter/${payload.parent_id}`;
            } else if (payload.parent_type === 'ODC' && !payload.parent_splitter_id) {
                title    = 'Pasang Splitter ODC';
                subtitle = `Hubungkan splitter dari logistik ke ODC #${payload.parent_id}`;
                targetUrl = `/odc/splitter/${payload.parent_id}`;
            } else if (payload.parent_type === 'PON' || (payload.olt_id && payload.pon_port && !payload.parent_splitter_id)) {
                title    = `Pasang Splitter pada Port ${payload.pon_port}`;
                subtitle = `Pasang splitter induk dari logistik ke ${payload.olt_name || 'OLT'} (${payload.pon_port})`;
                targetUrl = ROUTES.splitterStore;
            } else {
                title    = payload.parent_port_out ? `Pasang Splitter Bertingkat (${payload.parent_port_out})` : 'Pasang Splitter Baru';
                subtitle = payload.parent_name ? `Menghubungkan splitter baru ke ${payload.parent_name}` : 'Tambahkan splitter baru ke hierarki';
                targetUrl = ROUTES.splitterStore;
            }
        }

        titleEl.textContent   = title;
        subtitleEl.textContent = subtitle;
        urlInput.value        = targetUrl;
        methodInput.value     = method;

        destroyDrawerTomSelects();
        formBody.innerHTML = generateDrawerFields(mode, type, payload);
        initDrawerTomSelects();

        // Native Top Layer invocation
        if (dialog) {
            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else {
                dialog.setAttribute('open', '');
            }
        }
    }

    function closeDrawer() {
        destroyDrawerTomSelects();
        const dialog = document.getElementById('modal-dialog');
        if (dialog) {
            if (typeof dialog.close === 'function') {
                dialog.close();
            } else {
                dialog.removeAttribute('open');
            }
        }
    }

    /* Add-Node dialog (centered modal for "Tambah Node") */
    function openAddNodeDialog() {
        const defaultBtn = document.querySelector('.node-tab-btn[data-type="Server"]');
        if (defaultBtn) selectType('Server', defaultBtn);
        initAddNodeTomSelects();
        const dialog = document.getElementById('add-node-dialog');
        if (dialog) {
            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            } else {
                dialog.setAttribute('open', '');
            }
        }
    }

    function closeAddNodeDialog() {
        const dialog = document.getElementById('add-node-dialog');
        if (dialog) {
            if (typeof dialog.close === 'function') {
                dialog.close();
            } else {
                dialog.removeAttribute('open');
            }
        }
    }

    // Native backdrop click listener for Add-Node dialog
    document.getElementById('add-node-dialog')?.addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const isInDialog = (
            rect.top <= e.clientY &&
            e.clientY <= rect.top + rect.height &&
            rect.left <= e.clientX &&
            e.clientX <= rect.left + rect.width
        );
        if (!isInDialog) {
            closeAddNodeDialog();
        }
    });

    // Native backdrop click listener for Top Layer dialog
    document.getElementById('modal-dialog')?.addEventListener('click', function(e) {
        const rect = this.getBoundingClientRect();
        const isInDialog = (
            rect.top <= e.clientY &&
            e.clientY <= rect.top + rect.height &&
            rect.left <= e.clientX &&
            e.clientX <= rect.left + rect.width
        );
        if (!isInDialog) {
            closeDrawer();
        }
    });


    function generateDrawerFields(mode, type, payload) {
        if (mode === 'edit' && type === 'Splitter') {
            const currentRatio = payload.rasio || '1:4';
            const currentSn = payload.serial || payload.serial_number || '';

            let snOptions = '<option value="">-- Tanpa SN / Manual --</option>';
            if (currentSn) {
                snOptions += `<option value="${escapeHtml(currentSn)}" selected>🔹 SN: ${escapeHtml(currentSn)} (Unit Saat Ini)</option>`;
            }
            if (perangkatSplitterGlobal && perangkatSplitterGlobal.length > 0) {
                perangkatSplitterGlobal.forEach(ps => {
                    if (ps.available_serials && ps.available_serials.length > 0) {
                        snOptions += `<optgroup label="📦 ${escapeHtml(ps.nama_perangkat)}">`;
                        ps.available_serials.forEach(s => {
                            if (s.serial_number !== currentSn) {
                                snOptions += `<option value="${escapeHtml(s.serial_number)}" data-sn="${escapeHtml(s.serial_number)}">🔹 SN: ${escapeHtml(s.serial_number)}${s.mac_address ? ` (MAC: ${escapeHtml(s.mac_address)})` : ''}</option>`;
                            }
                        });
                        snOptions += `</optgroup>`;
                    }
                });
            }

            return `
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5 mb-2">
                    <i class="bx bx-git-repo-forked text-xl text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <div class="font-bold">Edit Splitter: ${escapeHtml(payload.name || 'Splitter')}</div>
                        <div class="text-[11px] text-amber-800 leading-snug">
                            ${payload.pon_port ? `Terpasang pada <strong>Port ${escapeHtml(payload.pon_port)}</strong>` : ''}
                            ${payload.parent_port_out ? ` pada Port Induk <strong>${escapeHtml(payload.parent_port_out)}</strong>` : ''}
                        </div>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Rasio Port Keluaran <span class="text-rose-500">*</span></label>
                    <select id="drawer_splitter_rasio" name="rasio" required onchange="onDrawerSplitterRatioChanged(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                        <option value="1:2" ${currentRatio === '1:2' ? 'selected' : ''}>1:2 (2 Output Port)</option>
                        <option value="1:4" ${currentRatio === '1:4' ? 'selected' : ''}>1:4 (4 Output Port)</option>
                        <option value="1:8" ${currentRatio === '1:8' ? 'selected' : ''}>1:8 (8 Output Port)</option>
                        <option value="1:16" ${currentRatio === '1:16' ? 'selected' : ''}>1:16 (16 Output Port)</option>
                        <option value="1:32" ${currentRatio === '1:32' ? 'selected' : ''}>1:32 (32 Output Port)</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Serial Number (SN) dari Logistik</label>
                    <select id="drawer_splitter_edit_sn" name="serial_number" data-create="true" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700 font-mono text-xs" placeholder="Pilih dari Logistik atau ketik SN...">
                        ${snOptions}
                    </select>
                    <p class="text-[10px] text-slate-500 mb-0">Pilih dari unit logistik yang tersedia atau ketik SN baru jika diperlukan.</p>
                </div>
                <div id="drawer_splitterOutputsPreview" style="display:none" class="mt-2"></div>
            `;
        }

        if (mode === 'attach_splitter' || type === 'Splitter') {
            let splitterOptions = '<option value="" disabled selected>-- Pilih Perangkat Splitter --</option>';
            if (perangkatSplitterGlobal && perangkatSplitterGlobal.length > 0) {
                perangkatSplitterGlobal.forEach(ps => {
                    splitterOptions += `<option value="${ps.id}" data-rasio="${escapeHtml(ps.rasio_default || '1:4')}" data-stok="${ps.stok_tersedia}">
                        📦 ${escapeHtml(ps.nama_perangkat)} (Tersedia: ${ps.stok_tersedia} unit)
                    </option>`;
                });
            } else if (splitterListGlobal && splitterListGlobal.length > 0) {
                splitterListGlobal.forEach(sp => {
                    const pNama = (sp.perangkat && sp.perangkat.nama_perangkat) ? sp.perangkat.nama_perangkat : 'Splitter';
                    splitterOptions += `<option value="${sp.logistik_id}" data-rasio="${escapeHtml(sp.rasio || '1:4')}">
                        📦 ${escapeHtml(pNama)} (Rasio: ${escapeHtml(sp.rasio || '-')})
                    </option>`;
                });
            }

            const isPonSplitter = payload.parent_type === 'PON' || (payload.olt_id && payload.pon_port && !payload.parent_splitter_id);
            const oltId = payload.olt_id || payload.lokasi_id || '';
            const ponPort = payload.pon_port || '';

            const isDirectOdcOrOdp = payload.parent_type === 'ODC' || payload.parent_type === 'ODP';
            const parentSplitterId = payload.parent_splitter_id || (type === 'Splitter' && payload.parent_id && !isPonSplitter && !isDirectOdcOrOdp ? payload.parent_id : '');
            const parentPortOut = payload.parent_port_out || '';
            const parentRatio = payload.ratio || 4;

            return `
                ${isPonSplitter ? `
                <input type="hidden" name="lokasi_id" value="${oltId}">
                <input type="hidden" name="pon_port" value="${escapeHtml(ponPort)}">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5 mb-2">
                    <i class="bx bx-chip text-xl text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <div class="font-bold">Pasang Splitter Induk pada Port PON</div>
                        <div class="text-[11px] text-amber-800 leading-snug">
                            Splitter dari logistik akan dipasang sebagai <strong>Splitter Induk (Level 1)</strong> pada <strong>${escapeHtml(payload.olt_name || 'OLT')} (${escapeHtml(ponPort)})</strong>.
                        </div>
                    </div>
                </div>
                ` : ''}

                ${parentSplitterId ? `<input type="hidden" name="parent_splitter_id" value="${parentSplitterId}">` : ''}

                ${parentSplitterId ? `
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-start gap-2.5 mb-2">
                    <i class="bx bx-git-repo-forked text-xl text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <div class="font-bold">Splitter Bertingkat (Cascaded Splitter)</div>
                        <div class="text-[11px] text-amber-800 leading-snug">
                            Menghubungkan splitter baru ke <strong>${escapeHtml(payload.parent_name || ('Splitter #' + parentSplitterId))}</strong>
                            ${parentPortOut ? ` pada <span class="font-mono font-bold bg-amber-200 px-1.5 py-0.5 rounded text-amber-900">Port ${escapeHtml(parentPortOut)}</span>` : ''}
                        </div>
                    </div>
                </div>
                ` : (payload.parent_type === 'ODP' ? `
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-900 flex items-center gap-2 mb-2">
                    <i class="bx bx-plug text-lg text-emerald-600"></i>
                    <div>
                        <div class="font-bold">Splitter Internal ODP</div>
                        <div class="text-[11px] text-emerald-800">Menghubungkan splitter lokal (1:16, 1:8, dll.) ke box <strong>ODP #${payload.parent_id}</strong></div>
                    </div>
                </div>
                ` : (payload.parent_type === 'ODC' ? `
                <div class="p-3 bg-purple-50 rounded-xl border border-purple-200 text-xs text-purple-900 flex items-center gap-2 mb-2">
                    <i class="bx bx-cabinet text-lg text-purple-600"></i>
                    <div>
                        <div class="font-bold">Splitter Internal / Distribusi ODC</div>
                        <div class="text-[11px] text-purple-800">Memasang splitter dari logistik langsung ke dalam box <strong>ODC #${payload.parent_id}</strong></div>
                    </div>
                </div>
                ` : ''))}

                ${parentSplitterId && !parentPortOut ? `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Port Output pada Splitter Induk <span class="text-rose-500">*</span></label>
                    <select name="parent_port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                        ${buildPortOutOptions('', parentRatio, '-- Otomatis (port kosong pertama) --')}
                    </select>
                </div>
                ` : (parentPortOut ? `<input type="hidden" name="parent_port_out" value="${escapeHtml(parentPortOut)}">` : '')}

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Pilih Perangkat dari Logistik <span class="text-rose-500">*</span></label>
                    <select name="logistik_id" required onchange="onDrawerSplitterSelected(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                        ${splitterOptions}
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Rasio Port Keluaran <span class="text-rose-500">*</span></label>
                    <select id="drawer_splitter_rasio" name="rasio" required onchange="onDrawerSplitterRatioChanged(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                        <option value="1:2">1:2 (2 Output Port)</option>
                        <option value="1:4" selected>1:4 (4 Output Port)</option>
                        <option value="1:8">1:8 (8 Output Port)</option>
                        <option value="1:16" ${payload.parent_type === 'ODP' ? 'selected' : ''}>1:16 (16 Output Port)</option>
                        <option value="1:32">1:32 (32 Output Port)</option>
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Pilih Unit Fisik / Serial Number (SN)</label>
                    <select id="drawer_splitter_modem_detail_id" name="modem_detail_id" onchange="window.onSplitterSnSelectChange(this, 'drawer_splitter_sn')" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700 font-mono text-xs">
                        <option value="">-- Tanpa SN / Pilih Unit Nanti --</option>
                    </select>
                    <input type="hidden" id="drawer_splitter_sn" name="serial_number" value="">
                    <p class="text-[10px] text-slate-500 mb-0">Pilih nomor seri fisik dari logistik untuk otomatis memotong stok & menandai status TERPAKAI.</p>
                </div>
                <div id="drawer_splitterOutputsPreview" style="display:none" class="mt-2"></div>
            `;
        }

        if (type === 'Server') {
            return `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Nama Server <span class="text-rose-500">*</span></label>
                    <input type="text" name="lokasi_server" value="${escapeHtml(payload.name || '')}" required class="form-input-custom" placeholder="Contoh: Server Pusat Gedong Songo">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">IP Address</label>
                    <input type="text" name="ip" value="${escapeHtml(payload.ip || '')}" class="form-input-custom" placeholder="Contoh: 192.168.1.1">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">GPS / Link Map <span class="text-rose-500">*</span></label>
                    <input type="text" name="gps" value="${escapeHtml(payload.gps || '')}" required class="form-input-custom" placeholder="Link Maps atau Koordinat GPS">
                </div>
            `;
        }

        if (type === 'OLT') {
            let serverOptions = '<option value="" disabled>Pilih Server</option>';
            (serverListGlobal || []).forEach(s => {
                const isSelected = (payload.server_id == s.id || payload.parent_id == s.id) ? 'selected' : '';
                serverOptions += `<option value="${s.id}" ${isSelected}>${escapeHtml(s.lokasi_server)}</option>`;
            });
            const currentPon = payload.jumlah_pon || 8;

            return `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Parent (Server) <span class="text-rose-500">*</span></label>
                    <select name="lokasi_server" required class="form-input-custom bg-white">
                        ${serverOptions}
                    </select>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Nama OLT <span class="text-rose-500">*</span></label>
                    <input type="text" name="olt" value="${escapeHtml(payload.name || '')}" required class="form-input-custom" placeholder="Contoh: OLT EPON Dondong">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Jumlah Port PON <span class="text-rose-500">*</span></label>
                    <select name="jumlah_pon" required class="form-input-custom bg-white">
                        <option value="2" ${currentPon == 2 ? 'selected' : ''}>2 Port PON</option>
                        <option value="4" ${currentPon == 4 ? 'selected' : ''}>4 Port PON</option>
                        <option value="8" ${currentPon == 8 ? 'selected' : ''}>8 Port PON (Standard)</option>
                        <option value="16" ${currentPon == 16 ? 'selected' : ''}>16 Port PON</option>
                        <option value="32" ${currentPon == 32 ? 'selected' : ''}>32 Port PON</option>
                        <option value="64" ${currentPon == 64 ? 'selected' : ''}>64 Port PON</option>
                    </select>
                </div>
                ${buildHardwareLogistikFields('olt', 'drawer_olt', payload.perangkat_id, payload.modem_detail_id, payload.serial_number)}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">GPS / Link Map <span class="text-rose-500">*</span></label>
                    <input type="text" name="gps" value="${escapeHtml(payload.gps || '')}" required class="form-input-custom" placeholder="Link Maps atau GPS">
                </div>
            `;
        }

        if (type === 'ODC') {
            let oltOptions = '<option value="" disabled>Pilih OLT</option>';
            (oltListGlobal || []).forEach(o => {
                const isSelected = (payload.olt_id == o.id || payload.parent_id == o.id) ? 'selected' : '';
                const pon = o.jumlah_pon || 8;
                oltOptions += `<option value="${o.id}" data-pon="${pon}" ${isSelected}>${escapeHtml(o.nama_lokasi)} (${pon} PON)</option>`;
            });

            const currentPonCount = payload.olt_pon_count || 8;
            let ponOptions = '<option value="">-- Pilih Port PON (Opsional) --</option>';
            for (let i = 1; i <= currentPonCount; i++) {
                const pCode = `PON-${String(i).padStart(2, '0')}`;
                ponOptions += `<option value="${pCode}" ${payload.pon_port === pCode ? 'selected' : ''}>${pCode}</option>`;
            }

            let splitterOptions = '<option value="" data-rasio="">-- Tanpa Splitter / Pasang Nanti --</option>';
            if (perangkatSplitterGlobal && perangkatSplitterGlobal.length > 0) {
                perangkatSplitterGlobal.forEach(ps => {
                    splitterOptions += `<option value="${ps.id}" data-rasio="${escapeHtml(ps.rasio_default || '1:4')}" data-nama="${escapeHtml(ps.nama_perangkat)}" data-stok="${ps.stok_tersedia}">
                        📦 ${escapeHtml(ps.nama_perangkat)} (Tersedia: ${ps.stok_tersedia} unit)
                    </option>`;
                });
            } else if (splitterListGlobal && splitterListGlobal.length > 0) {
                splitterListGlobal.forEach(sp => {
                    const pNama = (sp.perangkat && sp.perangkat.nama_perangkat) ? sp.perangkat.nama_perangkat : 'Splitter';
                    splitterOptions += `<option value="${sp.logistik_id}" data-rasio="${escapeHtml(sp.rasio || '1:4')}" data-nama="${escapeHtml(pNama)}">
                        📦 ${escapeHtml(pNama)} (Rasio: ${escapeHtml(sp.rasio || '-')})
                    </option>`;
                });
            }

            let odcExistingOptions = '<option value="" disabled selected>-- Pilih ODC yang Sudah Ada --</option>';
            (odcListGlobal || []).forEach(odc => {
                const oltName = (odc.olt && odc.olt.nama_lokasi) ? odc.olt.nama_lokasi : 'Tanpa OLT';
                const ponPort = odc.pon_port || 'Tanpa PON';
                odcExistingOptions += `<option value="${odc.id}" data-name="${escapeHtml(odc.nama_odc)}" data-gps="${escapeHtml(odc.gps || '')}">
                    🏢 ${escapeHtml(odc.nama_odc)} (Saat ini: ${escapeHtml(oltName)} - ${escapeHtml(ponPort)})
                </option>`;
            });

            if (mode === 'edit') {
                return `
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Parent (OLT) <span class="text-rose-500">*</span></label>
                        <select name="olt" required onchange="onDrawerOltChange(this)" class="form-input-custom bg-white">
                            ${oltOptions}
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Port PON OLT</label>
                        <select id="drawer_odc_pon" name="pon_port" class="form-input-custom bg-white">
                            ${ponOptions}
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Nama ODC <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_odc" value="${escapeHtml(payload.name || '')}" required class="form-input-custom" placeholder="Contoh: ODC Dondong 2">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">GPS / Link Map <span class="text-rose-500">*</span></label>
                        <input type="text" name="gps" value="${escapeHtml(payload.gps || '')}" required class="form-input-custom" placeholder="Link Maps atau GPS">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Panjang Kabel (PON → ODC) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" name="panjang_kabel" value="${escapeHtml(payload.panjang_kabel || '')}" required class="form-input-custom" placeholder="Contoh: 350">
                            <span class="text-xs font-semibold text-slate-400 shrink-0">m</span>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Redaman (dBm) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="redaman" value="${escapeHtml(payload.redaman || '')}" required class="form-input-custom" placeholder="Contoh: -16.5">
                            <span class="text-xs font-semibold text-slate-400 shrink-0">dBm</span>
                        </div>
                    </div>
                    ${buildHardwareLogistikFields('odc', 'drawer_odc_edit', payload.perangkat_id, payload.modem_detail_id, payload.serial_number)}
                `;
            }

            const isExistingSplitter = !!(payload.splitter_id);
            const initialSplitterTitle = payload.pon_port
                ? `Pasang Splitter pada ${payload.pon_port} (Logistik)`
                : 'Pasang Splitter Logistik';

            const splitterSection = isExistingSplitter ? `
                <input type="hidden" name="splitter_id" value="${payload.splitter_id}">
                <input type="hidden" name="pon_port" value="${escapeHtml(payload.pon_port || '')}">
                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-900 flex items-center gap-2 mb-2">
                    <i class="bx bx-git-repo-forked text-lg text-amber-600"></i>
                    <div>
                        <div class="font-bold">Splitter Induk di PON Sudah Terpasang</div>
                        <div class="text-[11px] text-amber-800">ODC ini ditempatkan di bawah splitter induk pada <strong>${escapeHtml(payload.pon_port || 'PON')}</strong>. Pilih output port untuk ODC ini:</div>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Port Output Splitter <span class="text-rose-500">*</span></label>
                    <select name="port_out" required class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                        ${buildPortOutOptions(payload.port_out || '', payload.ratio || 32)}
                    </select>
                </div>
            ` : `
                <div id="drawer_wrapper_odc_splitter_section" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2.5 mt-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-0">
                            <i class="bx bx-git-repo-forked text-amber-600"></i> <span id="drawer_lbl_odc_splitter_title">${escapeHtml(initialSplitterTitle)}</span>
                        </label>
                        <span class="text-[10px] text-amber-700 bg-amber-100 font-semibold px-2 py-0.5 rounded-full">Stok Logistik</span>
                    </div>
                    <select id="drawer_odc_splitter" name="logistik_id" onchange="onDrawerSplitterSelected(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                        ${splitterOptions}
                    </select>

                    <div id="drawer_wrapper_odc_splitter_rasio" style="display:none;" class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Rasio Port Output Splitter</label>
                        <select id="drawer_odc_splitter_rasio" name="rasio" onchange="onDrawerSplitterRatioChanged(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                            <option value="1:2">1:2 (2 Output Port)</option>
                            <option value="1:4">1:4 (4 Output Port)</option>
                            <option value="1:8">1:8 (8 Output Port)</option>
                            <option value="1:16">1:16 (16 Output Port)</option>
                            <option value="1:32">1:32 (32 Output Port)</option>
                        </select>
                    </div>

                    <div id="drawer_wrapper_odc_splitter_sn" style="display:none;" class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Pilih Unit Fisik / SN Splitter</label>
                        <select id="drawer_odc_splitter_modem_detail_id" name="splitter_modem_detail_id" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700 font-mono text-xs">
                            <option value="">-- Tanpa SN / Pilih Unit Nanti --</option>
                        </select>
                        <p class="text-[10px] text-slate-500 mb-0">Unit fisik dari logistik akan otomatis ditandai TERPAKAI.</p>
                    </div>

                    <div id="drawer_wrapper_odc_port_out" style="display:none;" class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Port Output Splitter untuk ODC Ini <span class="text-[9px] text-amber-600 font-sans">(jika pasang splitter di PON)</span></label>
                        <select id="drawer_odc_port_out" name="port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium">
                            <option value="">-- Otomatis (port kosong pertama) --</option>
                        </select>
                    </div>

                    <p class="text-[10.5px] text-amber-700 mb-0">Jika Port PON dipilih, splitter menjadi <strong>splitter induk di PON port</strong> dan ODC ini menempatinya. Port output bisa diisi manual atau <strong>dikosongkan untuk otomatis</strong>.</p>
                    <div id="drawer_splitterOutputsPreview" style="display:none" class="mt-2"></div>
                </div>
            `;

            return `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Parent (OLT) <span class="text-rose-500">*</span></label>
                    <select name="olt" required onchange="onDrawerOltChange(this)" class="form-input-custom bg-white" ${isExistingSplitter ? 'disabled' : ''}>
                        ${oltOptions}
                    </select>
                    ${isExistingSplitter ? `<input type="hidden" name="olt" value="${payload.parent_id || payload.olt_id || ''}">` : ''}
                </div>
                ${isExistingSplitter ? '' : `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Port PON OLT</label>
                    <select id="drawer_odc_pon" name="pon_port" onchange="onDrawerOdcPonSelected(this)" class="form-input-custom bg-white">
                        ${ponOptions}
                    </select>
                </div>
                `}

                ${splitterSection}

                <!-- Pilihan ODC Ada vs Baru -->
                <div class="space-y-2 pt-1 border-t border-slate-200">
                    <label class="block text-xs font-bold text-slate-700">Pilihan ODC <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label id="drawer_lbl_odc_existing" class="flex items-center gap-2 p-2.5 rounded-xl border-2 border-purple-500 bg-purple-50/80 cursor-pointer text-xs font-bold text-purple-900 transition-all shadow-xs">
                            <input type="radio" name="odc_source" value="existing" onchange="onOdcSourceChange(this, 'drawer')" checked class="text-purple-600 focus:ring-purple-500">
                            <span class="flex items-center gap-1.5"><i class="bx bx-list-check text-base text-purple-700"></i> Pilih ODC Ada</span>
                        </label>
                        <label id="drawer_lbl_odc_new" class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer text-xs font-medium text-slate-700 transition-all">
                            <input type="radio" name="odc_source" value="new" onchange="onOdcSourceChange(this, 'drawer')" class="text-purple-600 focus:ring-purple-500">
                            <span class="flex items-center gap-1.5"><i class="bx bx-plus-circle text-base text-slate-500"></i> Input Manual Baru</span>
                        </label>
                    </div>

                    <!-- Wrapper Pilih ODC Ada -->
                    <div id="drawer_wrapper_odc_existing" class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-700">Pilih ODC Terdaftar <span class="text-rose-500">*</span></label>
                        <select id="drawer_odc_existing_id" name="odc_id" required class="form-input-custom border-purple-300 focus:border-purple-500 bg-white font-medium text-slate-800">
                            ${odcExistingOptions}
                        </select>
                        <p class="text-[11px] text-slate-500 mb-0">ODC yang dipilih akan dihubungkan ke port PON / Splitter ini.</p>
                    </div>

                    <!-- Wrapper Input Manual ODC Baru -->
                    <div id="drawer_wrapper_odc_new" style="display:none;" class="space-y-2">
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">Nama ODC Baru <span class="text-rose-500">*</span></label>
                            <input type="text" id="drawer_f_odc_name" name="nama_odc" value="${escapeHtml(payload.name || '')}" required class="form-input-custom" placeholder="Contoh: ODC Dondong 2">
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-semibold text-slate-700">GPS / Link Map <span class="text-rose-500">*</span></label>
                            <input type="text" name="gps" value="${escapeHtml(payload.gps || '')}" required class="form-input-custom" placeholder="Link Maps atau GPS">
                        </div>
                        ${buildHardwareLogistikFields('odc', 'drawer_odc_new', payload.perangkat_id, payload.modem_detail_id, payload.serial_number)}
                    </div>

                    <div class="space-y-1 pt-1">
                        <label class="block text-xs font-semibold text-slate-700">Panjang Kabel (PON → ODC) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" name="panjang_kabel" value="${payload.panjang_kabel || ''}" required class="form-input-custom" placeholder="Contoh: 350">
                            <span class="text-xs font-semibold text-slate-400 shrink-0">m</span>
                        </div>
                    </div>

                    <div class="space-y-1 pt-1">
                        <label class="block text-xs font-semibold text-slate-700">Redaman (dBm) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2">
                            <input type="text" name="redaman" value="${escapeHtml(payload.redaman || '')}" required class="form-input-custom" placeholder="Contoh: -16.5">
                            <span class="text-xs font-semibold text-slate-400 shrink-0">dBm</span>
                        </div>
                    </div>
                </div>
            `;
        }

        if (type === 'ODP') {
            const selectedOdcId = payload.odc_id || payload.parent_id || '';
            let odcOptions = '<option value="" disabled>Pilih ODC</option>';
            (odcListGlobal || []).forEach(odc => {
                const isSelected = (selectedOdcId == odc.id) ? 'selected' : '';
                odcOptions += `<option value="${odc.id}" ${isSelected}>${escapeHtml(odc.nama_odc)}</option>`;
            });

            const currentOdcSplitters = odcSplittersMap[selectedOdcId] || [];
            let splitterOptions = '<option value="">-- Di Luar Splitter (Langsung ke ODC) --</option>';
            currentOdcSplitters.forEach(sp => {
                const snText = sp.sn ? ` - SN: ${escapeHtml(sp.sn)}` : '';
                splitterOptions += `<option value="${sp.id}" data-rasio="${escapeHtml(sp.rasio)}" ${payload.splitter_id == sp.id ? 'selected' : ''}>📦 ${escapeHtml(sp.nama)} (Rasio: ${escapeHtml(sp.rasio)})${snText}</option>`;
            });

            const currentSplitter = currentOdcSplitters.find(s => s.id == payload.splitter_id);
            const currentRatioNum = currentSplitter ? parseRatioNum(currentSplitter.rasio, 4) : 0;

            let portOptions = '<option value="">-- Pilih Port Output --</option>';
            if (currentRatioNum > 0) {
                for (let i = 1; i <= currentRatioNum; i++) {
                    const portName = `OUT-${String(i).padStart(2, '0')}`;
                    portOptions += `<option value="${portName}" ${payload.port_out === portName ? 'selected' : ''}>Port ${portName}</option>`;
                }
            }

            const showSplitterBox = currentOdcSplitters.length > 0 || payload.splitter_id;
            const showPortBox = payload.splitter_id && currentRatioNum > 0;

            return `
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Parent (ODC) <span class="text-rose-500">*</span></label>
                    <select name="odc" id="drawer_odp_parent_odc" required onchange="onDrawerOdcChange(this)" class="form-input-custom bg-white">
                        ${odcOptions}
                    </select>
                </div>

                <div id="drawer_wrapper_odp_splitter" class="p-3 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2.5" style="${showSplitterBox ? '' : 'display:none;'}">
                    <div>
                        <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-1">
                            <i class="bx bx-git-repo-forked text-amber-600"></i> Hubungkan ke Splitter (ODC)
                        </label>
                        <select id="drawer_odp_splitter" name="splitter_id" onchange="onDrawerOdpSplitterChange(this)" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                            ${splitterOptions}
                        </select>
                    </div>

                    <div id="drawer_wrapper_odp_port" style="${showPortBox ? '' : 'display:none;'}">
                        <label class="block text-xs font-bold text-amber-900 flex items-center gap-1 mb-1">
                            <i class="bx bx-log-out-circle text-amber-600"></i> Port Output Splitter
                        </label>
                        <select id="drawer_odp_port" name="port_out" class="form-input-custom border-amber-300 focus:border-amber-500 bg-white font-medium text-slate-700">
                            ${portOptions}
                        </select>
                    </div>
                    <p class="text-[10.5px] text-amber-700 mb-0">Pilih splitter dan port keluaran untuk memasukkan ODP ke dalam jalur splitter.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Nama ODP <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_odp" value="${escapeHtml(payload.name || '')}" required class="form-input-custom" placeholder="Contoh: ODP Dondong RW 04">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Rasio Splitter Internal ODP (Opsional)</label>
                    <select name="rasio" class="form-input-custom bg-white">
                        <option value="">-- Tanpa Splitter / Bawaan --</option>
                        <option value="1:2" ${payload.rasio === '1:2' ? 'selected' : ''}>1:2</option>
                        <option value="1:4" ${payload.rasio === '1:4' ? 'selected' : ''}>1:4</option>
                        <option value="1:8" ${payload.rasio === '1:8' ? 'selected' : ''}>1:8</option>
                        <option value="1:16" ${payload.rasio === '1:16' || !payload.rasio ? 'selected' : ''}>1:16</option>
                    </select>
                </div>
                ${buildHardwareLogistikFields('odp', 'drawer_odp', payload.perangkat_id, payload.modem_detail_id, payload.serial_number)}
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">GPS / Link Map <span class="text-rose-500">*</span></label>
                    <input type="text" name="gps" value="${escapeHtml(payload.gps || '')}" required class="form-input-custom" placeholder="Link Maps atau Koordinat GPS">
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Panjang Kabel (ODC → ODP) <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="number" step="0.01" min="0" name="panjang_kabel" value="${payload.panjang_kabel || ''}" required class="form-input-custom" placeholder="Contoh: 120">
                        <span class="text-xs font-semibold text-slate-400 shrink-0">m</span>
                    </div>
                </div>
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700">Redaman (dBm) <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="redaman" value="${escapeHtml(payload.redaman || '')}" required class="form-input-custom" placeholder="Contoh: -19.2">
                        <span class="text-xs font-semibold text-slate-400 shrink-0">dBm</span>
                    </div>
                </div>
            `;
        }

        return '';
    }

    function handleDrawerSubmit(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-drawer-submit');
        const submitText = document.getElementById('btn-drawer-submit-text');
        const targetUrl = document.getElementById('drawer_target_url').value;
        const method = document.getElementById('drawer_action_method').value;

        // Validation for required fields
        const requiredFields = e.target.querySelectorAll('[required]');
        for (let el of requiredFields) {
            if (el.closest('[style*="display:none"]') || el.closest('[style*="display: none"]')) continue;
            if (!el.value || !el.value.trim()) {
                const label = el.closest('div')?.querySelector('label')?.textContent?.replace('*', '').trim() || 'Bidang';
                showAlert(`Harap lengkapi "${label}" yang wajib diisi!`, 'error');
                if (el.tomselect) el.tomselect.focus();
                else el.focus();
                return;
            }
        }

        submitBtn.disabled = true;
        submitText.textContent = 'Menyimpan...';

        const fd = new FormData(e.target);

        fetch(targetUrl, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
        .then(r => {
            if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Terjadi kesalahan'); });
            return r.json();
        })
        .then(res => {
            showAlert(res.message || 'Data berhasil disimpan!', 'success');
            closeDrawer();
            loadTree();
        })
        .catch(err => {
            showAlert(err.message || 'Gagal menyimpan data.', 'error');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitText.textContent = 'Simpan Perubahan';
        });
    }

    function deleteNode(type, id) {
        if (!confirm(`Apakah Anda yakin ingin menghapus ${type} #${id}?`)) return;

        const deleteUrl = `/${type.toLowerCase()}/delete/${id}`;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Gagal menghapus node'); });
            return r.json();
        })
        .then(res => {
            showAlert(res.message || `${type} berhasil dihapus!`, 'success');
            loadTree();
        })
        .catch(err => {
            showAlert(err.message || 'Gagal menghapus node.', 'error');
        });
    }

    function detachSplitter(id) {
        if (!confirm('Apakah Anda yakin ingin melepas Splitter ini dan mengembalikannya ke stok logistik?')) return;

        fetch(`/splitter/delete/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) throw new Error('Gagal melepas splitter');
            return r.json();
        })
        .then((res) => {
            showAlert(res.message || 'Splitter berhasil dilepas ke stok logistik!', 'success');
            loadTree();
        })
        .catch(() => {
            showAlert('Gagal melepas splitter.', 'error');
        });
    }

    /* ================================================================================= */
    /* MODAL KHUSUS PENGELOLAAN & AKSI PORT PON OLT                                      */
    /* ================================================================================= */

    function openEmptyPonActionModal(payload) {
        const dialog = document.getElementById('modal-dialog');
        const titleEl = document.getElementById('modal-title');
        const subtitleEl = document.getElementById('modal-subtitle');
        const iconEl = document.getElementById('modal-icon');
        const iconBg = document.getElementById('modal-icon-bg');
        const formBody = document.getElementById('drawer-form-body');

        iconBg.style.background = 'linear-gradient(135deg, #dcfce7, #bbf7d0)';
        iconBg.style.color = '#15803d';
        iconEl.className = 'bx bx-chip';

        titleEl.textContent = `Aksi Port ${payload.pon_port}`;
        subtitleEl.textContent = `${payload.olt_name || 'OLT'} — Pilih jenis konfigurasi untuk port ini:`;

        formBody.innerHTML = `
            <div class="space-y-3">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-700 flex items-center justify-between">
                    <div>
                        <span class="text-slate-400 font-normal">Perangkat Induk:</span>
                        <strong class="ml-1">${escapeHtml(payload.olt_name || 'OLT')}</strong>
                    </div>
                    <span class="px-2 py-0.5 font-mono text-[11px] font-bold bg-emerald-100 text-emerald-800 rounded-md">${escapeHtml(payload.pon_port)} (Kosong)</span>
                </div>

                <div class="grid grid-cols-1 gap-2.5">
                    <!-- Option 1: Pasang Splitter ke PON -->
                    <div onclick="openDrawer('attach_splitter', 'Splitter', { parent_type: 'PON', olt_id: ${payload.olt_id}, pon_port: '${escapeHtml(payload.pon_port)}', olt_name: '${escapeHtml(payload.olt_name || '')}', olt_pon_count: ${payload.olt_pon_count || 8} })" 
                         class="p-4 rounded-xl border-2 border-amber-200 bg-amber-50/50 hover:bg-amber-100/60 hover:border-amber-400 cursor-pointer transition-all duration-150 group shadow-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 grid place-items-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                <i class="bx bx-git-repo-forked"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-amber-900 mb-0.5">Pasang Splitter Induk ke PON</h4>
                                    <span class="text-[10px] font-bold text-amber-700 bg-amber-200/80 px-2 py-0.5 rounded-full">Rekomendasi FTTH</span>
                                </div>
                                <p class="text-[11px] text-slate-600 mb-0 leading-relaxed">
                                    Pasang splitter rasio (1:2, 1:4, 1:8, 1:16, 1:32) dari logistik pada port PON ini. Port output nantinya bisa dipasangi beberapa ODC / ODP bertingkat.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Hubungkan ODC Langsung ke PON -->
                    <div onclick="openDrawer('add', 'ODC', { parent_id: ${payload.olt_id}, pon_port: '${escapeHtml(payload.pon_port)}', olt_pon_count: ${payload.olt_pon_count || 8} })" 
                         class="p-4 rounded-xl border border-slate-200 bg-white hover:bg-purple-50/50 hover:border-purple-300 cursor-pointer transition-all duration-150 group shadow-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 grid place-items-center text-xl shrink-0 group-hover:scale-105 transition-transform">
                                <i class="bx bx-cabinet"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xs font-bold text-slate-800 group-hover:text-purple-900 mb-0.5">Hubungkan ODC Langsung ke PON</h4>
                                <p class="text-[11px] text-slate-600 mb-0 leading-relaxed">
                                    Buat ODC baru dan hubungkan langsung 1-to-1 ke port PON ini tanpa splitter di level OLT.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const footerSubmitBtn = document.getElementById('btn-drawer-submit');
        if (footerSubmitBtn) footerSubmitBtn.style.display = 'none';

        if (dialog) {
            if (typeof dialog.showModal === 'function') dialog.showModal();
            else dialog.setAttribute('open', '');
        }
    }

    function openManagePonModal(payload) {
        const dialog = document.getElementById('modal-dialog');
        const titleEl = document.getElementById('modal-title');
        const subtitleEl = document.getElementById('modal-subtitle');
        const iconEl = document.getElementById('modal-icon');
        const iconBg = document.getElementById('modal-icon-bg');
        const formBody = document.getElementById('drawer-form-body');

        iconBg.style.background = 'linear-gradient(135deg, #fef3c7, #fde68a)';
        iconBg.style.color = '#b45309';
        iconEl.className = 'bx bx-chip';

        titleEl.textContent = `Kelola Port ${payload.pon_port}`;
        subtitleEl.textContent = `${payload.olt_name || 'OLT'} — Rincian perangkat & manajemen splitter/ODC`;

        const attachedList = payload.attached || [];
        const rootSplitter = attachedList.find(a => a.type === 'Splitter');
        const directOdcs = attachedList.filter(a => a.type === 'ODC');

        let contentHtml = '';

        if (rootSplitter) {
            const ratioNum = rootSplitter.ratio || parseRatioNum(rootSplitter.rasio, 4);
            const children = rootSplitter.children || [];

            // Build output port status list
            let portStatusItems = [];
            let emptyCount = 0;
            for (let i = 1; i <= ratioNum; i++) {
                const pOut = `OUT-${String(i).padStart(2, '0')}`;
                const childNode = children.find(c => c.port_out === pOut) || (children[i - 1] && !children[i - 1].port_out ? children[i - 1] : null);
                if (childNode) {
                    portStatusItems.push(`
                        <div class="flex items-center justify-between p-2 rounded-lg bg-white border border-slate-200 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-300">${pOut}</span>
                                <span class="font-semibold text-slate-800">${escapeHtml(childNode.name)}</span>
                                <span class="text-[9px] px-1.5 py-0.2 rounded-full font-bold uppercase" style="background:${getNodeBadgeBg(childNode.type)};color:${getNodeBadgeColor(childNode.type)}">${childNode.type}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                ${childNode.type === 'ODC' ? `<button type="button" onclick="openDrawer('edit', 'ODC', ${JSON.stringify(childNode).replace(/"/g, '&quot;')})" class="px-2 py-0.5 text-[10px] font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 rounded border border-purple-200">Edit ODC</button>` : ''}
                                ${childNode.type === 'Splitter' ? `<button type="button" onclick="openDrawer('edit', 'Splitter', ${JSON.stringify(childNode).replace(/"/g, '&quot;')})" class="px-2 py-0.5 text-[10px] font-bold text-amber-700 bg-amber-50 hover:bg-amber-100 rounded border border-amber-200">Edit Splitter</button>` : ''}
                            </div>
                        </div>
                    `);
                } else {
                    emptyCount++;
                    portStatusItems.push(`
                        <div class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 border border-emerald-200 text-xs">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded border border-emerald-300">${pOut}</span>
                                <span class="text-emerald-700 font-medium">Port Kosong</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_splitter_id: ${rootSplitter.db_id}, parent_port_out: '${pOut}', parent_name: '${escapeHtml(rootSplitter.name)}', ratio: ${ratioNum} })" class="px-2 py-0.5 text-[10.5px] font-bold text-amber-800 bg-amber-100 hover:bg-amber-200 rounded transition-all">
                                    +Splitter
                                </button>
                                <button type="button" onclick="openDrawer('add', 'ODC', { parent_id: ${payload.olt_id}, pon_port: '${escapeHtml(payload.pon_port)}', splitter_id: ${rootSplitter.db_id}, port_out: '${pOut}', ratio: ${ratioNum} })" class="px-2 py-0.5 text-[10.5px] font-bold text-purple-800 bg-purple-100 hover:bg-purple-200 rounded transition-all">
                                    +ODC
                                </button>
                            </div>
                        </div>
                    `);
                }
            }

            contentHtml = `
                <div class="space-y-3.5">
                    <!-- Card Rincian Splitter Induk -->
                    <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-amber-200/70 text-amber-800 grid place-items-center text-lg font-bold">
                                    <i class="bx bx-git-repo-forked"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-amber-950 mb-0">${escapeHtml(rootSplitter.name)}</h4>
                                    <div class="text-[10.5px] text-amber-800">Splitter Induk Level 1 • Rasio: <strong>${escapeHtml(rootSplitter.rasio)}</strong> ${rootSplitter.serial ? `• SN: ${escapeHtml(rootSplitter.serial)}` : ''}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" onclick="openDrawer('edit', 'Splitter', ${JSON.stringify(rootSplitter).replace(/"/g, '&quot;')})" class="px-2.5 py-1 text-xs font-bold text-amber-800 bg-amber-200/80 hover:bg-amber-300 rounded-lg transition-all flex items-center gap-1">
                                    <i class="bx bx-pencil text-xs"></i> Edit Splitter
                                </button>
                                <button type="button" onclick="detachSplitter(${rootSplitter.db_id})" class="px-2 py-1 text-xs font-bold text-rose-700 bg-rose-100 hover:bg-rose-200 rounded-lg transition-all" title="Lepas Splitter dari PON">
                                    <i class="bx bx-unlink text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Status Port Output Splitter -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold text-slate-700 flex items-center gap-1 mb-0">
                                <i class="bx bx-network-chart text-amber-600"></i> Distribusi Port Output Splitter (${ratioNum - emptyCount}/${ratioNum} Terisi)
                            </label>
                            ${emptyCount > 0 ? `<span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">${emptyCount} port kosong</span>` : ''}
                        </div>
                        <div class="space-y-1.5 max-h-[220px] overflow-y-auto custom-scroll pr-1">
                            ${portStatusItems.join('')}
                        </div>
                    </div>

                    <!-- Quick Action Buttons -->
                    <div class="pt-2 border-t border-slate-200 flex items-center gap-2">
                        <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_splitter_id: ${rootSplitter.db_id}, parent_name: '${escapeHtml(rootSplitter.name)}', ratio: ${ratioNum} })" class="flex-1 py-2 px-3 bg-amber-100 hover:bg-amber-200 text-amber-900 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1 cursor-pointer">
                            <i class="bx bx-git-repo-forked"></i> + Splitter Bertingkat
                        </button>
                        <button type="button" onclick="openDrawer('add', 'ODC', { parent_id: ${payload.olt_id}, pon_port: '${escapeHtml(payload.pon_port)}', splitter_id: ${rootSplitter.db_id}, ratio: ${ratioNum} })" class="flex-1 py-2 px-3 bg-purple-100 hover:bg-purple-200 text-purple-900 text-xs font-bold rounded-xl transition-all flex items-center justify-center gap-1 cursor-pointer">
                            <i class="bx bx-cabinet"></i> + Hubungkan ODC
                        </button>
                    </div>
                </div>
            `;
        } else if (directOdcs.length > 0) {
            const odc = directOdcs[0];
            contentHtml = `
                <div class="space-y-3.5">
                    <div class="p-3.5 bg-purple-50/80 border border-purple-200 rounded-xl space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-purple-200/70 text-purple-800 grid place-items-center text-lg font-bold">
                                    <i class="bx bx-cabinet"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-purple-950 mb-0">${escapeHtml(odc.name)}</h4>
                                    <div class="text-[10.5px] text-purple-800">ODC terhubung langsung ke Port <strong>${escapeHtml(payload.pon_port)}</strong> (Tanpa Splitter Induk)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-xs font-semibold text-slate-700">Aksi untuk Port PON ini:</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" onclick="openDrawer('edit', 'ODC', ${JSON.stringify(odc).replace(/"/g, '&quot;')})" class="p-2.5 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl text-xs font-bold text-purple-800 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="bx bx-pencil"></i> Edit Data ODC
                            </button>
                            <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_type: 'PON', olt_id: ${payload.olt_id}, pon_port: '${escapeHtml(payload.pon_port)}', olt_name: '${escapeHtml(payload.olt_name || '')}' })" class="p-2.5 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl text-xs font-bold text-amber-800 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="bx bx-git-repo-forked"></i> Pasang Splitter di PON
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        formBody.innerHTML = contentHtml;

        const footerSubmitBtn = document.getElementById('btn-drawer-submit');
        if (footerSubmitBtn) footerSubmitBtn.style.display = 'none';

        if (dialog) {
            if (typeof dialog.showModal === 'function') dialog.showModal();
            else dialog.setAttribute('open', '');
        }
    }

    /* ================================================================================= */
    /* TOPOLOGY TREE RENDERING WITH ACTION BAR & PORT MATRIX                             */
    /* ================================================================================= */

    function createNodeElement(node) {
        const wrapper = document.createElement('div');
        wrapper.className = 'node-card-wrapper' + (node.type === 'Server' ? ' mb-3' : '');
        wrapper.setAttribute('data-node-id', node.id);

        const box = document.createElement('div');
        box.id = node.id;
        box.className = 'node-box ' + getNodeClass(node.type) + (node.type === 'Server' ? ' mb-2' : '');
        box.setAttribute('data-name', (node.name || '').toLowerCase());
        box.setAttribute('data-ip', (node.ip || '').toLowerCase());

        let kids = [];
        if (node.type === 'ODC') {
            (node.splitters || []).forEach(s => kids.push(s));
            (node.children || []).forEach(c => kids.push(c));
        } else if (node.type === 'ODP') {
            (node.splitters || []).forEach(s => kids.push(s));
            (node.children || []).forEach(c => kids.push(c));
        } else {
            kids = node.children || [];
        }

        // Clean inline metadata items
        let metaItems = [];
        if (node.type === 'Server' && node.ip) {
            metaItems.push(`<span class="node-meta-item font-mono"><i class="bx bx-globe text-[11px] text-slate-400"></i> ${escapeHtml(node.ip)}</span>`);
        }
        if (node.type === 'OLT') {
            const ponCount = node.jumlah_pon || 8;
            metaItems.push(`<span class="node-meta-item text-blue-600 font-semibold"><i class="bx bx-chip text-[11px]"></i> ${ponCount} PON</span>`);
        }
        if (node.type === 'ODC' && node.pon_port) {
            metaItems.push(`<span class="node-meta-item text-indigo-600 font-mono font-bold"><i class="bx bx-plug text-[11px]"></i> ${escapeHtml(node.pon_port)}</span>`);
        }
        if (node.type === 'ODC' && node.panjang_kabel) {
            metaItems.push(`<span class="node-meta-item text-slate-500 font-mono font-semibold"><i class="bx bx-ruler text-[11px] text-cyan-600"></i> ${escapeHtml(node.panjang_kabel)} m</span>`);
        }
        if (node.type === 'ODC' && node.redaman) {
            const rdOdc = String(node.redaman).trim();
            const rdOdcText = rdOdc.toLowerCase().includes('db') ? rdOdc : `${rdOdc} dBm`;
            metaItems.push(`<span class="node-meta-item text-rose-600 font-mono font-semibold" data-bs-toggle="tooltip" data-bs-placement="top" title="Redaman ODC"><i class="bx bx-broadcast text-[11px] text-rose-500"></i> ${escapeHtml(rdOdcText)}</span>`);
        }
        if (node.type === 'Splitter' && node.rasio) {
            metaItems.push(`<span class="node-meta-item text-amber-700 font-mono font-bold"><i class="bx bx-git-repo-forked text-[11px]"></i> ${escapeHtml(node.rasio)}</span>`);
        }
        if (node.type === 'Splitter' && node.pon_port) {
            metaItems.push(`<span class="node-meta-item text-indigo-600 font-mono font-bold"><i class="bx bx-plug text-[11px]"></i> ${escapeHtml(node.pon_port)}</span>`);
        }
        if (node.type === 'ODP' && node.port_out) {
            metaItems.push(`<span class="node-meta-item text-emerald-700 font-mono font-bold"><i class="bx bx-log-out-circle text-[11px]"></i> ${escapeHtml(node.port_out)}</span>`);
        }
        if (node.type === 'ODP' && node.panjang_kabel) {
            metaItems.push(`<span class="node-meta-item text-slate-500 font-mono font-semibold"><i class="bx bx-ruler text-[11px] text-cyan-600"></i> ${escapeHtml(node.panjang_kabel)} m</span>`);
        }
        if (node.type === 'ODP' && node.redaman) {
            const rdOdp = String(node.redaman).trim();
            const rdOdpText = rdOdp.toLowerCase().includes('db') ? rdOdp : `${rdOdp} dBm`;
            metaItems.push(`<span class="node-meta-item text-rose-600 font-mono font-semibold" data-bs-toggle="tooltip" data-bs-placement="top" title="Redaman ODP"><i class="bx bx-broadcast text-[11px] text-rose-500"></i> ${escapeHtml(rdOdpText)}</span>`);
        }
        if ((node.type === 'OLT' || node.type === 'ODC' || node.type === 'ODP') && node.serial_number) {
            const pInfo = node.perangkat_nama ? `${node.perangkat_nama} • ` : '';
            metaItems.push(`<span class="node-meta-item text-slate-600 font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200" data-bs-toggle="tooltip" data-bs-placement="top" title="Logistik: ${escapeHtml(pInfo)}${escapeHtml(node.serial_number)}"><i class="bx bx-barcode text-[11px] text-blue-600"></i> ${escapeHtml(node.serial_number)}</span>`);
        }
        if (node.gps) {
            const gpsUrl = node.gps.startsWith('http') ? node.gps : `https://maps.google.com/?q=${encodeURIComponent(node.gps)}`;
            metaItems.push(`<a href="${escapeHtml(gpsUrl)}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Buka Lokasi di Google Maps" class="node-meta-item text-blue-500 hover:text-blue-700 no-underline transition-colors"><i class="bx bx-map-pin text-[11px] text-rose-500"></i> Map</a>`);
        }
        if (node.type !== 'Splitter' && kids.length > 0) {
            metaItems.push(`<span class="node-meta-item text-slate-400 font-medium">• ${kids.length} cabang</span>`);
        }

        const hasSubNodes = (node.type === 'Splitter') || (kids.length > 0);

        // Minimalist Quick Action Buttons (Mini Icon Buttons with Tooltips)
        let actionButtonsHtml = '';
        if (node.type === 'Server') {
            actionButtonsHtml = `
                <button type="button" onclick="openDrawer('add', 'OLT', { parent_id: ${node.db_id} })" class="node-btn-action btn-add" data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah OLT baru ke Server ini"><i class="bx bx-plus"></i></button>
                <button type="button" onclick="openDrawer('edit', 'Server', ${JSON.stringify(node).replace(/"/g, '&quot;')})" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data Server"><i class="bx bx-pencil"></i></button>
                <button type="button" onclick="deleteNode('Server', ${node.db_id})" class="node-btn-action btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus Server ini"><i class="bx bx-trash"></i></button>
            `;
        } else if (node.type === 'OLT') {
            actionButtonsHtml = `
                <button type="button" onclick="openDrawer('add', 'ODC', { parent_id: ${node.db_id}, olt_pon_count: ${node.jumlah_pon || 8} })" class="node-btn-action btn-add" data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah ODC ke OLT ini"><i class="bx bx-plus"></i></button>
                <button type="button" onclick="openDrawer('edit', 'OLT', ${JSON.stringify(node).replace(/"/g, '&quot;')})" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data OLT"><i class="bx bx-pencil"></i></button>
                <button type="button" onclick="deleteNode('OLT', ${node.db_id})" class="node-btn-action btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus OLT ini"><i class="bx bx-trash"></i></button>
            `;
        } else if (node.type === 'ODC') {
            actionButtonsHtml = `
                <button type="button" onclick="openDrawer('add', 'ODP', { parent_id: ${node.db_id} })" class="node-btn-action btn-add" data-bs-toggle="tooltip" data-bs-placement="top" title="Tambah ODP langsung ke ODC"><i class="bx bx-plus"></i></button>
                <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_id: ${node.db_id}, parent_type: 'ODC' })" class="node-btn-action btn-splitter" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang Splitter dari Logistik ke ODC"><i class="bx bx-git-repo-forked"></i></button>
                <button type="button" onclick="openDrawer('edit', 'ODC', ${JSON.stringify(node).replace(/"/g, '&quot;')})" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data ODC"><i class="bx bx-pencil"></i></button>
                <button type="button" onclick="deleteNode('ODC', ${node.db_id})" class="node-btn-action btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus ODC ini"><i class="bx bx-trash"></i></button>
            `;
        } else if (node.type === 'ODP') {
            actionButtonsHtml = `
                <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_id: ${node.db_id}, parent_type: 'ODP' })" class="node-btn-action btn-splitter" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang Splitter Internal ODP (1:16, 1:8)"><i class="bx bx-git-repo-forked"></i></button>
                <button type="button" onclick="openDrawer('edit', 'ODP', ${JSON.stringify(node).replace(/"/g, '&quot;')})" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data ODP"><i class="bx bx-pencil"></i></button>
                <button type="button" onclick="deleteNode('ODP', ${node.db_id})" class="node-btn-action btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus ODP ini"><i class="bx bx-trash"></i></button>
            `;
        } else if (node.type === 'Splitter') {
            const isRootPonSplitter = node.pon_port && !node.odc_id;
            actionButtonsHtml = `
                <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_splitter_id: ${node.db_id}, parent_name: '${escapeHtml(node.name)}', ratio: ${node.ratio || 4} })" class="node-btn-action btn-splitter" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang Splitter Anak (Cascade)"><i class="bx bx-git-repo-forked"></i></button>
                ${isRootPonSplitter ? `
                <button type="button" onclick="openDrawer('add', 'ODC', { parent_id: ${node.lokasi_id}, pon_port: '${escapeHtml(node.pon_port)}', splitter_id: ${node.db_id}, ratio: ${node.ratio || 4} })" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang ODC pada Splitter PON ini"><i class="bx bx-cabinet"></i></button>
                ` : ''}
                <button type="button" onclick="openDrawer('add', 'ODP', { parent_id: '${node.odc_id || ''}', splitter_id: ${node.db_id} })" class="node-btn-action btn-add" data-bs-toggle="tooltip" data-bs-placement="top" title="Hubungkan ODP ke Splitter ini"><i class="bx bx-plus"></i></button>
                <button type="button" onclick="openDrawer('edit', 'Splitter', ${JSON.stringify(node).replace(/"/g, '&quot;')})" class="node-btn-action" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Data Splitter (Rasio / SN)"><i class="bx bx-pencil"></i></button>
                <button type="button" onclick="detachSplitter(${node.db_id})" class="node-btn-action btn-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Lepas Splitter & Kembalikan ke Logistik"><i class="bx bx-unlink"></i></button>
            `;
        }

        box.innerHTML = `
            <div class="node-icon-bg">
                <i class="${getNodeIcon(node.type)}"></i>
            </div>
            <div class="flex-1 min-w-0 pr-1">
                <div class="flex items-center gap-1.5 flex-nowrap">
                    <span class="node-title-text" title="${escapeHtml(node.name)}">${escapeHtml(node.name)}</span>
                    <span class="node-type-pill shrink-0" style="background:${getNodeBadgeBg(node.type)};color:${getNodeBadgeColor(node.type)}">${node.type}</span>
                </div>
                <div class="node-meta-row">
                    ${metaItems.join('<span class="text-slate-300">•</span>')}
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <div class="node-action-group">
                    ${actionButtonsHtml}
                </div>
                ${hasSubNodes ? `
                    <button type="button" class="toggle-btn" onclick="toggleCollapseNode(this)" data-bs-toggle="tooltip" data-bs-placement="top" title="Buka / Tutup Cabang">
                        <i class="bx bx-chevron-down"></i>
                    </button>
                ` : ''}
            </div>
        `;

        if (node.type === 'OLT') {
            const ponCount = node.jumlah_pon || 8;
            const childOdcs = node.children || [];
            
            const usedPons = {};
            childOdcs.forEach(odc => {
                if (odc.pon_port) {
                    if (!usedPons[odc.pon_port]) usedPons[odc.pon_port] = [];
                    usedPons[odc.pon_port].push(odc);
                }
            });

            let ponPortButtons = [];
            let emptyCount = 0;
            for (let i = 1; i <= ponCount; i++) {
                const pCode = `PON-${String(i).padStart(2, '0')}`;
                const attached = usedPons[pCode];
                if (attached && attached.length > 0) {
                    const hasSplitter = attached.some(a => a.type === 'Splitter');
                    const odcNames = attached.map(o => escapeHtml(o.name)).join(', ');
                    const payloadJson = JSON.stringify({
                        olt_id: node.db_id,
                        olt_name: node.name,
                        pon_port: pCode,
                        olt_pon_count: ponCount,
                        attached: attached
                    }).replace(/"/g, '&quot;');

                    if (hasSplitter) {
                        ponPortButtons.push(`
                            <button type="button" onclick="openManagePonModal(${payloadJson})" class="pon-port-chip filled has-splitter" data-bs-toggle="tooltip" data-bs-placement="top" title="${pCode}: ${odcNames} (Splitter Terpasang) — Klik untuk Kelola PON">
                                <i class="bx bx-git-repo-forked text-[10px] text-amber-700"></i> ${pCode}
                            </button>
                        `);
                    } else {
                        ponPortButtons.push(`
                            <button type="button" onclick="openManagePonModal(${payloadJson})" class="pon-port-chip filled" data-bs-toggle="tooltip" data-bs-placement="top" title="${pCode}: ${odcNames} (ODC Terpasang) — Klik untuk Kelola PON">
                                <i class="bx bx-check-circle text-[10px] text-blue-600"></i> ${pCode}
                            </button>
                        `);
                    }
                } else {
                    emptyCount++;
                    const emptyPayloadJson = JSON.stringify({
                        olt_id: node.db_id,
                        olt_name: node.name,
                        pon_port: pCode,
                        olt_pon_count: ponCount
                    }).replace(/"/g, '&quot;');
                    ponPortButtons.push(`
                        <button type="button" onclick="openEmptyPonActionModal(${emptyPayloadJson})" class="pon-port-chip empty" data-bs-toggle="tooltip" data-bs-placement="top" title="${pCode} Kosong — Klik untuk Pasang Splitter / ODC">
                            <i class="bx bx-plus text-[10px] text-emerald-600"></i> ${pCode}
                        </button>
                    `);
                }
            }

            const ponHub = document.createElement('div');
            ponHub.className = 'olt-pon-hub';
            ponHub.innerHTML = `
                <div class="olt-pon-title justify-between">
                    <span class="flex items-center gap-1"><i class="bx bx-chip"></i> Port PON (${ponCount - emptyCount}/${ponCount} Terisi)</span>
                    ${emptyCount > 0 ? `<span class="text-[9px] font-normal text-emerald-700 font-sans">${emptyCount} kosong (klik untuk kelola)</span>` : ''}
                </div>
                <div class="olt-pon-grid">
                    ${ponPortButtons.join('')}
                </div>
            `;
            const contentArea = box.querySelector('.flex-1');
            if (contentArea) contentArea.appendChild(ponHub);
        }

        wrapper.appendChild(box);

        if (node.type === 'Splitter') {
            const isRootPonSplitter = node.type === 'Splitter' && node.pon_port && !node.odc_id;
            const childrenWrapper = document.createElement('div');
            childrenWrapper.className = 'children-container';
            const ratioNum = node.ratio || parseRatioNum(node.rasio, 4);
            const targetChildren = node.children || [];

            let emptyPorts = [];

            for (let i = 1; i <= ratioNum; i++) {
                const portCode = `OUT-${String(i).padStart(2, "0")}`;
                const childNode = targetChildren.find(c => c.port_out === portCode) || (targetChildren[i - 1] && !targetChildren[i - 1].port_out ? targetChildren[i - 1] : null);

                if (childNode) {
                    const childWrapper = document.createElement('div');
                    childWrapper.className = 'child-branch';
                    const badgeContainer = document.createElement('div');
                    badgeContainer.className = 'mb-1 flex items-center gap-1';
                    badgeContainer.innerHTML = `<span class="port-badge">${portCode}</span>`;
                    childWrapper.appendChild(badgeContainer);
                    childWrapper.appendChild(createNodeElement(childNode));
                    childrenWrapper.appendChild(childWrapper);
                } else {
                    emptyPorts.push(portCode);
                }
            }

            // If there are empty ports, render them as a sleek compact multi-action grid
            if (emptyPorts.length > 0) {
                const emptyBranch = document.createElement('div');
                emptyBranch.className = 'child-branch';

                let emptyPortButtons = emptyPorts.map(pCode => {
                    return `
                    <div class="empty-port-group">
                        <span class="empty-port-code">${pCode}</span>
                        <button type="button" onclick="openDrawer('attach_splitter', 'Splitter', { parent_splitter_id: ${node.db_id}, parent_port_out: '${pCode}', parent_name: '${escapeHtml(node.name)}', ratio: ${node.ratio || 4} })" class="empty-port-subbtn btn-sub-splitter" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang Splitter Anak pada Port ${pCode}">
                            <i class="bx bx-git-repo-forked"></i> +Splitter
                        </button>
                        <button type="button" onclick="openDrawer('add', 'ODP', { parent_id: '${node.odc_id || ''}', splitter_id: ${node.db_id}, port_out: '${pCode}' })" class="empty-port-subbtn btn-sub-odp" data-bs-toggle="tooltip" data-bs-placement="top" title="Hubungkan ODP ke Port ${pCode}">
                            <i class="bx bx-plug"></i> +ODP
                        </button>
                        ${isRootPonSplitter ? `
                        <button type="button" onclick="openDrawer('add', 'ODC', { parent_id: ${node.lokasi_id}, pon_port: '${escapeHtml(node.pon_port)}', splitter_id: ${node.db_id}, port_out: '${pCode}', ratio: ${node.ratio || 4} })" class="empty-port-subbtn btn-sub-odc" data-bs-toggle="tooltip" data-bs-placement="top" title="Pasang ODC pada Port ${pCode}">
                            <i class="bx bx-cabinet"></i> +ODC
                        </button>
                        ` : ''}
                    </div>
                    `;
                }).join('');

                emptyBranch.innerHTML = `
                    <div class="splitter-empty-hub">
                        <div class="splitter-empty-title">
                            <i class="bx bx-git-repo-forked"></i>
                            <span>Port Kosong (${emptyPorts.length}/${ratioNum}) — Klik aksi untuk menghubungkan:</span>
                        </div>
                        <div class="splitter-empty-grid">
                            ${emptyPortButtons}
                        </div>
                    </div>
                `;
                childrenWrapper.appendChild(emptyBranch);
            }

            wrapper.appendChild(childrenWrapper);
        } else if (kids.length) {
            const childrenWrapper = document.createElement('div');
            childrenWrapper.className = 'children-container';

            kids.forEach(childNode => {
                const child = document.createElement('div');
                child.className = 'child-branch';
                child.appendChild(createNodeElement(childNode));
                childrenWrapper.appendChild(child);
            });

            wrapper.appendChild(childrenWrapper);
        }

        return wrapper;
    }

    function initializeTooltips() {
        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Tooltip !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                const existing = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
                if (existing) existing.dispose();
                return new bootstrap.Tooltip(tooltipTriggerEl, { container: 'body', trigger: 'hover' });
            });
        }
    }

    let tomServerFilter = null;

    function initServerTomSelect() {
        const selectEl = document.getElementById('serverFilterSelect');
        if (!selectEl) return;

        if (typeof TomSelect !== 'undefined') {
            try {
                tomServerFilter = new TomSelect(selectEl, {
                    create: false,
                    allowEmptyOption: true,
                    sortField: { field: "text", direction: "asc" },
                    placeholder: "Tampilkan Semua Server...",
                    controlInput: '<input>',
                    onChange: function(val) {
                        renderFilteredTopology(val);
                    }
                });
            } catch (e) {
                console.warn('TomSelect init error:', e);
                selectEl.addEventListener('change', function() {
                    renderFilteredTopology(this.value);
                });
            }
        } else {
            selectEl.addEventListener('change', function() {
                renderFilteredTopology(this.value);
            });
        }
    }

    function renderFilteredTopology(selectedServerId = null) {
        if (selectedServerId === null) {
            selectedServerId = tomServerFilter ? tomServerFilter.getValue() : (document.getElementById('serverFilterSelect')?.value || '');
        }

        const container = document.getElementById('topology');
        container.innerHTML = '';

        if (!treeDataGlobal || !treeDataGlobal.length) {
            container.innerHTML = `
                <div class="text-center py-16 text-slate-400">
                    <i class="bx bx-sitemap text-4xl mb-2 text-slate-300"></i>
                    <p class="text-xs font-bold text-slate-700">Belum Ada Topologi</p>
                    <p class="text-[11px] text-slate-400 mt-1">Tambahkan Server pertama Anda menggunakan form di sebelah kiri.</p>
                </div>
            `;
            updateCounters([]);
            return;
        }

        // Mode Default: Tampilkan Seluruh Server beserta seluruh anak pohonnya
        let filteredData = treeDataGlobal;
        if (selectedServerId && String(selectedServerId).trim() !== '' && selectedServerId !== 'all') {
            filteredData = treeDataGlobal.filter(root => String(root.db_id) === String(selectedServerId) || root.id === 'server-' + selectedServerId);
        }

        if (!filteredData.length) {
            container.innerHTML = `
                <div class="text-center py-16 text-slate-400">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 grid place-items-center mx-auto mb-2.5 text-2xl">
                        <i class="bx bx-server"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-700 mb-0.5">Server Ini Belum Memiliki Node</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Silakan hubungkan OLT ke server ini melalui menu form di sebelah kiri.</p>
                </div>
            `;
            updateCounters([]);
            return;
        }

        filteredData.forEach(root => container.appendChild(createNodeElement(root)));
        updateCounters(filteredData);
        initializeTooltips();
    }

    function renderTopology(data) {
        treeDataGlobal = data;
        renderFilteredTopology();
    }

    function updateModalSelects(preserveSelections = true) {
        // 1. Server Filter Toolbar
        if (typeof tomServerFilter !== 'undefined' && tomServerFilter) {
            const cur = tomServerFilter.getValue();
            tomServerFilter.clearOptions();
            tomServerFilter.addOption({ value: '', text: 'Semua Server (Tampilkan Seluruh Topologi)' });
            (serverListGlobal || []).forEach(s => {
                tomServerFilter.addOption({ value: String(s.id), text: s.lokasi_server });
            });
            tomServerFilter.setValue(cur, true);
        } else {
            const sfEl = document.getElementById('serverFilterSelect');
            if (sfEl) {
                const cur = sfEl.value;
                let h = '<option value="">Semua Server (Tampilkan Seluruh Topologi)</option>';
                (serverListGlobal || []).forEach(s => {
                    h += `<option value="${s.id}" ${cur == s.id ? 'selected' : ''}>${escapeHtml(s.lokasi_server)}</option>`;
                });
                sfEl.innerHTML = h;
            }
        }

        // 2. Parent Server for OLT (#parent-OLT)
        const parentOltEl = document.getElementById('parent-OLT');
        if (parentOltEl) {
            const cur = preserveSelections ? parentOltEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>Pilih Server</option>`;
            (serverListGlobal || []).forEach(s => {
                const ipText = s.ip_address ? ` (${escapeHtml(s.ip_address)})` : '';
                h += `<option value="${s.id}" ${cur == s.id ? 'selected' : ''}>${escapeHtml(s.lokasi_server)}${ipText}</option>`;
            });
            parentOltEl.innerHTML = h;
            syncTomSelect(parentOltEl);
        }

        // 3. OLT for Splitter (#f_sidebar_splitter_olt)
        const splitOltEl = document.getElementById('f_sidebar_splitter_olt');
        if (splitOltEl) {
            const cur = preserveSelections ? splitOltEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>-- Pilih OLT --</option>`;
            (oltListGlobal || []).forEach(o => {
                const pon = o.jumlah_pon || 8;
                h += `<option value="${o.id}" data-pon="${pon}" ${cur == o.id ? 'selected' : ''}>${escapeHtml(o.nama_lokasi)} (${pon} PON)</option>`;
            });
            splitOltEl.innerHTML = h;
            syncTomSelect(splitOltEl);
        }

        // 4. Parent Splitter for Cascade (#parent-Splitter)
        const parentSplitEl = document.getElementById('parent-Splitter');
        if (parentSplitEl) {
            const cur = preserveSelections ? parentSplitEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>-- Pilih Splitter Induk --</option>`;
            (activeSplittersGlobal || []).forEach(as => {
                h += `<option value="${as.id}" data-rasio="${escapeHtml(as.rasio || '1:4')}" ${cur == as.id ? 'selected' : ''}>${escapeHtml(as.nama)}</option>`;
            });
            parentSplitEl.innerHTML = h;
            syncTomSelect(parentSplitEl);
        }

        // 5. Logistics Splitter for Splitter Form (#f_sidebar_splitter_logistik)
        const splitDevEl = document.getElementById('f_sidebar_splitter_logistik');
        if (splitDevEl) {
            const cur = preserveSelections ? splitDevEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>-- Pilih Splitter dari Logistik --</option>`;
            if (perangkatSplitterGlobal && perangkatSplitterGlobal.length > 0) {
                perangkatSplitterGlobal.forEach(ps => {
                    h += `<option value="${ps.id}" data-rasio="${escapeHtml(ps.rasio_default || '1:4')}" data-nama="${escapeHtml(ps.nama_perangkat)}" data-stok="${ps.stok_tersedia}" ${cur == ps.id ? 'selected' : ''}>
                        📦 ${escapeHtml(ps.nama_perangkat)} (Tersedia: ${ps.stok_tersedia} unit)
                    </option>`;
                });
            } else if (splitterListGlobal && splitterListGlobal.length > 0) {
                splitterListGlobal.forEach(sp => {
                    const pNama = (sp.perangkat && sp.perangkat.nama_perangkat) ? sp.perangkat.nama_perangkat : 'Splitter';
                    h += `<option value="${sp.logistik_id}" data-rasio="${escapeHtml(sp.rasio || '1:4')}" data-nama="${escapeHtml(pNama)}" ${cur == sp.logistik_id ? 'selected' : ''}>
                        📦 ${escapeHtml(pNama)} (Rasio: ${escapeHtml(sp.rasio || '-')})
                    </option>`;
                });
            } else {
                h += `<option disabled>Tidak ada splitter tersedia di logistik</option>`;
            }
            splitDevEl.innerHTML = h;
            syncTomSelect(splitDevEl);
        }

        // 6. Parent OLT for ODC (#parent-ODC)
        const parentOdcEl = document.getElementById('parent-ODC');
        if (parentOdcEl) {
            const cur = preserveSelections ? parentOdcEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>Pilih OLT</option>`;
            (oltListGlobal || []).forEach(o => {
                const pon = o.jumlah_pon || 8;
                h += `<option value="${o.id}" data-pon="${pon}" ${cur == o.id ? 'selected' : ''}>${escapeHtml(o.nama_lokasi)} (${pon} PON)</option>`;
            });
            parentOdcEl.innerHTML = h;
            syncTomSelect(parentOdcEl);
        }

        // 7. Logistics Splitter for ODC (#f_odc_splitter)
        const odcSplitDevEl = document.getElementById('f_odc_splitter');
        if (odcSplitDevEl) {
            const cur = preserveSelections ? odcSplitDevEl.value : '';
            let h = `<option value="" data-rasio="" ${!cur ? 'selected' : ''}>-- Pilih Splitter dari Logistik (atau kosongkan = ODC langsung ke PON) --</option>`;
            if (perangkatSplitterGlobal && perangkatSplitterGlobal.length > 0) {
                perangkatSplitterGlobal.forEach(ps => {
                    h += `<option value="${ps.id}" data-rasio="${escapeHtml(ps.rasio_default || '1:4')}" data-nama="${escapeHtml(ps.nama_perangkat)}" data-stok="${ps.stok_tersedia}" ${cur == ps.id ? 'selected' : ''}>
                        📦 ${escapeHtml(ps.nama_perangkat)} (Tersedia: ${ps.stok_tersedia} unit)
                    </option>`;
                });
            } else if (splitterListGlobal && splitterListGlobal.length > 0) {
                splitterListGlobal.forEach(sp => {
                    const pNama = (sp.perangkat && sp.perangkat.nama_perangkat) ? sp.perangkat.nama_perangkat : 'Splitter';
                    h += `<option value="${sp.logistik_id}" data-rasio="${escapeHtml(sp.rasio || '1:4')}" data-nama="${escapeHtml(pNama)}" ${cur == sp.logistik_id ? 'selected' : ''}>
                        📦 ${escapeHtml(pNama)} (Rasio: ${escapeHtml(sp.rasio || '-')})
                    </option>`;
                });
            } else {
                h += `<option disabled>Tidak ada splitter tersedia di logistik</option>`;
            }
            odcSplitDevEl.innerHTML = h;
            syncTomSelect(odcSplitDevEl);
        }

        // 8. ODC Existing (#f_sidebar_odc_existing)
        const odcExistEl = document.getElementById('f_sidebar_odc_existing');
        if (odcExistEl) {
            const cur = preserveSelections ? odcExistEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>-- Pilih ODC yang Sudah Ada --</option>`;
            (odcListGlobal || []).forEach(odc => {
                const oltName = (odc.olt && odc.olt.nama_lokasi) ? odc.olt.nama_lokasi : 'Tanpa OLT';
                const ponPort = odc.pon_port || 'Tanpa PON';
                h += `<option value="${odc.id}" data-name="${escapeHtml(odc.nama_odc)}" data-gps="${escapeHtml(odc.gps || '')}" ${cur == odc.id ? 'selected' : ''}>
                    🏢 ${escapeHtml(odc.nama_odc)} (Saat ini: ${escapeHtml(oltName)} - ${escapeHtml(ponPort)})
                </option>`;
            });
            odcExistEl.innerHTML = h;
            syncTomSelect(odcExistEl);
        }

        // 9. Parent ODC for ODP (#parent-ODP)
        const parentOdpEl = document.getElementById('parent-ODP');
        if (parentOdpEl) {
            const cur = preserveSelections ? parentOdpEl.value : '';
            let h = `<option value="" disabled ${!cur ? 'selected' : ''}>Pilih ODC</option>`;
            (odcListGlobal || []).forEach(odc => {
                const splittersJson = JSON.stringify(odc.splitters_data || []);
                h += `<option value="${odc.id}" data-splitters='${escapeHtml(splittersJson)}' ${cur == odc.id ? 'selected' : ''}>${escapeHtml(odc.nama_odc)}</option>`;
            });
            parentOdpEl.innerHTML = h;
            syncTomSelect(parentOdpEl);
        }

        // 10. Perangkat OLT (#f_olt_perangkat_id)
        const oltDevEl = document.getElementById('f_olt_perangkat_id');
        if (oltDevEl) {
            const cur = preserveSelections ? oltDevEl.value : '';
            let h = '<option value="">-- Pilih OLT dari Logistik (Opsional) --</option>';
            (perangkatOltGlobal || []).forEach(p => {
                h += `<option value="${p.id}" ${cur == p.id ? 'selected' : ''}>📦 ${escapeHtml(p.nama_perangkat)} (Tersedia: ${p.stok_tersedia} unit)</option>`;
            });
            oltDevEl.innerHTML = h;
            syncTomSelect(oltDevEl);
        }

        // 11. Perangkat ODC (#f_odc_perangkat_id)
        const odcDevEl = document.getElementById('f_odc_perangkat_id');
        if (odcDevEl) {
            const cur = preserveSelections ? odcDevEl.value : '';
            let h = '<option value="">-- Pilih Box ODC dari Logistik (Opsional) --</option>';
            (perangkatOdcGlobal || []).forEach(p => {
                h += `<option value="${p.id}" ${cur == p.id ? 'selected' : ''}>📦 ${escapeHtml(p.nama_perangkat)} (Tersedia: ${p.stok_tersedia} unit)</option>`;
            });
            odcDevEl.innerHTML = h;
            syncTomSelect(odcDevEl);
        }

        // 12. Perangkat ODP (#f_odp_perangkat_id)
        const odpDevEl = document.getElementById('f_odp_perangkat_id');
        if (odpDevEl) {
            const cur = preserveSelections ? odpDevEl.value : '';
            let h = '<option value="">-- Pilih Box ODP dari Logistik (Opsional) --</option>';
            (perangkatOdpGlobal || []).forEach(p => {
                h += `<option value="${p.id}" ${cur == p.id ? 'selected' : ''}>📦 ${escapeHtml(p.nama_perangkat)} (Tersedia: ${p.stok_tersedia} unit)</option>`;
            });
            odpDevEl.innerHTML = h;
            syncTomSelect(odpDevEl);
        }
    }

    function refreshFormOptions(preserveSelections = true) {
        return fetch(ROUTES.infrastrukturOptions, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (!data) return;
            serverListGlobal = data.serverList || [];
            oltListGlobal = data.oltList || [];
            odcListGlobal = data.odcList || [];
            splitterListGlobal = data.splitterList || [];
            perangkatSplitterGlobal = data.perangkatSplitter || [];
            perangkatOltGlobal = data.perangkatOlt || [];
            perangkatOdcGlobal = data.perangkatOdc || [];
            perangkatOdpGlobal = data.perangkatOdp || [];
            ponSplittersGlobal = data.ponSplitters || [];
            activeSplittersGlobal = data.activeSplitters || [];

            rebuildIndexes();
            updateModalSelects(preserveSelections);
        })
        .catch(err => {
            console.warn('Gagal memuat form options:', err);
        });
    }

    function loadTree(refreshOptions = true) {
        fetch(ROUTES.infrastrukturJson)
            .then(r => r.json())
            .then(data => renderTopology(data))
            .catch(() => {
                document.getElementById('topology').innerHTML = `
                    <div class="text-center py-16 text-rose-500">
                        <i class="bx bx-error-circle text-3xl mb-1"></i>
                        <p class="text-xs font-semibold">Gagal memuat topologi.</p>
                    </div>
                `;
            });

        if (refreshOptions) {
            refreshFormOptions(true);
        }
    }

    function toggleCollapseNode(btn) {
        const cardWrapper = btn.closest('.node-card-wrapper');
        if (!cardWrapper) return;
        const container = cardWrapper.querySelector(':scope > .children-container');
        if (!container) return;

        const isCollapsed = container.classList.contains('collapsed');
        if (isCollapsed) {
            container.classList.remove('collapsed');
            btn.innerHTML = '<i class="bx bx-chevron-down"></i>';
        } else {
            container.classList.add('collapsed');
            btn.innerHTML = '<i class="bx bx-chevron-right"></i>';
        }
    }

    function toggleExpandAll(expand) {
        const containers = document.querySelectorAll('.children-container');
        const buttons = document.querySelectorAll('.toggle-btn');

        containers.forEach(c => {
            if (expand) c.classList.remove('collapsed');
            else c.classList.add('collapsed');
        });

        buttons.forEach(b => {
            b.innerHTML = expand ? '<i class="bx bx-chevron-down"></i>' : '<i class="bx bx-chevron-right"></i>';
        });
    }

    function filterTopology(query) {
        const q = (query || '').toLowerCase().trim();
        const boxes = document.querySelectorAll('.node-box');
        boxes.forEach(box => {
            const name = box.getAttribute('data-name') || '';
            const ip = box.getAttribute('data-ip') || '';
            if (q && (name.includes(q) || ip.includes(q))) {
                box.classList.add('search-match');
                let p = box.closest('.children-container');
                while (p) {
                    p.classList.remove('collapsed');
                    p = p.parentElement ? p.parentElement.closest('.children-container') : null;
                }
            } else {
                box.classList.remove('search-match');
            }
        });
    }

    function onOdcSelectedForOdp(selectEl) {
        const wrapper = document.getElementById('wrapper-odp-splitter');
        const splitterSelect = document.getElementById('f_odp_splitter');
        const portWrapper = document.getElementById('wrapper-odp-port');
        const portSelect = document.getElementById('f_odp_port');
        if (!wrapper || !splitterSelect) return;

        if (portWrapper) portWrapper.style.display = 'none';
        if (portSelect) {
            portSelect.innerHTML = '<option value="">-- Pilih Port Output --</option>';
            syncTomSelect(portSelect);
        }

        if (!selectEl.value || selectEl.selectedIndex < 0) {
            wrapper.style.display = 'none';
            splitterSelect.innerHTML = '<option value="">-- Pilih Splitter dari ODC --</option>';
            syncTomSelect(splitterSelect);
            return;
        }

        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        let splitters = [];
        try {
            const raw = selectedOpt ? selectedOpt.getAttribute('data-splitters') : '';
            if (raw) splitters = JSON.parse(raw);
        } catch (e) {
            splitters = [];
        }

        if ((!splitters || splitters.length === 0) && selectEl.value && odcSplittersMap[selectEl.value]) {
            splitters = odcSplittersMap[selectEl.value];
        }

        if (splitters && splitters.length > 0) {
            let html = '<option value="">-- Pilih Splitter dari ODC --</option>';
            splitters.forEach(sp => {
                const snText = sp.sn ? ` - SN: ${escapeHtml(sp.sn)}` : '';
                html += `<option value="${sp.id}" data-rasio="${escapeHtml(sp.rasio)}">📦 ${escapeHtml(sp.nama)} (Rasio: ${escapeHtml(sp.rasio)})${snText}</option>`;
            });
            splitterSelect.innerHTML = html;
            syncTomSelect(splitterSelect);
            wrapper.style.display = 'block';
        } else {
            wrapper.style.display = 'none';
            splitterSelect.innerHTML = '<option value="">-- Pilih Splitter dari ODC --</option>';
            syncTomSelect(splitterSelect);
        }
    }

    function onOdpSplitterChange(selectEl) {
        const portWrapper = document.getElementById('wrapper-odp-port');
        const portSelect = document.getElementById('f_odp_port');
        if (!portWrapper || !portSelect) return;

        if (!selectEl.value || selectEl.selectedIndex < 0) {
            portWrapper.style.display = 'none';
            portSelect.innerHTML = '<option value="">-- Pilih Port Output --</option>';
            syncTomSelect(portSelect);
            return;
        }

        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const rasioStr = selectedOpt ? selectedOpt.getAttribute('data-rasio') : '';
        const ratioNum = parseRatioNum(rasioStr, 4);

        let html = '<option value="">-- Pilih Port Output --</option>';
        for (let i = 1; i <= ratioNum; i++) {
            const portName = `OUT-${String(i).padStart(2, '0')}`;
            html += `<option value="${portName}">Port ${portName}</option>`;
        }

        portSelect.innerHTML = html;
        syncTomSelect(portSelect);
        portWrapper.style.display = 'block';
    }

    function onSplitterSelected(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const rasioStr = selectedOption ? selectedOption.getAttribute('data-rasio') : '';
        const rasioWrapper = document.getElementById('wrapper_odc_splitter_rasio');
        const snWrapper = document.getElementById('wrapper_odc_splitter_sn');
        const rasioSelect = document.getElementById('f_odc_splitter_rasio');
        const container = document.getElementById('splitterOutputsPreview');

        if (!selectEl.value) {
            if (rasioWrapper) rasioWrapper.style.display = 'none';
            if (snWrapper) snWrapper.style.display = 'none';
            if (container) {
                container.style.display = 'none';
                container.innerHTML = '';
            }
            return;
        }

        if (rasioWrapper) rasioWrapper.style.display = 'block';
        if (snWrapper) {
            snWrapper.style.display = 'block';
            window.onHardwarePerangkatChange('splitter', selectEl, 'f_odc_splitter_modem_detail_id');
        }
        if (rasioSelect && rasioStr) {
            rasioSelect.value = rasioStr;
            if (rasioSelect.tomselect) rasioSelect.tomselect.setValue(rasioStr, true);
        }

        const effectiveRatio = (rasioSelect && rasioSelect.value) ? rasioSelect.value : (rasioStr || '1:4');
        renderSplitterPreview(effectiveRatio, container);
        refreshOdcPortOut();
    }

    function onSplitterRatioChanged(selectEl) {
        const container = document.getElementById('splitterOutputsPreview');
        renderSplitterPreview(selectEl.value, container);
        refreshOdcPortOut();
    }

    function refreshDrawerPortOut() {
        const ponEl = document.getElementById('drawer_odc_pon');
        const splitterEl = document.getElementById('drawer_odc_splitter');
        const portWrapper = document.getElementById('drawer_wrapper_odc_port_out');
        const portSelect = document.getElementById('drawer_odc_port_out');
        if (!portWrapper || !portSelect) return;

        const hasPon = ponEl && ponEl.value;
        const hasSplitter = splitterEl && splitterEl.value;

        if (hasPon && hasSplitter) {
            const selOpt = splitterEl.options[splitterEl.selectedIndex];
            const rasioStr = selOpt ? selOpt.getAttribute('data-rasio') : '';
            const rasioEl = document.getElementById('drawer_odc_splitter_rasio');
            const effectiveRatio = (rasioEl && rasioEl.value) ? rasioEl.value : (rasioStr || '1:4');
            const ratioNum = parseRatioNum(effectiveRatio, 4);
            const currentVal = portSelect.value;
            portSelect.innerHTML = buildPortOutOptions(currentVal, ratioNum);
            syncTomSelect(portSelect);
            portWrapper.style.display = 'block';
        } else {
            portWrapper.style.display = 'none';
            portSelect.innerHTML = '<option value="">-- Pilih Port Output (OUT) --</option>';
            syncTomSelect(portSelect);
        }
    }

    function onDrawerSplitterSelected(selectEl) {
        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const rasioStr = selectedOption ? selectedOption.getAttribute('data-rasio') : '';
        const rasioWrapper = document.getElementById('drawer_wrapper_odc_splitter_rasio');
        const rasioSelect = document.getElementById('drawer_odc_splitter_rasio');
        const container = document.getElementById('drawer_splitterOutputsPreview');

        const drawerSplitterSnSelect = document.getElementById('drawer_splitter_modem_detail_id');
        if (drawerSplitterSnSelect) {
            window.onHardwarePerangkatChange('splitter', selectEl, 'drawer_splitter_modem_detail_id');
        }

        const odcSnWrapper = document.getElementById('drawer_wrapper_odc_splitter_sn');
        if (odcSnWrapper) {
            if (!selectEl.value) {
                odcSnWrapper.style.display = 'none';
            } else {
                odcSnWrapper.style.display = 'block';
                window.onHardwarePerangkatChange('splitter', selectEl, 'drawer_odc_splitter_modem_detail_id');
            }
        }

        if (!selectEl.value) {
            if (rasioWrapper) rasioWrapper.style.display = 'none';
            if (container) {
                container.style.display = 'none';
                container.innerHTML = '';
            }
            refreshDrawerPortOut();
            return;
        }

        if (rasioWrapper) rasioWrapper.style.display = 'block';
        if (rasioSelect && rasioStr) {
            rasioSelect.value = rasioStr;
            if (rasioSelect.tomselect) rasioSelect.tomselect.setValue(rasioStr, true);
        }

        const effectiveRatio = (rasioSelect && rasioSelect.value) ? rasioSelect.value : (rasioStr || '1:4');
        renderSplitterPreview(effectiveRatio, container);
        refreshDrawerPortOut();
    }

    function onDrawerSplitterRatioChanged(selectEl) {
        const container = document.getElementById('drawer_splitterOutputsPreview');
        renderSplitterPreview(selectEl.value, container);
        refreshDrawerPortOut();
    }

    function renderSplitterPreview(rasioStr, container) {
        if (!container) return;
        if (!rasioStr) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        const ratioNum = parseRatioNum(rasioStr, 4);

        container.style.display = 'block';
        container.innerHTML = `
            <div class="px-3 py-1.5 bg-amber-100/90 text-amber-900 font-bold text-[11px] rounded-t-lg border-x border-t border-amber-300 flex items-center justify-between">
                <span><i class="bx bx-git-repo-forked"></i> Port Output Splitter (Rasio ${escapeHtml(rasioStr)})</span>
                <span class="text-[10px] bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded font-mono">${ratioNum} Port</span>
            </div>
            <div class="divide-y divide-amber-200/60 bg-white border border-amber-300 rounded-b-lg overflow-hidden max-h-[140px] overflow-y-auto">
                ${Array.from({ length: ratioNum }, (_, i) => `
                    <div class="flex items-center gap-2 px-3 py-1 text-[11px]">
                        <span class="font-mono font-bold text-amber-800 w-16">OUT-${String(i + 1).padStart(2, "0")}</span>
                        <span class="text-[10px] text-slate-400">&rarr; Siap untuk ODP / Cabang</span>
                    </div>
                `).join('')}
            </div>
        `;
    }

    function refreshOdcPortOut() {
        const oltEl = document.getElementById('parent-ODC');
        const ponEl = document.getElementById('f_odc_pon');
        const splitterEl = document.getElementById('f_odc_splitter');
        const portWrapper = document.getElementById('wrapper_odc_port_out');
        const portSelect = document.getElementById('f_odc_port_out');
        if (!portWrapper || !portSelect) return;

        const currentVal = portSelect.value;
        if (!ponEl || !ponEl.value) {
            portWrapper.style.display = 'none';
            portSelect.innerHTML = buildPortOutOptions('', 4, '-- Otomatis (port kosong pertama) --');
            syncTomSelect(portSelect);
            return;
        }

        const key = (oltEl && oltEl.value) ? `${oltEl.value}|${ponEl.value}` : '';
        const existing = key ? ponSplitterIndex[key] : null;
        let ratioNum = 0;

        if (existing) {
            ratioNum = parseRatioNum(existing.rasio, 4);
        } else if (splitterEl && splitterEl.value) {
            const selOpt = splitterEl.options[splitterEl.selectedIndex];
            const rasioStr = selOpt ? selOpt.getAttribute('data-rasio') : '';
            const rasioEl = document.getElementById('f_odc_splitter_rasio');
            const effectiveRatio = (rasioEl && rasioEl.value) ? rasioEl.value : (rasioStr || '1:4');
            ratioNum = parseRatioNum(effectiveRatio, 4);
        }

        if (ratioNum > 0) {
            portSelect.innerHTML = buildPortOutOptions(currentVal, ratioNum, '-- Otomatis (port kosong pertama) --');
            syncTomSelect(portSelect);
            portWrapper.style.display = 'block';
        } else {
            portWrapper.style.display = 'none';
            portSelect.innerHTML = buildPortOutOptions('', 4, '-- Otomatis (port kosong pertama) --');
            syncTomSelect(portSelect);
        }
    }

    function refreshOdcSplitterContext() {
        const oltEl = document.getElementById('parent-ODC');
        const ponEl = document.getElementById('f_odc_pon');
        const infoBanner = document.getElementById('wrapper_odc_splitter_info');
        const infoText = document.getElementById('lbl_odc_splitter_info_text');
        const logistikBox = document.getElementById('wrapper-odc-splitter');
        const titleEl = document.getElementById('lbl_odc_splitter_title');
        const splitterEl = document.getElementById('f_odc_splitter');

        const ponVal = ponEl ? ponEl.value : '';
        const key = (oltEl && oltEl.value && ponVal) ? `${oltEl.value}|${ponVal}` : '';
        const existing = key ? ponSplitterIndex[key] : null;

        if (existing) {
            if (infoBanner) infoBanner.style.display = 'flex';
            if (infoText) infoText.textContent = `${existing.nama} (${existing.rasio}) sudah terpasang di ${ponVal}. ODC ini akan ditempatkan di port kosong yang tersedia.`;
            if (logistikBox) logistikBox.style.display = 'none';
        } else {
            if (infoBanner) infoBanner.style.display = 'none';
            if (logistikBox) logistikBox.style.display = '';
            if (titleEl) titleEl.textContent = ponVal ? `Pasang Splitter Induk di ${ponVal} (opsional)` : 'Pasang Splitter Induk di PON (opsional)';
            if (splitterEl && !ponVal) {
                splitterEl.value = '';
                if (splitterEl.tomselect) splitterEl.tomselect.setValue('', true);
            }
        }
        refreshOdcPortOut();
    }

    function onOdcPonSelected(selectEl) {
        refreshOdcSplitterContext();
    }

    function onDrawerOdcPonSelected(selectEl) {
        const titleEl = document.getElementById('drawer_lbl_odc_splitter_title');
        const ponVal = selectEl.value;
        if (titleEl) {
            titleEl.textContent = ponVal ? `Pasang Splitter pada ${ponVal} (Logistik)` : 'Pasang Splitter Logistik';
        }
        refreshDrawerPortOut();
    }

    function onOltSelectedForOdc(selectEl) {
        const ponSelect = document.getElementById('f_odc_pon');
        if (!ponSelect) return;

        const splitterEl = document.getElementById('f_odc_splitter');
        if (splitterEl && splitterEl.value) {
            splitterEl.value = '';
            if (splitterEl.tomselect) splitterEl.tomselect.setValue('', true);
        }
        const rasioWrapper = document.getElementById('wrapper_odc_splitter_rasio');
        if (rasioWrapper) rasioWrapper.style.display = 'none';
        const preview = document.getElementById('splitterOutputsPreview');
        if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }

        if (!selectEl.value || selectEl.selectedIndex < 0) {
            ponSelect.innerHTML = '<option value="">-- Pilih Port PON --</option>';
            syncTomSelect(ponSelect);
            onOdcPonSelected(ponSelect);
            return;
        }
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const ponCount = parseInt(selectedOpt.getAttribute('data-pon') || '8', 10);
        let html = '<option value="">-- Pilih Port PON --</option>';
        for (let i = 1; i <= ponCount; i++) {
            const pCode = `PON-${String(i).padStart(2, '0')}`;
            html += `<option value="${pCode}">${pCode}</option>`;
        }
        ponSelect.innerHTML = html;
        syncTomSelect(ponSelect);
        onOdcPonSelected(ponSelect);
    }

    function onDrawerOltChange(selectEl) {
        const ponSelect = document.getElementById('drawer_odc_pon');
        if (!ponSelect) return;
        if (!selectEl.value || selectEl.selectedIndex < 0) {
            ponSelect.innerHTML = '<option value="">-- Pilih Port PON (Opsional) --</option>';
            syncTomSelect(ponSelect);
            onDrawerOdcPonSelected(ponSelect);
            return;
        }
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const ponCount = parseInt(selectedOpt.getAttribute('data-pon') || '8', 10);
        let html = '<option value="">-- Pilih Port PON (Opsional) --</option>';
        for (let i = 1; i <= ponCount; i++) {
            const pCode = `PON-${String(i).padStart(2, '0')}`;
            html += `<option value="${pCode}">${pCode}</option>`;
        }
        ponSelect.innerHTML = html;
        syncTomSelect(ponSelect);
        onDrawerOdcPonSelected(ponSelect);
    }

    function onDrawerOdcChange(selectEl) {
        const odcId = selectEl.value;
        const splitterWrapper = document.getElementById('drawer_wrapper_odp_splitter');
        const splitterSelect = document.getElementById('drawer_odp_splitter');
        const portWrapper = document.getElementById('drawer_wrapper_odp_port');
        const portSelect = document.getElementById('drawer_odp_port');

        if (!splitterSelect) return;

        if (portWrapper) portWrapper.style.display = 'none';
        if (portSelect) {
            portSelect.innerHTML = '<option value="">-- Pilih Port Output --</option>';
            syncTomSelect(portSelect);
        }

        const splitters = odcSplittersMap[odcId] || [];
        if (splitters.length > 0) {
            let html = '<option value="">-- Di Luar Splitter (Langsung ke ODC) --</option>';
            splitters.forEach(sp => {
                const snText = sp.sn ? ` - SN: ${escapeHtml(sp.sn)}` : '';
                html += `<option value="${sp.id}" data-rasio="${escapeHtml(sp.rasio)}">📦 ${escapeHtml(sp.nama)} (Rasio: ${escapeHtml(sp.rasio)})${snText}</option>`;
            });
            splitterSelect.innerHTML = html;
            syncTomSelect(splitterSelect);
            if (splitterWrapper) splitterWrapper.style.display = 'block';
        } else {
            splitterSelect.innerHTML = '<option value="">-- Di Luar Splitter (Langsung ke ODC) --</option>';
            syncTomSelect(splitterSelect);
            if (splitterWrapper) splitterWrapper.style.display = 'none';
        }
    }

    function onDrawerOdpSplitterChange(selectEl) {
        const portWrapper = document.getElementById('drawer_wrapper_odp_port');
        const portSelect = document.getElementById('drawer_odp_port');
        if (!portWrapper || !portSelect) return;

        if (!selectEl.value || selectEl.selectedIndex < 0) {
            portWrapper.style.display = 'none';
            portSelect.innerHTML = '<option value="">-- Pilih Port Output --</option>';
            syncTomSelect(portSelect);
            return;
        }

        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const rasioStr = selectedOpt ? selectedOpt.getAttribute('data-rasio') : '';
        const ratioNum = parseRatioNum(rasioStr, 4);

        let html = '<option value="">-- Pilih Port Output --</option>';
        for (let i = 1; i <= ratioNum; i++) {
            const portName = `OUT-${String(i).padStart(2, '0')}`;
            html += `<option value="${portName}">Port ${portName}</option>`;
        }

        portSelect.innerHTML = html;
        syncTomSelect(portSelect);
        portWrapper.style.display = 'block';
    }

    function val(id) {
        const el = document.getElementById(id);
        return el ? el.value.trim() : '';
    }

    document.getElementById('infraForm').addEventListener('submit', function (e) {
        e.preventDefault();

        // 1. Validation according to current active type
        if (currentType === 'Server') {
            if (!val('f_server_name')) {
                showAlert('Harap isi Nama Server!', 'error');
                document.getElementById('f_server_name')?.focus();
                return;
            }
            if (!val('f_server_gps')) {
                showAlert('Harap isi GPS / Link Map Server!', 'error');
                document.getElementById('f_server_gps')?.focus();
                return;
            }
        } else if (currentType === 'OLT') {
            const parentServer = document.getElementById('parent-OLT');
            if (!parentServer?.value) {
                showAlert('Harap pilih Parent (Server)!', 'error');
                if (parentServer?.tomselect) parentServer.tomselect.focus();
                else parentServer?.focus();
                return;
            }
            if (!val('f_olt_name')) {
                showAlert('Harap isi Nama OLT!', 'error');
                document.getElementById('f_olt_name')?.focus();
                return;
            }
            if (!val('f_olt_gps')) {
                showAlert('Harap isi GPS / Link Map OLT!', 'error');
                document.getElementById('f_olt_gps')?.focus();
                return;
            }
        } else if (currentType === 'Splitter') {
            const targetType = document.querySelector('input[name="splitter_target_type"]:checked')?.value || 'pon';
            if (targetType === 'pon') {
                const oltEl = document.getElementById('f_sidebar_splitter_olt');
                if (!oltEl?.value) {
                    showAlert('Harap pilih OLT untuk Splitter!', 'error');
                    if (oltEl?.tomselect) oltEl.tomselect.focus();
                    else oltEl?.focus();
                    return;
                }
                const ponEl = document.getElementById('f_sidebar_splitter_pon_port');
                if (!ponEl?.value) {
                    showAlert('Harap pilih Port PON OLT!', 'error');
                    if (ponEl?.tomselect) ponEl.tomselect.focus();
                    else ponEl?.focus();
                    return;
                }
            } else {
                const parentSpEl = document.getElementById('parent-Splitter');
                if (!parentSpEl?.value) {
                    showAlert('Harap pilih Splitter Induk (Parent)!', 'error');
                    if (parentSpEl?.tomselect) parentSpEl.tomselect.focus();
                    else parentSpEl?.focus();
                    return;
                }
            }
            const logistikEl = document.getElementById('f_sidebar_splitter_logistik');
            if (!logistikEl?.value) {
                showAlert('Harap pilih Splitter dari Logistik!', 'error');
                if (logistikEl?.tomselect) logistikEl.tomselect.focus();
                else logistikEl?.focus();
                return;
            }
        } else if (currentType === 'ODC') {
            const oltEl = document.getElementById('parent-ODC');
            if (!oltEl?.value) {
                showAlert('Harap pilih Parent (OLT) untuk ODC!', 'error');
                if (oltEl?.tomselect) oltEl.tomselect.focus();
                else oltEl?.focus();
                return;
            }
            const ponEl = document.getElementById('f_odc_pon');
            if (!ponEl?.value) {
                showAlert('Harap pilih Port PON OLT!', 'error');
                if (ponEl?.tomselect) ponEl.tomselect.focus();
                else ponEl?.focus();
                return;
            }
            const odcSource = document.querySelector('input[name="sidebar_odc_source"]:checked')?.value || 'existing';
            if (odcSource === 'existing') {
                const existEl = document.getElementById('f_sidebar_odc_existing');
                if (!existEl?.value) {
                    showAlert('Harap pilih ODC Terdaftar yang sudah ada!', 'error');
                    if (existEl?.tomselect) existEl.tomselect.focus();
                    else existEl?.focus();
                    return;
                }
            } else {
                if (!val('f_odc_name')) {
                    showAlert('Harap isi Nama ODC Baru!', 'error');
                    document.getElementById('f_odc_name')?.focus();
                    return;
                }
                if (!val('f_odc_gps')) {
                    showAlert('Harap isi GPS / Link Map ODC!', 'error');
                    document.getElementById('f_odc_gps')?.focus();
                    return;
                }
            }
            if (!val('f_odc_panjang_kabel')) {
                showAlert('Harap isi Panjang Kabel (PON → ODC)!', 'error');
                document.getElementById('f_odc_panjang_kabel')?.focus();
                return;
            }
            if (!val('f_odc_redaman')) {
                showAlert('Harap isi Redaman (dBm) ODC!', 'error');
                document.getElementById('f_odc_redaman')?.focus();
                return;
            }
        } else if (currentType === 'ODP') {
            const odcEl = document.getElementById('parent-ODP');
            if (!odcEl?.value) {
                showAlert('Harap pilih Parent (ODC) untuk ODP!', 'error');
                if (odcEl?.tomselect) odcEl.tomselect.focus();
                else odcEl?.focus();
                return;
            }
            if (!val('f_odp_name')) {
                showAlert('Harap isi Nama ODP!', 'error');
                document.getElementById('f_odp_name')?.focus();
                return;
            }
            if (!val('f_odp_gps')) {
                showAlert('Harap isi GPS / Link Map ODP!', 'error');
                document.getElementById('f_odp_gps')?.focus();
                return;
            }
            if (!val('f_odp_panjang_kabel')) {
                showAlert('Harap isi Panjang Kabel (ODC → ODP)!', 'error');
                document.getElementById('f_odp_panjang_kabel')?.focus();
                return;
            }
            if (!val('f_odp_redaman')) {
                showAlert('Harap isi Redaman (dBm) ODP!', 'error');
                document.getElementById('f_odp_redaman')?.focus();
                return;
            }
        }

        const submitBtn = document.getElementById('btn-submit');
        const submitText = document.getElementById('btn-submit-text');
        submitBtn.disabled = true;
        submitText.textContent = 'Menyimpan...';

        const fd = new FormData();
        if (currentType === 'Server') {
            fd.append('lokasi_server', val('f_server_name'));
            fd.append('ip', val('f_server_ip'));
            fd.append('gps', val('f_server_gps'));
        } else if (currentType === 'OLT') {
            fd.append('olt', val('f_olt_name'));
            fd.append('lokasi_server', val('parent-OLT'));
            fd.append('jumlah_pon', val('f_olt_pon') || '8');
            fd.append('gps', val('f_olt_gps'));
            const oltMd = val('f_olt_modem_detail_id');
            if (oltMd) fd.append('modem_detail_id', oltMd);
        } else if (currentType === 'Splitter') {
            const targetType = document.querySelector('input[name="splitter_target_type"]:checked')?.value || 'pon';
            if (targetType === 'pon') {
                const oltVal = val('f_sidebar_splitter_olt');
                const ponVal = val('f_sidebar_splitter_pon_port');
                if (oltVal) fd.append('lokasi_id', oltVal);
                if (ponVal) fd.append('pon_port', ponVal);
            } else {
                const parentSpId = val('parent-Splitter');
                if (parentSpId) fd.append('parent_splitter_id', parentSpId);
                const parentPort = val('f_sidebar_splitter_parent_port');
                if (parentPort) fd.append('parent_port_out', parentPort);
            }
            fd.append('logistik_id', val('f_sidebar_splitter_logistik'));
            fd.append('rasio', val('f_sidebar_splitter_rasio') || '1:4');
            const splitterMd = val('f_sidebar_splitter_modem_detail_id');
            if (splitterMd) fd.append('modem_detail_id', splitterMd);
            const snVal = val('f_sidebar_splitter_sn');
            if (snVal) fd.append('serial_number', snVal);
        } else if (currentType === 'ODC') {
            const oltVal = val('parent-ODC');
            const ponVal = val('f_odc_pon');
            const odcSource = document.querySelector('input[name="sidebar_odc_source"]:checked')?.value || 'existing';
            if (odcSource === 'existing') {
                const existingOdcId = val('f_sidebar_odc_existing');
                if (existingOdcId) fd.append('odc_id', existingOdcId);
            } else {
                fd.append('nama_odc', val('f_odc_name'));
                fd.append('gps', val('f_odc_gps'));
                const odcMd = val('f_odc_modem_detail_id');
                if (odcMd) fd.append('modem_detail_id', odcMd);
            }
            fd.append('olt', oltVal);
            fd.append('pon_port', ponVal);
            const pkOdc = val('f_odc_panjang_kabel');
            if (pkOdc) fd.append('panjang_kabel', pkOdc);
            const rdOdc = val('f_odc_redaman');
            if (rdOdc) fd.append('redaman', rdOdc);

            const portOutEl = document.getElementById('f_odc_port_out');
            const portOutVal = portOutEl ? portOutEl.value : '';
            const existing = (oltVal && ponVal) ? ponSplitterIndex[`${oltVal}|${ponVal}`] : null;

            if (existing) {
                fd.append('splitter_id', existing.id);
                if (portOutVal) fd.append('port_out', portOutVal);
            } else {
                const sel = document.getElementById('f_odc_splitter');
                if (sel && sel.value) {
                    fd.append('logistik_id', sel.value);
                    const rasioEl = document.getElementById('f_odc_splitter_rasio');
                    if (rasioEl && rasioEl.value) {
                        fd.append('rasio', rasioEl.value);
                    }
                    const odcSplitterMd = val('f_odc_splitter_modem_detail_id');
                    if (odcSplitterMd) {
                        fd.append('splitter_modem_detail_id', odcSplitterMd);
                    }
                    if (portOutVal) fd.append('port_out', portOutVal);
                }
            }
        } else if (currentType === 'ODP') {
            fd.append('nama_odp', val('f_odp_name'));
            fd.append('odc', val('parent-ODP'));
            fd.append('gps', val('f_odp_gps'));
            const pkOdp = val('f_odp_panjang_kabel');
            if (pkOdp) fd.append('panjang_kabel', pkOdp);
            const rdOdp = val('f_odp_redaman');
            if (rdOdp) fd.append('redaman', rdOdp);
            const odpMd = val('f_odp_modem_detail_id');
            if (odpMd) fd.append('modem_detail_id', odpMd);
            const odpSplitter = document.getElementById('f_odp_splitter');
            if (odpSplitter && odpSplitter.value) fd.append('splitter_id', odpSplitter.value);
            const odpPort = document.getElementById('f_odp_port');
            if (odpPort && odpPort.value) fd.append('port_out', odpPort.value);
        }

        fetch(TYPE_ACTION[currentType], {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: fd
        })
            .then(r => {
                if (!r.ok) return r.json().then(err => { throw new Error(err.message || 'Gagal menyimpan data'); });
                return r.json();
            })
            .then((res) => {
                document.getElementById('infraForm').reset();
                document.querySelectorAll('#infraForm select').forEach(sel => {
                    if (sel.tomselect) {
                        sel.tomselect.setValue(sel.value, true);
                    }
                });
                const splitterSnSelect = document.getElementById('f_sidebar_splitter_modem_detail_id');
                if (splitterSnSelect) {
                    splitterSnSelect.innerHTML = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
                    syncTomSelect(splitterSnSelect);
                }
                const splitterHiddenSn = document.getElementById('f_sidebar_splitter_sn');
                if (splitterHiddenSn) splitterHiddenSn.value = '';
                const odcSnWrapper = document.getElementById('wrapper_odc_splitter_sn');
                if (odcSnWrapper) odcSnWrapper.style.display = 'none';
                const odcSplitterSnSelect = document.getElementById('f_odc_splitter_modem_detail_id');
                if (odcSplitterSnSelect) {
                    odcSplitterSnSelect.innerHTML = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
                    syncTomSelect(odcSplitterSnSelect);
                }
                const preview = document.getElementById('splitterOutputsPreview');
                if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }
                const sidebarPreview = document.getElementById('sidebar_splitter_preview');
                if (sidebarPreview) { sidebarPreview.style.display = 'none'; sidebarPreview.innerHTML = ''; }
                const wrapperSidebarPort = document.getElementById('wrapper_sidebar_parent_port');
                if (wrapperSidebarPort) wrapperSidebarPort.style.display = 'none';
                const targetPonWrapper = document.getElementById('wrapper_sidebar_splitter_pon_target');
                if (targetPonWrapper) targetPonWrapper.style.display = 'block';
                const targetCascadeWrapper = document.getElementById('wrapper_sidebar_splitter_cascade_target');
                if (targetCascadeWrapper) targetCascadeWrapper.style.display = 'none';
                const defaultRadio = document.querySelector('input[name="splitter_target_type"][value="pon"]');
                if (defaultRadio) defaultRadio.checked = true;
                const odcExistingWrapper = document.getElementById('sidebar_wrapper_odc_existing');
                if (odcExistingWrapper) odcExistingWrapper.style.display = 'block';
                const odcNewWrapper = document.getElementById('sidebar_wrapper_odc_new');
                if (odcNewWrapper) odcNewWrapper.style.display = 'none';
                const odcSourceDefault = document.querySelector('input[name="sidebar_odc_source"][value="existing"]');
                if (odcSourceDefault) odcSourceDefault.checked = true;
                const rasioWrapper = document.getElementById('wrapper_odc_splitter_rasio');
                if (rasioWrapper) rasioWrapper.style.display = 'none';
                const infoBanner = document.getElementById('wrapper_odc_splitter_info');
                if (infoBanner) infoBanner.style.display = 'none';
                const logistikBox = document.getElementById('wrapper-odc-splitter');
                if (logistikBox) logistikBox.style.display = '';
                const portOutWrapper = document.getElementById('wrapper_odc_port_out');
                if (portOutWrapper) portOutWrapper.style.display = 'none';
                const portOutSelect = document.getElementById('f_odc_port_out');
                if (portOutSelect) {
                    portOutSelect.innerHTML = buildPortOutOptions('', 4, '-- Otomatis (port kosong pertama) --');
                    syncTomSelect(portOutSelect);
                }
                const wrapperOdpSplitter = document.getElementById('wrapper-odp-splitter');
                if (wrapperOdpSplitter) wrapperOdpSplitter.style.display = 'none';
                const wrapperOdpPort = document.getElementById('wrapper-odp-port');
                if (wrapperOdpPort) wrapperOdpPort.style.display = 'none';
                showAlert(res.message || `Node ${currentType} berhasil ditambahkan!`, 'success');
                loadTree();
                closeAddNodeDialog();
            })
            .catch(err => showAlert(err.message || 'Gagal menyimpan data. Periksa kembali input Anda.', 'error'))
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = 'Tambahkan ke Topologi';
            });
    });

    function onSidebarParentSplitterChange(selectEl) {
        const parentId = selectEl.value;
        const selectedOpt = selectEl.options[selectEl.selectedIndex];
        const rasioStr = selectedOpt ? selectedOpt.getAttribute('data-rasio') : '1:4';
        const ratioNum = parseRatioNum(rasioStr, 4);
        const portWrapper = document.getElementById('wrapper_sidebar_parent_port');
        const portSelect = document.getElementById('f_sidebar_splitter_parent_port');
        if (portSelect && portWrapper) {
            portSelect.innerHTML = buildPortOutOptions('', ratioNum, '-- Otomatis (Port kosong pertama) --');
            syncTomSelect(portSelect);
            portWrapper.style.display = parentId ? 'block' : 'none';
        }
    }

    function onSidebarSplitterDevChange(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (!opt) return;
        const rasio = opt.getAttribute('data-rasio');
        const rasioSelect = document.getElementById('f_sidebar_splitter_rasio');
        if (rasio && rasioSelect) {
            rasioSelect.value = rasio;
            if (rasioSelect.tomselect) rasioSelect.tomselect.setValue(rasio, true);
        }
        renderSplitterPreview(rasioSelect ? rasioSelect.value : '1:4', document.getElementById('sidebar_splitter_preview'));

        // Populate Splitter SN select from Logistik
        window.onHardwarePerangkatChange('splitter', selectEl, 'f_sidebar_splitter_modem_detail_id');
        const hiddenSn = document.getElementById('f_sidebar_splitter_sn');
        if (hiddenSn) hiddenSn.value = '';
    }

    function onSidebarSplitterRatioChange(selectEl) {
        renderSplitterPreview(selectEl.value, document.getElementById('sidebar_splitter_preview'));
    }

    /* ================================================================================= */
    /* OPTIMASI A: AUTO-SYNC PON DARI NAMA ODC                                           */
    /* ================================================================================= */
    function syncOdcPonPorts() {
        if (!confirm('Apakah Anda ingin menyinkronkan nomor Port PON pada seluruh ODC secara otomatis berdasarkan nama perangkat (contoh: "ODC PON 1" -> PON-01)?')) return;

        const btn = document.querySelector('button[onclick="syncOdcPonPorts()"]');
        const origText = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin text-xs"></i> Menyinkronkan...';
        }

        fetch(ROUTES.syncOdcPon, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(res => {
            showAlert(res.message || 'Sinkronisasi Port PON berhasil!', 'success');
            loadTree();
        })
        .catch(() => {
            showAlert('Gagal menyinkronkan Port PON ODC.', 'error');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        });
    }

    /* ================================================================================= */
    /* OPTIMASI B: LIVE SEARCH & JUMP TO NODE                                            */
    /* ================================================================================= */
    let searchDebounceTimer = null;

    function handleGlobalSearch(query) {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            performSearch(query);
        }, 120);
    }

    function performSearch(query) {
        const q = (query || '').trim().toLowerCase();
        const dropdown = document.getElementById('searchResultsDropdown');
        const clearBtn = document.getElementById('btnSearchClear');
        if (clearBtn) clearBtn.style.display = q ? 'block' : 'none';

        if (!q || q.length < 2) {
            if (dropdown) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; }
            filterTopology(q);
            return;
        }

        filterTopology(q);

        const results = [];
        function searchTraverse(nodes, path = [], serverId = null) {
            (nodes || []).forEach(node => {
                const curServerId = node.type === 'Server' ? node.db_id : serverId;
                const currentPath = [...path, { type: node.type, name: node.name, db_id: node.db_id }];
                const nameStr = (node.name || '').toLowerCase();
                const ipStr = (node.ip || '').toLowerCase();
                const serialStr = (node.serial || '').toLowerCase();
                const ponStr = (node.pon_port || '').toLowerCase();
                const portStr = (node.port_out || '').toLowerCase();

                if (nameStr.includes(q) || ipStr.includes(q) || serialStr.includes(q) || ponStr.includes(q) || portStr.includes(q)) {
                    results.push({
                        node: node,
                        path: currentPath,
                        serverId: curServerId
                    });
                }

                if (node.splitters) searchTraverse(node.splitters, currentPath, curServerId);
                if (node.children) searchTraverse(node.children, currentPath, curServerId);
            });
        }

        (treeDataGlobal || []).forEach(srv => {
            searchTraverse([srv], [], srv.db_id);
        });

        if (!dropdown) return;

        if (results.length === 0) {
            dropdown.innerHTML = `
                <div class="p-3 text-center text-slate-400 text-xs">
                    <i class="bx bx-search-alt text-lg text-slate-300 mb-1"></i>
                    <p class="mb-0">Tidak ditemukan node dengan kata kunci "<strong>${escapeHtml(query)}</strong>"</p>
                </div>
            `;
            dropdown.style.display = 'block';
            return;
        }

        const topResults = results.slice(0, 15);
        let html = `
            <div class="px-2.5 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider flex items-center justify-between border-b border-slate-100 mb-1">
                <span>Ditemukan ${results.length} Node</span>
                <span class="text-[9.5px] font-normal text-slate-400 font-sans">Klik item untuk melompat</span>
            </div>
        `;

        topResults.forEach(res => {
            const n = res.node;
            const pathBreadcrumb = res.path.slice(0, -1).map(p => escapeHtml(p.name)).join(' <i class="bx bx-chevron-right text-[9px] text-slate-300"></i> ');

            html += `
                <div class="search-result-item" onclick="jumpToNode('${n.id}', ${res.serverId || 'null'})">
                    <div class="search-item-header">
                        <span class="search-item-title flex items-center gap-1.5">
                            <i class="${getNodeIcon(n.type)} text-slate-500"></i>
                            ${escapeHtml(n.name)}
                        </span>
                        <span class="node-type-pill text-[9px] font-semibold" style="background:${getNodeBadgeBg(n.type)};color:${getNodeBadgeColor(n.type)}">${n.type}</span>
                    </div>
                    ${pathBreadcrumb ? `<div class="search-item-path">${pathBreadcrumb}</div>` : ''}
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';
    }

    function clearGlobalSearch() {
        const input = document.getElementById('globalSearchInput');
        if (input) input.value = '';
        const dropdown = document.getElementById('searchResultsDropdown');
        if (dropdown) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; }
        const clearBtn = document.getElementById('btnSearchClear');
        if (clearBtn) clearBtn.style.display = 'none';
        filterTopology('');
    }

    function jumpToNode(nodeId, serverDbId) {
        const dropdown = document.getElementById('searchResultsDropdown');
        if (dropdown) dropdown.style.display = 'none';

        // 1. If a specific single server is filtered and doesn't match this node's server, switch filter or reset to all
        if (serverDbId && tomServerFilter && tomServerFilter.getValue() && tomServerFilter.getValue() != serverDbId) {
            tomServerFilter.setValue(serverDbId);
        }

        setTimeout(() => {
            let targetBox = document.getElementById(nodeId);
            if (!targetBox) {
                const wrapper = document.querySelector(`[data-node-id="${nodeId}"]`);
                if (wrapper) targetBox = wrapper.querySelector('.node-box');
            }

            if (targetBox) {
                // 2. Expand all collapsed parent branches up to root
                let p = targetBox.closest('.children-container');
                while (p) {
                    p.classList.remove('collapsed');
                    const toggleBtn = p.previousElementSibling?.querySelector('.toggle-btn i') || p.parentElement?.querySelector('.toggle-btn i');
                    if (toggleBtn) toggleBtn.className = 'bx bx-chevron-down';
                    p = p.parentElement ? p.parentElement.closest('.children-container') : null;
                }

                // 3. Smooth scroll directly to the node (inside topology-wrapper and window)
                const canvas = document.querySelector('.topology-wrapper');
                if (canvas) {
                    const canvasRect = canvas.getBoundingClientRect();
                    const boxRect = targetBox.getBoundingClientRect();
                    const targetScrollTop = canvas.scrollTop + (boxRect.top - canvasRect.top) - (canvasRect.height / 2) + (boxRect.height / 2);
                    const targetScrollLeft = canvas.scrollLeft + (boxRect.left - canvasRect.left) - (canvasRect.width / 2) + (boxRect.width / 2);

                    canvas.scrollTo({
                        top: Math.max(0, targetScrollTop),
                        left: Math.max(0, targetScrollLeft),
                        behavior: 'smooth'
                    });
                }
                targetBox.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });

                // 4. Trigger pulse highlight animation
                targetBox.classList.remove('node-highlight-pulse');
                void targetBox.offsetWidth; // force DOM reflow
                targetBox.classList.add('node-highlight-pulse');
                setTimeout(() => { targetBox.classList.remove('node-highlight-pulse'); }, 4000);
            }
        }, 120);
    }

    // Global click listener to dismiss search dropdown
    document.addEventListener('click', function(e) {
        const searchContainer = document.getElementById('globalSearchContainer');
        const dropdown = document.getElementById('searchResultsDropdown');
        if (searchContainer && dropdown && !searchContainer.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Keyboard shortcut (Ctrl + K / Cmd + K) for Quick Search
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const input = document.getElementById('globalSearchInput');
            if (input) {
                input.focus();
                input.select();
            }
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const defaultBtn = document.querySelector('.node-tab-btn[data-type="Server"]');
        if (defaultBtn) selectType('Server', defaultBtn);
        initServerTomSelect();
        initAddNodeTomSelects();
        initializeTooltips();
        loadTree();
    });
