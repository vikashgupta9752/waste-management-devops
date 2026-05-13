@extends('layouts.dashboard')

@section('title', 'Track Driver')

@section('sidebar')
    <a href="{{ route('citizen.dashboard') }}"><i class="fa-solid fa-house"></i> Dashboard</a>
@endsection

@section('content')
<div class="card p-3">
    <h4>Live Tracking: {{ $driver->name }}</h4>
    <div id="tracking-map" style="height: 500px; border-radius: 10px;"></div>
    <div class="mt-3">
        <p><strong>Status:</strong> <span id="driver-status">Loading...</span></p>
        <p><strong>Estimated ETA:</strong> <span id="eta">Calculating...</span></p>
    </div>
</div>
@endsection

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const indiaBounds = L.latLngBounds([6.4626999, 68.1097], [35.513327, 97.395358]);
    const map = L.map('tracking-map', {
        center: [31.2559, 75.7051],
        zoom: 13,
        minZoom: 5,
        maxBounds: indiaBounds,
        maxBoundsViscosity: 1.0
    });
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap contributors © CARTO',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);
    
    let marker;

    function updateLocation() {
        fetch('{{ route("api.driver-location", $driver->id) }}')
            .then(res => res.json())
            .then(data => {
                if (data && data.latitude) {
                    const pos = [data.latitude, data.longitude];
                    if (!marker) {
                        marker = L.marker(pos).addTo(map).bindPopup('{{ $driver->name }} is here').openPopup();
                        map.setView(pos);
                    } else {
                        marker.setLatLng(pos);
                    }
                    document.getElementById('driver-status').innerText = 'On the way';
                    document.getElementById('eta').innerText = '10-15 mins';
                }
            });
    }

    setInterval(updateLocation, 5000); // Update every 5 seconds
    updateLocation();
</script>
@endsection
