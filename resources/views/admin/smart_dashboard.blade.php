@extends('layouts.dashboard')

@section('title', 'Smart City Level Dashboard')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge"></i> Overview</a>
    <a href="{{ route('admin.smart-dashboard') }}" class="active"><i class="fa-solid fa-city"></i> Smart Dashboard</a>
    <a href="{{ route('admin.bins') }}"><i class="fa-solid fa-trash-can"></i> Smart Bins</a>
    <a href="{{ route('admin.requests') }}"><i class="fa-solid fa-list"></i> Requests</a>
    <a href="{{ route('admin.users') }}"><i class="fa-solid fa-users-gear"></i> User Management</a>
@endsection

@section('head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --accent-color: #1abc9c;
    }
    body { background: #f0f2f5; }
    #map { height: 600px; width: 100%; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.5s ease; }
    .map-fullscreen { position: fixed !important; top: 0; left: 0; width: 100vw !important; height: 100vh !important; z-index: 9999; border-radius: 0 !important; }
    
    .glass-card { background: var(--glass-bg); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.2); border-radius: 15px; transition: transform 0.3s ease; }
    .glass-card:hover { transform: translateY(-5px); }
    
    .stat-card-premium { padding: 25px; border-radius: 15px; color: white; position: relative; overflow: hidden; }
    .stat-card-premium i { position: absolute; right: -10px; bottom: -10px; font-size: 5rem; opacity: 0.15; transform: rotate(-15deg); }
    
    .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
    .bg-gradient-danger { background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); }

    .insight-item { border-left: 4px solid #1abc9c; padding-left: 15px; margin-bottom: 15px; animation: slideInLeft 0.5s ease; }
    .insight-danger { border-color: #ff0844; }
    .insight-warning { border-color: #f6d365; }

    /* Chatbot Styles */
    .chatbot-widget { position: fixed; bottom: 20px; right: 20px; width: 350px; z-index: 2000; }
    .chat-card { border-radius: 15px; overflow: hidden; display: none; }
    .chat-header { background: var(--accent-color); color: white; padding: 12px 15px; cursor: pointer; }
    .chat-body { height: 350px; overflow-y: auto; background: #fff; padding: 15px; }
    .chat-msg { margin-bottom: 10px; padding: 10px 14px; border-radius: 18px; max-width: 85%; font-size: 0.9rem; line-height: 1.4; }
    .bot-msg { background: #f0f2f5; color: #333; align-self: flex-start; }
    .user-msg { background: var(--accent-color); color: white; align-self: flex-end; margin-left: auto; }

    .btn-simulation { transition: all 0.3s ease; border-radius: 30px; padding: 10px 25px; font-weight: 600; }
    .btn-simulation:hover { transform: scale(1.05); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
</style>
@endsection

@section('content')
<!-- Header & Simulation Controls -->
<div class="d-flex justify-content-between align-items-center mb-4 animate__animated animate__fadeIn">
    <div>
        <h2 class="mb-1 fw-bold">Smart City Control Center</h2>
        <p class="text-muted">Intelligent AI-driven waste management & prediction</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary btn-simulation shadow-sm" onclick="triggerSimulation('demo')">
            <i class="fa-solid fa-play me-2"></i> Run Demo Mode
        </button>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="stat-card-premium bg-gradient-primary animate__animated animate__zoomIn">
            <i class="fa-solid fa-truck-fast"></i>
            <h6>Pickup Efficiency</h6>
            <h3>{{ $kpis['efficiency_index'] }}/10</h3>
            <small>Based on completion time</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium bg-gradient-success animate__animated animate__zoomIn" style="animation-delay: 0.1s">
            <i class="fa-solid fa-tree"></i>
            <h6>Environmental Impact</h6>
            <h3>{{ $kpis['trees_saved'] }}</h3>
            <p class="mb-0 small">Trees saved equivalent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium bg-gradient-warning animate__animated animate__zoomIn" style="animation-delay: 0.2s">
            <i class="fa-solid fa-clock"></i>
            <h6>Avg. Response Time</h6>
            <h3>{{ $kpis['avg_pickup_time'] }}m</h3>
            <small>Request to Disposal</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card-premium bg-gradient-danger animate__animated animate__zoomIn" style="animation-delay: 0.3s">
            <i class="fa-solid fa-leaf"></i>
            <h6>Carbon Reduced</h6>
            <h3>{{ $kpis['carbon_reduced'] }}kg</h3>
            <small>CO2 offset total</small>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Main Map -->
    <div class="col-md-8">
        <div class="card glass-card h-100 overflow-hidden">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0"><i class="fa-solid fa-map-location-dot me-2 text-primary"></i>Live Heatmap & Real-Time Tracking</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen()">
                        <i class="fa-solid fa-expand"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="loadHeatmap()"><i class="fa-solid fa-rotate"></i></button>
                </div>
            </div>
            <div class="position-relative">
                <div id="map"></div>
                <div class="heatmap-legend">
                    <h6>Waste Density</h6>
                    <div class="legend-item"><div class="color-box" style="background: #FF0000; opacity: 0.5;"></div> High (Urgent)</div>
                    <div class="legend-item"><div class="color-box" style="background: #FFFF00; opacity: 0.5;"></div> Medium</div>
                    <div class="legend-item"><div class="color-box" style="background: #00FF00; opacity: 0.5;"></div> Low (Clean)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Insights -->
    <div class="col-md-4">
        <div class="card glass-card h-100">
            <div class="card-body">
                <h5 class="mb-4"><i class="fa-solid fa-brain me-2 text-info"></i>Smart Insights & Alerts</h5>
                <div id="insightsContainer">
                    @forelse($insights as $insight)
                    <div class="insight-item insight-{{ $insight->type }}">
                        <p class="mb-0 fw-bold">{{ $insight->message }}</p>
                        <small class="text-muted">{{ $insight->created_at->diffForHumans() }}</small>
                    </div>
                    @empty
                    <div class="text-center py-5">
                        <i class="fa-solid fa-check-circle fa-3x text-success mb-3 opacity-25"></i>
                        <p>No critical alerts detected. System is running smoothly.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Prediction Chart -->
    <div class="col-md-8">
        <div class="card glass-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Waste Generation History (Last 7 Days)</h5>
                    <span class="badge bg-soft-success text-success">Real-time Data</span>
                </div>
                <div style="height: 300px; position: relative;">
                    <canvas id="predictionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- Composition -->
    <div class="col-md-4">
        <div class="card glass-card">
            <div class="card-body">
                <h5><i class="fa-solid fa-chart-pie me-2 text-warning"></i>Waste Composition</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="compositionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chatbot -->
<div class="chatbot-widget">
    <div class="chat-card card shadow-lg animate__animated animate__fadeInUp" id="chatCard">
        <div class="chat-header d-flex justify-content-between align-items-center" onclick="toggleChat()">
            <span><i class="fa-solid fa-robot me-2"></i> EcoBot AI Assistant</span>
            <i class="fa-solid fa-chevron-down"></i>
        </div>
        <div class="chat-body d-flex flex-column" id="chatBody">
            <div class="chat-msg bot-msg">Welcome to the Smart City Portal! I can help you with predictions, fleet status, and environmental reports. What would you like to know?</div>
        </div>
        <div class="p-3 bg-white border-top">
            <div class="input-group">
                <input type="text" id="chatInput" class="form-control border-0 bg-light" placeholder="Type your query..." onkeypress="if(event.key === 'Enter') sendMessage()">
                <button class="btn btn-primary rounded-circle ms-2" style="width: 40px; height: 40px; padding: 0;" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
    <button class="btn btn-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" id="chatToggle" style="width: 60px; height: 60px;" onclick="toggleChat()">
        <i class="fa-solid fa-comment-dots fa-lg"></i>
    </button>
</div>

@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps.key') }}&libraries=visualization&language=en"></script>
<script>
    let map;
    let heatmap;
    let driverMarkers = {};

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 31.2559, lng: 75.7051 },
            zoom: 13,
            minZoom: 5,
            restriction: {
                latLngBounds: {
                    north: 35.5133,
                    south: 6.4626,
                    west: 68.1097,
                    east: 97.3953
                },
                strictBounds: false
            },
            disableDefaultUI: false,
            styles: [
                { "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#e9e9e9" }, { "lightness": 17 }] },
                { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 20 }] },
                { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }, { "lightness": 17 }] }
            ]
        });

        loadHeatmap();
        updateDrivers();
        setInterval(updateDrivers, 5000);
    }

    async function triggerSimulation(type) {
        const response = await fetch('{{ route('api.simulate') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ type: type })
        });
        const data = await response.json();
        alert(data.message);
        location.reload();
    }

    function toggleFullscreen() {
        document.getElementById('map').classList.toggle('map-fullscreen');
        setTimeout(() => google.maps.event.trigger(map, 'resize'), 500);
    }

    async function loadHeatmap() {
        const response = await fetch(`/api/heatmap-data`);
        const data = await response.json();
        
        if (heatmap) heatmap.setMap(null);
        
        const heatmapPoints = data.map(cell => {
            return {
                location: new google.maps.LatLng(cell.lat, cell.lng),
                weight: cell.count
            };
        });

        heatmap = new google.maps.visualization.HeatmapLayer({
            data: heatmapPoints,
            map: map,
            radius: 30,
            opacity: 0.7,
            dissipating: true
        });
    }

    async function updateDrivers() {
        const response = await fetch('/api/drivers');
        const drivers = await response.json();
        drivers.forEach(driver => {
            const pos = { lat: parseFloat(driver.lat), lng: parseFloat(driver.lng) };
            if (driverMarkers[driver.driver_id]) {
                driverMarkers[driver.driver_id].setPosition(pos);
            } else {
                driverMarkers[driver.driver_id] = new google.maps.Marker({
                    position: pos, map: map, title: driver.name,
                    icon: { path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW, scale: 5, fillColor: "#1abc9c", fillOpacity: 1, strokeWeight: 2 }
                });
            }
        });
    }

    async function loadAnalytics() {
        const res = await fetch('/api/analytics-data');
        const data = await res.json();
        
        // History Chart
        new Chart(document.getElementById('predictionChart'), {
            type: 'line',
            data: {
                labels: data.trends.map(t => t.date),
                datasets: [{
                    label: 'Waste Requests',
                    data: data.trends.map(t => t.count),
                    borderColor: '#1abc9c',
                    backgroundColor: 'rgba(26, 188, 156, 0.1)',
                    fill: true, tension: 0.4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Composition Chart
        new Chart(document.getElementById('compositionChart'), {
            type: 'doughnut',
            data: {
                labels: data.categories.map(c => c.name),
                datasets: [{
                    data: data.categories.map(c => c.count),
                    backgroundColor: ['#1abc9c', '#3498db', '#f1c40f', '#e74c3c', '#9b59b6']
                }]
            },
            options: { responsive: true, cutout: '70%' }
        });
    }

    // Chatbot (Simplified)
    function toggleChat() {
        const card = document.getElementById('chatCard');
        card.style.display = card.style.display === 'none' ? 'block' : 'none';
    }

    async function sendMessage() {
        const input = document.getElementById('chatInput');
        const body = document.getElementById('chatBody');
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        body.innerHTML += `<div class="chat-msg user-msg">${msg}</div>`;
        body.scrollTop = body.scrollHeight;
        const res = await fetch('/api/chatbot', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: msg })
        });
        const data = await res.json();
        setTimeout(() => {
            body.innerHTML += `<div class="chat-msg bot-msg">${data.response}</div>`;
            body.scrollTop = body.scrollHeight;
        }, 500);
    }

    window.onload = () => {
        initMap();
        loadAnalytics();
    };
</script>
@endsection
