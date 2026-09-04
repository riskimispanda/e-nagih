var SERIALIZED_CATEGORIES = ['modem', 'tenda', 'sfp', 'olt', 'odp', 'odc', 'htb', 'splitter'];
var NO_MAC_CATEGORIES = ['olt', 'odp', 'odc', 'splitter'];

// Pagination state per tab
var paginationState = {
    global: { page: 1, pageSize: 10 },
    available: { page: 1, pageSize: 10 },
    maintenance: { page: 1, pageSize: 10 },
    damaged: { page: 1, pageSize: 10 }
};

function renderPagination(tabName) {
    var pane = document.getElementById(tabName + '-pane');
    if (!pane) return;

    var rows = Array.from(pane.querySelectorAll('tbody tr.searchable-row, tbody tr.device-row'));
    var searchInput = document.getElementById('search');
    var term = searchInput ? searchInput.value.toLowerCase().trim() : '';

    var filteredRows = rows.filter(function (row) {
        if (!term) return true;
        return row.textContent.toLowerCase().includes(term);
    });

    var state = paginationState[tabName] || { page: 1, pageSize: 10 };
    var pageSize = state.pageSize;
    var totalItems = filteredRows.length;
    var totalPages = Math.max(1, Math.ceil(totalItems / pageSize));

    if (state.page > totalPages) state.page = totalPages;
    if (state.page < 1) state.page = 1;

    var startIndex = (state.page - 1) * pageSize;
    var endIndex = startIndex + pageSize;

    // Hide all rows first
    rows.forEach(function (r) { r.style.display = 'none'; });

    // Show only the filtered rows for the current page and update numbering
    filteredRows.slice(startIndex, endIndex).forEach(function (r, index) {
        r.style.display = '';
        var numCell = r.querySelector('.row-number');
        if (numCell) {
            numCell.textContent = (startIndex + index + 1);
        }
    });

    // Handle Empty State Row
    var emptyRow = pane.querySelector('tbody tr:not(.searchable-row):not(.device-row)');
    if (emptyRow) {
        emptyRow.style.display = totalItems === 0 ? '' : 'none';
    }

    // Update info text
    var infoEl = document.getElementById('info-' + tabName);
    if (infoEl) {
        if (totalItems === 0) {
            infoEl.textContent = 'Tidak ada data yang cocok';
        } else {
            var displayEnd = Math.min(endIndex, totalItems);
            infoEl.textContent = 'Menampilkan ' + (startIndex + 1) + ' - ' + displayEnd + ' dari ' + totalItems + ' data';
        }
    }

    // Update pagination controls
    var navEl = document.getElementById('nav-' + tabName);
    if (navEl) {
        navEl.innerHTML = '';
        if (totalPages <= 1) {
            return;
        }

        // Prev Button
        var prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 transition cursor-pointer text-sm disabled:opacity-40 disabled:cursor-not-allowed shadow-xs';
        prevBtn.innerHTML = '<i class="bx bx-chevron-left"></i>';
        prevBtn.disabled = state.page === 1;
        prevBtn.onclick = function () {
            if (state.page > 1) {
                state.page--;
                renderPagination(tabName);
            }
        };
        navEl.appendChild(prevBtn);

        // Page Numbers
        var maxButtons = 5;
        var startPage = Math.max(1, state.page - 2);
        var endPage = Math.min(totalPages, startPage + maxButtons - 1);
        if (endPage - startPage < maxButtons - 1) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }

        if (startPage > 1) {
            navEl.appendChild(createPageBtn(1, tabName));
            if (startPage > 2) {
                var dots = document.createElement('span');
                dots.className = 'px-1 text-slate-400 text-xs font-mono';
                dots.textContent = '...';
                navEl.appendChild(dots);
            }
        }

        for (var p = startPage; p <= endPage; p++) {
            navEl.appendChild(createPageBtn(p, tabName));
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                var dots2 = document.createElement('span');
                dots2.className = 'px-1 text-slate-400 text-xs font-mono';
                dots2.textContent = '...';
                navEl.appendChild(dots2);
            }
            navEl.appendChild(createPageBtn(totalPages, tabName));
        }

        // Next Button
        var nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-600 hover:bg-slate-50 transition cursor-pointer text-sm disabled:opacity-40 disabled:cursor-not-allowed shadow-xs';
        nextBtn.innerHTML = '<i class="bx bx-chevron-right"></i>';
        nextBtn.disabled = state.page === totalPages;
        nextBtn.onclick = function () {
            if (state.page < totalPages) {
                state.page++;
                renderPagination(tabName);
            }
        };
        navEl.appendChild(nextBtn);
    }
}

