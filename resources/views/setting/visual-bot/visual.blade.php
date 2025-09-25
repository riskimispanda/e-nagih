@extends('layouts.contentNavbarLayout')

@section('title', 'Setting Bot WhatsApp')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="card-header">
                <h4 class="fw-bold">Bot WhatsApp Aktif</h4>
                <span class="text-muted">Kelola dan pantau semua bot WhatsApp yang aktif.</span>
                <div id="botTerpilihInfo" class="text-success fw-bold mt-2"></div>
            </div>
            <button id="tambahBotBtn" class="btn btn-success btn-sm">
                <i class="bx bx-plus me-1"></i> Tambah Bot
            </button>
        </div>
    </div>

    <!-- Daftar Bot -->
    <div class="card mb-4">
        <h5 class="card-header card-title">
            <i class="bx bx-bot text-warning fw-bold me-2"></i> Daftar Bot Tersedia
        </h5>
        <div class="table-responsive text-nowrap mb-4">
            <table class="table table-bordered table-hover">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Session</th>
                        <th>Pesan Terkirim</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="botTableBody" class="text-center">
                    <tr><td colspan="5">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- QR Bot -->
    <div id="botList" class="row gy-4 mb-4"></div>

    <!-- Log Pengiriman -->
    <div class="card">
        <h5 class="card-header">
            <i class="bx bx-pulse text-warning me-2 fw-bold"></i> Log Pengiriman Pesan
        </h5>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Bot</th>
                        <th>Tujuan</th>
                        <th>Pesan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="logTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const socket = io("https://enagih-chat.niscala.net:3000", { transports: ["websocket"] });
    const botTable = document.getElementById("botTableBody");
    const botList = document.getElementById("botList");
    const logTable = document.getElementById("logTableBody");
    const botInfo = document.getElementById("botTerpilihInfo");

    let botTerpilih = null;

    // ambil bot terpilih dari server
    fetch("https://enagih-chat.niscala.net/get-selected-bot")
        .then(res => res.json())
        .then(data => {
            botTerpilih = data.selectedBot;
            tampilkanBotTerpilih();
            updateTombolAksi();
        })
        .catch(() => {
            botTerpilih = null;
            tampilkanBotTerpilih();
        });

    function tampilkanBotTerpilih() {
        if (!botTerpilih || botTerpilih === "null") {
            botInfo.innerHTML = `<span class="text-danger">❌ Belum ada bot yang terhubung</span>`;
        } else {
            botInfo.innerHTML = `🔘 Bot terpilih saat ini: <strong>${botTerpilih}</strong>`;
        }
    }

    socket.on("connect", () => console.log("✅ Socket terhubung"));

    // Daftar bot
    socket.on("bot-list", (bots) => {
        botTable.innerHTML = "";
        if (bots.length === 0) {
            botTable.innerHTML = `<tr><td colspan="5" class="text-center">Belum ada bot tersedia</td></tr>`;
            return;
        }

        bots.forEach(({ session, count }, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${session}</td>
                <td>${count}</td>
                <td><span class="badge bg-success">Aktif</span></td>
                <td>
                    <div class="d-flex justify-content-center gap-2" role="group">
                        <button class="btn btn-sm ${botTerpilih === session ? 'btn-success' : 'btn-outline-primary'} pilih-bot" data-bot="${session}">
                            ${botTerpilih === session ? "✅ Terpilih" : "Gunakan"}
                        </button>
                        <button class="btn btn-sm btn-warning disconnect-bot" data-bot="${session}">🔌 Disconnect</button>
                        <button class="btn btn-sm btn-info reconnect-bot" data-bot="${session}">♻ Reconnect</button>
                    </div>
                </td>
            `;
            botTable.appendChild(row);
        });

        setActionListeners();
        tampilkanBotTerpilih();
    });

    // QRCode handler
    socket.on("qr", ({ session, qr }) => {
        if (!qr.startsWith("data:image/")) return;

        const existing = document.getElementById("bot-" + session);
        const html = `
            <div class="col-md-4" id="bot-${session}">
                <div class="card h-100 shadow-sm border-success">
                    <div class="card-body text-center">
                        <h6 class="text-success fw-bold">${session}</h6>
                        <img src="${qr}" alt="QR ${session}" class="img-fluid rounded shadow mb-3" />
                        <p class="text-muted" id="status-${session}">📲 Scan QR dengan WhatsApp</p>
                        <div class="d-flex justify-content-center gap-2">
                            <button class="btn btn-sm btn-warning disconnect-bot" data-bot="${session}">🔌 Disconnect</button>
                            <button class="btn btn-sm btn-info reconnect-bot" data-bot="${session}">♻ Reconnect</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        if (!existing) {
            botList.insertAdjacentHTML("beforeend", html);
            setActionListeners();
        } else {
            document.querySelector(`#bot-${session} img`).src = qr;
        }
    });

    socket.on("status", ({ session, status }) => {
        const el = document.getElementById("status-" + session);
        if (el) el.innerText = status;
    });

    socket.on("connected", ({ session }) => {
        const el = document.getElementById("status-" + session);
        if (el) el.innerText = "✅ Terhubung";

        const botCard = document.getElementById("bot-" + session);
        if (botCard) botCard.remove();

        if (!document.querySelector(`button[data-bot="${session}"]`)) {
            const row = document.createElement("tr");
            const index = botTable.children.length + 1;

            row.innerHTML = `
                <td>${index}</td>
                <td>${session}</td>
                <td>0</td>
                <td><span class="badge bg-success">Aktif</span></td>
                <td>
                    <div class="d-flex justify-content-center" role="group">
                        <button class="btn btn-sm ${botTerpilih === session ? 'btn-success' : 'btn-outline-primary'} pilih-bot" data-bot="${session}">
                            ${botTerpilih === session ? "✅ Terpilih" : "Gunakan"}
                        </button>
                        <button class="btn btn-sm btn-warning disconnect-bot" data-bot="${session}">🔌 Disconnect</button>
                        <button class="btn btn-sm btn-info reconnect-bot" data-bot="${session}">♻ Reconnect</button>
                    </div>
                </td>
            `;
            botTable.appendChild(row);
            setActionListeners();
            updateTombolAksi();
        }
    });

    // Log
    socket.on("log", ({ waktu, session, to, pesan, status }) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td></td>
            <td>${waktu}</td>
            <td><span class="badge bg-label-success">${session}</span></td>
            <td>${to}</td>
            <td>${pesan}</td>
            <td>${status.includes("Gagal") ? '<span class="badge bg-danger">❌ Gagal</span>' : '<span class="badge bg-success">✅ Terkirim</span>'}</td>
        `;
        logTable.prepend(row);
        [...logTable.children].forEach((tr, i) => {
            tr.children[0].innerText = i + 1;
        });
    });

    // === ACTION LISTENERS ===
    function setActionListeners() {
        // pilih bot
        document.querySelectorAll(".pilih-bot").forEach(button => {
            button.onclick = function () {
                const selected = this.dataset.bot;
                fetch("https://enagih-chat.niscala.net/set-selected-bot", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ sessionName: selected }),
                })
                .then(res => res.json())
                .then((data) => {
                    if (data.selectedBot) {
                        botTerpilih = data.selectedBot;
                        updateTombolAksi();
                        tampilkanBotTerpilih();
                    } else {
                        Swal.fire("Gagal", data.error || "Unknown error", "error");
                    }
                })
                .catch((err) => {
                    Swal.fire("Error", "Gagal terhubung ke server bot", "error");
                    console.error(err);
                });
            };
        });

        // disconnect bot
        document.querySelectorAll(".disconnect-bot").forEach(button => {
            button.onclick = function () {
                const session = this.dataset.bot;
                Swal.fire({
                    title: `Yakin ingin disconnect bot ${session}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Disconnect",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("https://enagih-chat.niscala.net/disconnect-bot", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ sessionName: session }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.error ? "error" : "success",
                                title: data.error ? "Gagal" : "Berhasil",
                                text: data.status || data.error || "Bot berhasil di-disconnect",
                                timer: 2000,
                                showConfirmButton: false,
                                topLayer: true
                            });
                        })
                        .catch(err => {
                            console.error("❌ Gagal disconnect bot:", err);
                            Swal.fire("Error", "Gagal disconnect bot", "error");
                        });
                    }
                });
            };
        });

        // reconnect bot
        document.querySelectorAll(".reconnect-bot").forEach(button => {
            button.onclick = function () {
                const session = this.dataset.bot;
                Swal.fire({
                    title: `Ingin reconnect bot ${session}?`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Reconnect",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch("https://enagih-chat.niscala.net/reconnect-bot", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ sessionName: session }),
                        })
                        .then(res => res.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.error ? "error" : "success",
                                title: data.error ? "Gagal" : "Berhasil",
                                text: data.status || data.error || "Bot berhasil direconnect",
                                timer: 2000,
                                showConfirmButton: false,
                                topLayer: true
                            });
                        })
                        .catch(err => {
                            console.error("❌ Gagal reconnect bot:", err);
                            Swal.fire("Error", "Gagal reconnect bot", "error");
                        });
                    }
                });
            };
        });
    }

    function updateTombolAksi() {
        document.querySelectorAll(".pilih-bot").forEach(btn => {
            if (btn.dataset.bot === botTerpilih) {
                btn.innerText = "✅ Terpilih";
                btn.classList.remove("btn-outline-primary");
                btn.classList.add("btn-success");
            } else {
                btn.innerText = "Gunakan";
                btn.classList.remove("btn-success");
                btn.classList.add("btn-outline-primary");
            }
        });
    }

    // tambah bot baru
    document.getElementById("tambahBotBtn").addEventListener("click", () => {
        Swal.fire({
            title: "Masukkan nama session bot",
            input: "text",
            showCancelButton: true,
            confirmButtonText: "Tambah",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                fetch("https://enagih-chat.niscala.net/tambah-bot", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ sessionName: result.value }),
                })
                .then(res => res.json())
                .then(data => {
                    Swal.fire({
                        icon: data.error ? "error" : "success",
                        title: data.error ? "Gagal" : "Berhasil",
                        text: data.status || data.error || "Berhasil menambahkan bot",
                        timer: 2000,
                        showConfirmButton: false,
                        topLayer: true
                    });
                })
                .catch(err => {
                    console.error("❌ Gagal tambah bot:", err);
                    Swal.fire("Error", "Gagal menambahkan bot", "error");
                });
            }
        });
    });
</script>
@endsection