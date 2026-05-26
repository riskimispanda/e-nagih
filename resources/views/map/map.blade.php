@extends('layouts.contentNavbarLayout')
@section('title', 'Peta Mapping')

@section('content')
<style>
    .map-marker-hover {
        transition: transform 0.2s ease-in-out;
    }
    .map-marker-hover:hover {
        transform: scale(1.25);
        z-index: 1000 !important;
    }
    .info.legend {
        background: white;
        padding: 12px 16px;
        font-family: 'Public Sans', sans-serif;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        line-height: 22px;
        color: #333;
        font-size: 12px;
        border: 1px solid #e6e6e6;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
        font-weight: 500;
    }
    .legend-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 8px;
        display: inline-block;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title fw-bold m-0">Peta Mapping Real-time</h4>
                    <small class="card-subtitle text-muted">Pemantauan jalur BTS, OLT, ODC, ODP, dan Pelanggan berdasarkan status MikroTik</small>
                </div>
                <button onclick="location.reload()" class="btn btn-sm btn-primary">
                    <i class="bx bx-refresh me-1"></i> Refresh Status
                </button>
            </div>
        </div>
        <div class="card">
            <div id="map" style="height: 600px; border-radius: 8px;"></div>
        </div>
    </div>
</div>
@endsection

<!-- Tambahkan Boxicons dan Leaflet -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet' />

