@extends('layouts.contentNavbarLayout')
@section('title', 'Peta Mapping')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="card-title fw-bold">Peta Mapping</h4>
                <small class="card-subtitle">Peta mapping untuk melihat lokasi server, olt, odc, odp, dan customer</small>
            </div>
        </div>
        <div class="card">
            <div id="map" style="height: 520px;"></div>
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
    const map = L.map('map').setView([-9.5, 110.5], 10);
    const bounds = L.latLngBounds();

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '© Panda'
    }).addTo(map);
    

    const colors = {
        server: 'red',
        olt: 'orange',
        odc: 'green',
        odp: 'blue',
        customer: 'purple'
    };

    const icons = {
        server: 'bx-server fw-bold',
        olt: 'bx-terminal fw-bold',
        odc: 'bx-terminal fw-bold',
        odp: 'bx-terminal fw-bold',
        customer: 'bx-user fw-bold'
    };

    const data = await fetch("{{ route('peta.data') }}").then(res => res.json());

    const nodes = {
        server: {},
        olt: {},
        odc: {},
        odp: {},
        customer: {}
    };

    const linesDrawn = [];

    data.forEach(item => {
        const latlng = [item.lat, item.lng];
        bounds.extend(latlng);
        nodes[item.jenis][item.id] = item;

        const customIcon = L.divIcon({
            className: '',
            html: `<i class='bx ${icons[item.jenis]}' style="font-size: 24px; color: ${colors[item.jenis]};"></i>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const marker = L.marker(latlng, { icon: customIcon }).addTo(map);

        const detailPopup = `
            <b>${item.jenis.toUpperCase()}</b><br>
            Nama: ${item.nama}<br>
            Koordinat: ${item.lat}, ${item.lng}<br>
        `;

        marker.on('mouseover', function () {
            marker.bindPopup(detailPopup).openPopup();
        });

        marker.on('mouseout', function () {
            map.closePopup();
        });

        marker.on('click', function () {
            drawConnections(item);
        });
    });

    function drawConnections(item) {
        linesDrawn.forEach(line => map.removeLayer(line));
        linesDrawn.length = 0;

        const connection = [];

        if (item.jenis === 'customer') {
            const odp = nodes.odp[item.odp_id];
            const odc = odp ? nodes.odc[odp.odc_id] : null;
            const olt = odc ? nodes.olt[odc.olt_id] : null;
            const server = olt ? nodes.server[olt.server_id] : null;

            if (odp) connection.push([item, odp]);
            if (odc) connection.push([odp, odc]);
            if (olt) connection.push([odc, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'odp') {
            const odc = nodes.odc[item.odc_id];
            const olt = odc ? nodes.olt[odc.olt_id] : null;
            const server = olt ? nodes.server[olt.server_id] : null;

            if (odc) connection.push([item, odc]);
            if (olt) connection.push([odc, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'odc') {
            const olt = nodes.olt[item.olt_id];
            const server = olt ? nodes.server[olt.server_id] : null;

            if (olt) connection.push([item, olt]);
            if (server) connection.push([olt, server]);

        } else if (item.jenis === 'olt') {
            const server = nodes.server[item.server_id];
            if (server) connection.push([item, server]);
        }

        connection.forEach(([child, parent]) => {
            if (child && parent) {
                const line = L.polyline([
                    [child.lat, child.lng],
                    [parent.lat, parent.lng]
                ], {
                    color: colors[child.jenis] || 'gray',
                    weight: 3,
                    opacity: 0.8
                }).addTo(map);
                linesDrawn.push(line);
            }
        });
    }

    map.fitBounds(bounds);

    // Legend
    const legend = L.control({ position: "bottomright" });
    legend.onAdd = function () {
        const div = L.DomUtil.create("div", "info legend");
        const types = Object.keys(colors);
        div.innerHTML += `<strong>Legenda</strong><br>`;
        types.forEach(key => {
            div.innerHTML += `
                <i class='bx ${icons[key]}' style="color:${colors[key]};font-size:16px;"></i>
                ${key.charAt(0).toUpperCase() + key.slice(1)}<br>`;
        });
        return div;
    };
    legend.addTo(map);
});
</script>