function createPageBtn(pageNum, tabName) {
    var btn = document.createElement('button');
    btn.type = 'button';
    var isCurrent = paginationState[tabName].page === pageNum;
    btn.className = isCurrent
        ? 'w-7 h-7 rounded-lg bg-blue-600 text-white font-bold flex items-center justify-center text-xs shadow-xs cursor-pointer'
        : 'w-7 h-7 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold flex items-center justify-center text-xs transition cursor-pointer shadow-xs';
    btn.textContent = pageNum;
    btn.onclick = function () {
        paginationState[tabName].page = pageNum;
        renderPagination(tabName);
    };
    return btn;
}

function renderAllPagination() {
    ['global', 'available', 'maintenance', 'damaged'].forEach(function (tab) {
        renderPagination(tab);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Initial pagination render
    renderAllPagination();

    // Instant Search handler
    var searchInput = document.getElementById('search');
    function performSearch() {
        ['global', 'available', 'maintenance', 'damaged'].forEach(function (tab) {
            if (paginationState[tab]) paginationState[tab].page = 1;
        });
        renderAllPagination();
    }
    if (searchInput) {
        searchInput.addEventListener('keyup', performSearch);
        searchInput.addEventListener('input', performSearch);
    }

    // Page size dropdown handler
    document.querySelectorAll('.page-size-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var tab = this.getAttribute('data-target');
            if (paginationState[tab]) {
                paginationState[tab].pageSize = parseInt(this.value, 10);
                paginationState[tab].page = 1;
                renderPagination(tab);
            }
        });
    });

    window.formatRupiah = function (input) {
        var value = input.value.replace(/[^\d]/g, '');
        if (value !== '') {
            value = parseInt(value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = value;
        }
    };

    // Show/hide serial fields based on category
    var kategoriSelect = document.getElementById('kategori_id');
    var serialFields = document.getElementById('serialFields');
    var jumlahStok = document.getElementById('jumlah_stok');

    if (kategoriSelect) {
        function currentCategory() {
            var selected = kategoriSelect.options[kategoriSelect.selectedIndex];
            return (selected && selected.dataset.nama || '').toLowerCase();
        }

        function noMacCategory() {
            return NO_MAC_CATEGORIES.includes(currentCategory());
        }

        function toggleSerialFields() {
            var namaKategori = currentCategory();
            var ratioField = document.getElementById('ratioField');
            var jumlahRusakField = document.getElementById('jumlahRusakField');
            if (namaKategori === 'splitter') {
                if (ratioField) ratioField.style.display = 'block';
                serialFields.style.display = 'block';
                if (jumlahRusakField) jumlahRusakField.style.display = 'none';
                jumlahStok.value = document.querySelectorAll('.device-unit').length || 1;
                jumlahStok.readOnly = true;
                applyMacVisibility();
                syncRusakUI();
                return;
            }
            if (ratioField) ratioField.style.display = 'none';
            if (SERIALIZED_CATEGORIES.includes(namaKategori)) {
                serialFields.style.display = 'block';
                if (jumlahRusakField) jumlahRusakField.style.display = 'none';
                jumlahStok.value = document.querySelectorAll('.device-unit').length || 1;
                jumlahStok.readOnly = true;
                applyMacVisibility();
                syncRusakUI();
            } else {
                serialFields.style.display = 'none';
                if (jumlahRusakField) jumlahRusakField.style.display = 'block';
                jumlahStok.readOnly = false;
                syncRusakUI();
            }
        }

        function applyMacVisibility() {
            var hideMac = noMacCategory();
            var label = document.getElementById('serialFieldLabel');
            var hint = document.getElementById('serialFieldHint');
            var deviceUnits = document.getElementById('deviceUnits');
            if (deviceUnits) deviceUnits.style.display = '';
            document.querySelectorAll('.device-unit').forEach(function (unit) {
                var macCol = unit.querySelector('.mac-col');
                var snCol = unit.querySelector('.sn-col');
                if (hideMac) {
                    if (macCol) macCol.style.display = 'none';
                    if (snCol) {
                        snCol.classList.remove('col-5');
                        snCol.classList.add('col-10');
                    }
                } else {
                    if (macCol) macCol.style.display = '';
                    if (snCol) {
                        snCol.classList.remove('col-10');
                        snCol.classList.add('col-5');
                    }
                }
            });
            if (label) {
                var labelText = hideMac ? 'Detail Perangkat (Serial Number)' : 'Detail Perangkat (Serial & MAC)';
                label.innerHTML = '<i class=\'bx bx-barcode text-blue-600 text-sm\'></i> ' + labelText;
            }
            if (hint) {
                hint.textContent = hideMac
                    ? 'Input Serial Number untuk setiap unit perangkat'
                    : 'Input Serial Number dan MAC Address untuk setiap unit perangkat';
            }
        }
        kategoriSelect.addEventListener('change', toggleSerialFields);
    }

    // Add more device unit rows
    var deviceUnitsContainer = document.getElementById('deviceUnits');
    if (deviceUnitsContainer) {
        deviceUnitsContainer.addEventListener('click', function (e) {
            if (e.target.closest('.add-unit')) {
                var firstUnit = document.querySelector('.device-unit');
                var newUnit = firstUnit.cloneNode(true);
                newUnit.querySelectorAll('input').forEach(function (inp) { inp.value = ''; });
                newUnit.querySelectorAll('select').forEach(function (sel) { sel.selectedIndex = 0; });
                var addBtn = newUnit.querySelector('.add-unit');
                addBtn.className = 'w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 flex items-center justify-center transition-all remove-unit cursor-pointer';
                addBtn.innerHTML = '<i class="bx bx-x text-base"></i>';
                addBtn.title = 'Hapus unit';
                var rBtn = newUnit.querySelector('.unit-rstatus');
                if (rBtn) setUnitStatus(rBtn, 'ok');
                var clonedRusak = newUnit.querySelector('input[name="is_rusak[]"]');
                if (clonedRusak) clonedRusak.value = '0';
                firstUnit.parentNode.appendChild(newUnit);
                applyMacVisibility();
                syncJumlahStok();
                syncRusakUI();
            }
            if (e.target.closest('.remove-unit')) {
                var units = document.querySelectorAll('.device-unit');
                if (units.length > 1) {
                    e.target.closest('.device-unit').remove();
                    syncJumlahStok();
                    syncRusakUI();
                }
            }
            var statusBtn = e.target.closest('.unit-rstatus');
            if (statusBtn) {
                setUnitStatus(statusBtn, statusBtn.dataset.status === 'ok' ? 'rusak' : 'ok');
                syncRusakUI();
            }
        });
    }

    function setUnitStatus(btn, status) {
        btn.dataset.status = status;
        var rusak = status === 'rusak';
        var unit = btn.closest('.device-unit');
        var hiddenRusak = unit ? unit.querySelector('input[name="is_rusak[]"]') : null;
        if (hiddenRusak) hiddenRusak.value = rusak ? '1' : '0';
        btn.className = rusak
            ? 'unit-rstatus w-[62px] h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-rose-300 bg-rose-100 text-rose-700'
            : 'unit-rstatus w-[62px] h-8 rounded-lg border text-[10px] font-bold transition-all cursor-pointer flex items-center justify-center gap-1 border-slate-200 bg-slate-50 text-slate-600 hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700';
        btn.innerHTML = rusak
            ? '<i class="bx bx-x-circle text-sm"></i> RUSAK'
            : '<i class="bx bx-check-circle text-sm"></i> OK';
        btn.title = rusak ? 'Unit rusak (afkir). Klik untuk kembalikan ke OK' : 'Klik untuk menandai unit rusak';
    }

    function syncRusakUI() {
        var hiddenInput = document.getElementById('jumlah_rusak_input');
        var count = setUnitRusakCount();
        if (hiddenInput) hiddenInput.value = count;
        var countEl = document.getElementById('rusakCount');
        if (countEl) countEl.textContent = count;
        var plainField = document.getElementById('jumlah_rusak');
        var namaKategori = currentCategory();
        var isSerialized = namaKategori && (SERIALIZED_CATEGORIES.includes(namaKategori));
        if (!isSerialized) {
            var stok = parseInt(jumlahStok.value) || 0;
            if (plainField) plainField.max = Math.max(stok, 0);
        }
    }

    function setUnitRusakCount() {
        var count = 0;
        document.querySelectorAll('.unit-rstatus').forEach(function (btn) {
            if (btn.dataset.status === 'rusak') count++;
        });
        return count;
    }

    function syncJumlahStok() {
        if (kategoriSelect) {
            var selected = kategoriSelect.options[kategoriSelect.selectedIndex];
            var namaKategori = (selected && selected.dataset.nama || '').toLowerCase();
            if (SERIALIZED_CATEGORIES.includes(namaKategori)) {
                jumlahStok.value = document.querySelectorAll('.device-unit').length;
            }
        }
    }

    // Form validation
    var addDeviceForm = document.getElementById('addDeviceForm');
    if (addDeviceForm) {
        addDeviceForm.addEventListener('submit', function (e) {
            var namaPerangkat = document.getElementById('nama_perangkat').value.trim();
            var harga = document.getElementById('harga-satuan').value.trim();
            if (!namaPerangkat || !harga) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang diperlukan');
                return false;
            }
            // Validate rasio for splitter
            var selectedCat = kategoriSelect.options[kategoriSelect.selectedIndex];
            var namaKategori = (selectedCat && selectedCat.dataset.nama || '').toLowerCase();
            if (namaKategori === 'splitter') {
                var rasioEl = document.getElementById('rasio');
                if (!rasioEl || !rasioEl.value) {
                    e.preventDefault();
                    alert('Pilih rasio splitter (1:2, 1:4, 1:8, atau 1:16)');
                    return false;
                }
            }
            // Validate serial fields if visible
            var jumlahRusakInput = document.getElementById('jumlah_rusak_input');
            var totalStok = parseInt(jumlahStok.value) || 0;
            if (serialFields && serialFields.style.display !== 'none') {
                var allFilled = true;
                var reduceMac = noMacCategory();
                document.querySelectorAll('.device-unit').forEach(function (unit) {
                    var sn = unit.querySelector('input[name="serial_number[]"]').value.trim();
                    var mac = unit.querySelector('input[name="mac_address[]"]').value.trim();
                    if (!sn || (!reduceMac && !mac)) allFilled = false;
                });
                if (!allFilled) {
                    e.preventDefault();
                    alert(reduceMac ? 'Semua unit harus memiliki Serial Number' : 'Semua unit harus memiliki Serial Number dan MAC Address');
                    return false;
                }
                var rusakCount = setUnitRusakCount();
                if (jumlahRusakInput) jumlahRusakInput.value = rusakCount;
                if (rusakCount > totalStok) {
                    e.preventDefault();
                    alert('Jumlah unit rusak tidak boleh melebihi jumlah stok total (' + totalStok + ' unit).');
                    return false;
                }
            } else {
                // Non-serial: ambil dari field Jumlah Rusak
                if (jumlahRusakInput) {
                    var plainRusak = parseInt(document.getElementById('jumlah_rusak').value) || 0;
                    if (plainRusak < 0) plainRusak = 0;
                    jumlahRusakInput.value = Math.min(plainRusak, Math.max(totalStok, 0));
                }
            }
        });
    }

    // Sinkronkan field "Jumlah Rusak" (non-serial) ke hidden input
    var plainRusakField = document.getElementById('jumlah_rusak');
    if (plainRusakField) {
        plainRusakField.addEventListener('input', function () {
            var plainRusak = parseInt(this.value) || 0;
            if (plainRusak < 0) this.value = 0;
            var total = parseInt(jumlahStok.value) || 0;
            if (plainRusak > total) {
                this.value = total;
                plainRusak = total;
            }
            var hi = document.getElementById('jumlah_rusak_input');
            if (hi) hi.value = plainRusak;
        });
    }
});

