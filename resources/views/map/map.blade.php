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
            <div id="map" style="height: 600px;"></div>
        </div>
    </div>
</div>
@endsection

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const map = L.map('map').setView([-7.5, 110.5], 10);
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
        
        const lineColors = {
            server: 'red',
            olt: 'orange',
            odc: 'green',
            odp: 'blue',
            customer: 'purple'
        };
        
        const data = await fetch("{{ route('peta.data') }}").then(res => res.json());
        
        const nodes = {
            server: {},
            olt: {},
            odc: {},
            odp: {},
            customer: {}
        };
        
        data.forEach(item => {
            const marker = L.circleMarker([item.lat, item.lng], {
                radius: 7,
                color: colors[item.jenis],
                fillColor: colors[item.jenis],
                fillOpacity: 0.8,
                weight: 1
            }).addTo(map);

            const detailPopup = `
                <b>${item.jenis.toUpperCase()}</b><br>
                Nama: ${item.nama}<br>
                Koordinat: ${item.lat}, ${item.lng}<br>
            `;

            // Tampilkan detail saat hover
            marker.on('mouseover', function (e) {
                marker.bindPopup(detailPopup).openPopup();
            });

            marker.on('mouseout', function (e) {
                map.closePopup();
            });

            
            bounds.extend([item.lat, item.lng]);
            nodes[item.jenis][item.id] = item;
        });
        
        // Tarik garis penghubung antar node
        data.forEach(item => {
            let parent = null;
            let parentJenis = null;
            
            if (item.jenis === 'olt' && item.server_id) {
                parent = nodes.server[item.server_id];
                parentJenis = 'server';
            }
            
            if (item.jenis === 'odc' && item.olt_id) {
                parent = nodes.olt[item.olt_id];
                parentJenis = 'olt';
            }
            
            if (item.jenis === 'odp' && item.odc_id) {
                parent = nodes.odc[item.odc_id];
                parentJenis = 'odc';
            }
            
            if (item.jenis === 'customer' && item.odp_id) {
                parent = nodes.odp[item.odp_id];
                parentJenis = 'odp';
            }
            
            if (parent && parent.lat && parent.lng) {
                L.polyline([
                [item.lat, item.lng],
                [parent.lat, parent.lng]
                ], {
                    color: lineColors[item.jenis] || 'gray',
                    weight: 2,
                    opacity: 0.7
                }).addTo(map);
            }
        });
        
        map.fitBounds(bounds);
        
        // Legend
        const legend = L.control({ position: "bottomright" });
        legend.onAdd = function () {
            const div = L.DomUtil.create("div", "info legend");
            const types = Object.keys(colors);
            div.innerHTML += `<strong>E-Nagih</strong><br>`;
            types.forEach(key => {
                div.innerHTML += `
                    <i style="background:${colors[key]};width:12px;height:12px;display:inline-block;margin-right:5px;"></i>
                    ${key.charAt(0).toUpperCase() + key.slice(1)}<br>`;
            });
            return div;
        };
        legend.addTo(map);
    });
</script>