<script>
document.addEventListener("DOMContentLoaded", async () => {
    const map = L.map('map').setView([-8.0, 110.4], 10);
    const bounds = L.latLngBounds();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '© E-Nagih Panda'
    }).addTo(map);

    const statusColors = {
        online: '#28c76f',   // Hijau
        warning: '#ff9f43',  // Orange
        offline: '#ea5455',  // Merah
        empty: '#a8aaae',    // Abu-abu (kosong)
        unknown: '#a8aaae'
    };

    const icons = {
        server: 'bx-server',
        olt: 'bx-git-merge',
        odc: 'bx-cabinet',
        odp: 'bx-box',
        customer: 'bx-user'
    };

    const response = await fetch("{{ route('peta.data') }}");
    const data = await response.json();

    const nodes = {
        server: {},
        olt: {},
        odc: {},
        odp: {},
        customer: {}
    };

    // Index all nodes for drawing connections
    data.forEach(item => {
        nodes[item.jenis][item.id] = item;
    });

    // Draw all connections first (so lines are behind markers)
    data.forEach(item => {
        let parent = null;
        if (item.jenis === 'customer') {
            parent = nodes.odp[item.odp_id];
        } else if (item.jenis === 'odp') {
            parent = nodes.odc[item.odc_id];
        } else if (item.jenis === 'odc') {
            parent = nodes.olt[item.olt_id];
        } else if (item.jenis === 'olt') {
            parent = nodes.server[item.server_id];
        }

        if (parent && item.lat && item.lng && parent.lat && parent.lng) {
            const color = statusColors[item.status] || '#a8aaae';
            L.polyline([
                [item.lat, item.lng],
                [parent.lat, parent.lng]
            ], {
                color: color,
                weight: 4,
                opacity: 0.8,
                dashArray: item.status === 'offline' ? '6, 6' : null
            }).addTo(map);
        }
    });

    // Draw markers
    data.forEach(item => {
        const latlng = [item.lat, item.lng];
        bounds.extend(latlng);

        const color = statusColors[item.status] || '#a8aaae';
        
        // Beautiful premium marker HTML
        const customIcon = L.divIcon({
            className: '',
            html: `
                <div class="map-marker-hover" style="
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    width: 34px;
                    height: 34px;
                    background-color: white;
                    border: 3.5px solid ${color};
                    border-radius: 50%;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.18);
                ">
                    <i class='bx ${icons[item.jenis]}' style="font-size: 16px; color: ${color};"></i>
                </div>
            `,
            iconSize: [34, 34],
            iconAnchor: [17, 17]
        });

        const marker = L.marker(latlng, { icon: customIcon }).addTo(map);

        // Advanced troubleshooting popup
        let detailPopup = '';
        if (item.jenis === 'customer') {
            detailPopup = `
                <div style="font-family: 'Public Sans', sans-serif; min-width: 220px;">
                    <h6 class="fw-bold m-0 text-primary" style="font-size:14px;">${item.nama}</h6>
                    <div class="d-flex justify-content-between align-items-center my-2">
                        <span class="badge bg-label-${item.status === 'online' ? 'success' : 'danger'}" style="font-size:10px; padding: 4px 8px;">
                            ${item.status.toUpperCase()}
                        </span>
                        <small class="text-muted fw-bold">${item.details.package}</small>
                    </div>
                    <table class="table table-sm table-borderless m-0" style="font-size: 11px; line-height: 1.5;">
                        <tr><td class="p-0 fw-bold">Redaman:</td><td class="p-0 text-end text-danger fw-bold">${item.details.redaman}</td></tr>
                        <tr><td class="p-0 fw-bold">HP:</td><td class="p-0 text-end">${item.details.phone}</td></tr>
                        <tr><td class="p-0 fw-bold">Secret:</td><td class="p-0 text-end text-muted" style="word-break: break-all;">${item.details.usersecret}</td></tr>
                    </table>
                    <hr class="my-2">
                    <small class="d-block text-muted" style="font-size: 10px; line-height: 1.3;">
                        <i class="bx bx-map me-1"></i>${item.details.address}
                    </small>
                </div>
            `;
        } else {
            let desc = '';
            if (item.jenis === 'odp') {
                desc = `Total Pelanggan: <strong class="text-dark">${item.details.total_customers}</strong> (${item.details.online_customers} Online)`;
            } else if (item.jenis === 'odc') {
                desc = `Total ODP: <strong class="text-dark">${item.details.total_odps}</strong> (${item.details.online_odps} Online)`;
            } else if (item.jenis === 'olt') {
                desc = `Total ODC: <strong class="text-dark">${item.details.total_odcs}</strong> (${item.details.online_odcs} Online)`;
            } else if (item.jenis === 'server') {
                desc = `IP Address: <strong class="text-dark">${item.details.ip_address}</strong><br>Total OLT: <strong class="text-dark">${item.details.total_olts}</strong>`;
            }

            const badgeColor = item.status === 'online' ? 'success' : (item.status === 'warning' ? 'warning' : 'danger');

            detailPopup = `
                <div style="font-family: 'Public Sans', sans-serif; min-width: 180px;">
                    <h6 class="fw-bold m-0 text-dark" style="font-size:13px;">${item.nama}</h6>
                    <span class="badge bg-label-${badgeColor} my-2" style="font-size:9px; padding: 3px 6px;">
                        ${item.jenis.toUpperCase()} - ${item.status.toUpperCase()}
                    </span>
                    <p class="m-0 text-muted" style="font-size: 11px; line-height: 1.4;">${desc}</p>
                </div>
            `;
        }

        marker.bindPopup(detailPopup);

        marker.on('mouseover', function () {
            marker.openPopup();
        });

        marker.on('click', function () {
            marker.openPopup();
        });
    });

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    // Legend
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
        const div = L.DomUtil.create("div", "info legend");
        
        // Section 1: Device Types
        div.innerHTML += `<strong style="font-size: 13px; display:block; margin-bottom: 6px;">Tipe Perangkat</strong>`;
        Object.keys(icons).forEach(key => {
            div.innerHTML += `
                <div class="legend-item">
                    <i class='bx ${icons[key]} me-2' style="font-size:16px; color:#566a7f;"></i>
                    <span>${key.toUpperCase()}</span>
                </div>`;
        });

        div.innerHTML += `<hr class="my-2">`;

        // Section 2: Connection Status
        div.innerHTML += `<strong style="font-size: 13px; display:block; margin-bottom: 6px;">Status Koneksi</strong>`;
        const statusLabels = {
            online: 'Online (Aktif)',
            warning: 'Warning (Gangguan Sebagian)',
            offline: 'Offline (Putus)',
            empty: 'Kosong (Belum ada Pelanggan)'
        };
        Object.keys(statusLabels).forEach(key => {
            div.innerHTML += `
                <div class="legend-item">
                    <span class="legend-dot" style="background-color:${statusColors[key]};"></span>
                    <span>${statusLabels[key]}</span>
                </div>`;
        });

        return div;
    };
    legend.addTo(map);
});
</script>