function hapusLogistik(id) {
    Swal.fire({
        title: 'Yakin hapus data?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (result.isConfirmed) window.location.href = '/hapus-logistik/' + id;
    });
}

function selesaiPerbaikan(id) {
    Swal.fire({
        title: 'Selesai Perbaikan?',
        text: 'Perangkat akan dimasukkan kembali ke stok tersedia di gudang.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Selesai!',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (result.isConfirmed) window.location.href = '/logistik/perbaiki/' + id;
    });
}

function pindahkanKeMaintenance(id) {
    Swal.fire({
        title: 'Kirim ke Maintenance?',
        text: 'Perangkat ini akan dipindahkan ke status perbaikan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Kirim!',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (result.isConfirmed) window.location.href = '/logistik/maintenance/' + id;
    });
}

function afkirBarang(id) {
    Swal.fire({
        title: 'Afkir Barang?',
        text: 'Perangkat ini akan dideklarasikan rusak total (afkir).',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Afkir!',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (result.isConfirmed) window.location.href = '/logistik/afkir/' + id;
    });
}

function hapusBarangRusak(id) {
    Swal.fire({
        title: 'Hapus Permanen?',
        text: 'Barang rusak ini akan dihapus permanen dari pencatatan sistem.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(function (result) {
        if (result.isConfirmed) window.location.href = '/logistik/buang/' + id;
    });
}
