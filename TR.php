<?php
/**
 * 🚀 GPS Tracker Pro v3.0
 * 📅 Last Updated: 2025-04-07 11:43:33 UTC
 * 👤 Developer: GR3r
 * 🔒 Licensed under MIT
 */

session_start();
date_default_timezone_set('UTC');
header('Content-Type: text/html; charset=utf-8');

// 🔒 Security Headers
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="GPS Tracker Pro - ระบบติดตาม GPS แบบเรียลไทม์">
    <meta name="author" content="GR3r">
    <title>🚀 GPS Tracker Pro v3.0</title>
    
    <!-- 🎨 Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* 🎨 Custom Properties */
        :root {
            --primary: #00ff88;
            --primary-dark: #00cc6a;
            --secondary: #00ccff;
            --dark: #1a1a1a;
            --darker: #0f0f0f;
            --light: #ffffff;
            --card: rgba(255, 255, 255, 0.1);
            --danger: #ff4444;
            --warning: #ffbb33;
            --success: #00C851;
        }

        /* 🌟 Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Kanit', sans-serif;
            background: linear-gradient(135deg, var(--darker) 0%, var(--dark) 100%);
            color: var(--light);
            min-height: 100vh;
            line-height: 1.6;
        }

        /* 🌈 Glass Morphism */
        .glass {
            background: var(--card);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        /* 📱 Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* 🎯 Header */
        .header {
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 2em;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* 🗺️ Map */
        #map {
            height: 60vh;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        #map:hover {
            box-shadow: 0 12px 48px rgba(0, 255, 136, 0.2);
        }

        /* 📊 Stats Grid */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-value {
            font-size: 2em;
            font-weight: bold;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 10px 0;
        }

        /* 🎮 Controls */
        .control-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .btn {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            color: var(--dark);
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
        }

        .btn:hover::after {
            transform: translateX(100%);
            transition: all 0.5s ease;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        /* 📱 Status Badges */
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
            margin: 5px;
            background: var(--primary);
            color: var(--dark);
            transition: all 0.3s ease;
        }

        .status-badge:hover {
            transform: scale(1.05);
        }

        /* 🎯 Accuracy Meter */
        .accuracy-meter {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            margin: 10px 0;
            overflow: hidden;
        }

        .accuracy-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: width 0.3s ease;
        }

        /* 🌈 Animations */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .float {
            animation: float 3s ease-in-out infinite;
        }

        /* 📱 Responsive Design */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .stat-card {
                margin-bottom: 10px;
            }
        }

        /* 🌙 Dark Mode Enhancement */
        @media (prefers-color-scheme: dark) {
            .glass {
                background: rgba(0, 0, 0, 0.3);
            }
        }

        /* 🎨 Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header glass">
            <h1><i class="fas fa-satellite"></i> GPS Tracker Pro</h1>
            <div>
                <span class="status-badge" id="connectionStatus">
                    <i class="fas fa-signal"></i> ออนไลน์
                </span>
                <span class="status-badge" id="batteryStatus">
                    <i class="fas fa-battery-full"></i> 100%
                </span>
                <span class="status-badge" id="timeStatus">
                    <i class="fas fa-clock"></i> <?php echo date('H:i:s'); ?>
                </span>
            </div>
        </header>

        <div id="map" class="glass"></div>

        <div class="control-panel">
            <button id="startBtn" class="btn">
                <i class="fas fa-play"></i> เริ่มการนำทาง
            </button>
            <button id="stopBtn" class="btn" style="display:none">
                <i class="fas fa-stop"></i> หยุดการนำทาง
            </button>
            <button id="shareBtn" class="btn">
                <i class="fas fa-share-alt"></i> แชร์ตำแหน่ง
            </button>
            <button id="saveBtn" class="btn">
                <i class="fas fa-save"></i> บันทึกเส้นทาง
            </button>
        </div>

        <div class="accuracy-meter">
            <div id="accuracyBar" class="accuracy-bar" style="width: 0%"></div>
        </div>

        <div class="stats-container">
            <div class="stat-card glass float">
                <h3><i class="fas fa-tachometer-alt"></i> ความเร็วปัจจุบัน</h3>
                <div id="currentSpeed" class="stat-value">0 กม/ชม</div>
                <div id="speedAlert"></div>
            </div>

            <div class="stat-card glass float">
                <h3><i class="fas fa-road"></i> ระยะทางรวม</h3>
                <div id="totalDistance" class="stat-value">0 กม</div>
                <div id="weather"></div>
            </div>

            <div class="stat-card glass float">
                <h3><i class="fas fa-stopwatch"></i> เวลาที่ใช้</h3>
                <div id="totalTime" class="stat-value">0:00:00</div>
                <div id="pace"></div>
            </div>

            <div class="stat-card glass float">
                <h3><i class="fas fa-chart-line"></i> ความเร็วเฉลี่ย</h3>
                <div id="avgSpeed" class="stat-value">0 กม/ชม</div>
                <div id="efficiency"></div>
            </div>
        </div>
    </div>

    <!-- 🗺️ Map & Utils -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // 🌟 Initialize Variables
        let map, marker, path;
        let isTracking = false;
        let startTime = null;
        let positions = [];
        let totalDistance = 0;
        let trackData = {
            timestamps: [],
            speeds: [],
            positions: []
        };

        // 🗺️ Initialize Map
        function initMap() {
            map = L.map('map').setView([13.7563, 100.5018], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        }

        // 🔋 Battery Status
        navigator.getBattery().then(battery => {
            function updateBattery() {
                const level = Math.round(battery.level * 100);
                let icon = 'battery-full';
                if (level <= 20) icon = 'battery-quarter';
                else if (level <= 50) icon = 'battery-half';
                else if (level <= 75) icon = 'battery-three-quarters';
                
                document.getElementById('batteryStatus').innerHTML = 
                    `<i class="fas fa-${icon}"></i> ${level}%`;
            }
            battery.addEventListener('levelchange', updateBattery);
            updateBattery();
        });

        // 📍 Calculate Distance
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3;
            const φ1 = lat1 * Math.PI/180;
            const φ2 = lat2 * Math.PI/180;
            const Δφ = (lat2-lat1) * Math.PI/180;
            const Δλ = (lon2-lon1) * Math.PI/180;

            const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
                    Math.cos(φ1) * Math.cos(φ2) *
                    Math.sin(Δλ/2) * Math.sin(Δλ/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

            return R * c;
        }

        // ⚡ Update Stats
        function updateStats(position) {
            const speed = position.coords.speed ? position.coords.speed * 3.6 : 0;
            trackData.speeds.push(speed);
            trackData.timestamps.push(Date.now());

            document.getElementById('currentSpeed').textContent = 
                `${speed.toFixed(1)} กม/ชม`;

            // Update time
            const currentTime = new Date().getTime();
            const elapsedTime = (currentTime - startTime) / 1000;
            const hours = Math.floor(elapsedTime / 3600);
            const minutes = Math.floor((elapsedTime % 3600) / 60);
            const seconds = Math.floor(elapsedTime % 60);
            
            document.getElementById('totalTime').textContent = 
                `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

            // Update distance
            document.getElementById('totalDistance').textContent = 
                `${(totalDistance / 1000).toFixed(2)} กม`;

            // Update average speed
            const avgSpeed = totalDistance / elapsedTime * 3.6;
            document.getElementById('avgSpeed').textContent = 
                `${avgSpeed.toFixed(1)} กม/ชม`;

            // Update efficiency
            const efficiency = speed > 0 ? (totalDistance / elapsedTime / speed) * 100 : 0;
            document.getElementById('efficiency').textContent = 
                `ประสิทธิภาพ: ${efficiency.toFixed(0)}%`;

            // Speed alerts
            const speedAlert = document.getElementById('speedAlert');
            if (speed > 80) {
                speedAlert.innerHTML = '⚠️ ความเร็วเกินกำหนด!';
                speedAlert.style.color = var(--danger);
                speedAlert.classList.add('pulse');
            } else {
                speedAlert.innerHTML = '';
                speedAlert.classList.remove('pulse');
            }
        }

        // 📍 Update Position
        function updatePosition(position) {
            const { latitude, longitude, accuracy } = position.coords;
            
            // Update accuracy indicator
            const accuracyPercentage = Math.max(0, Math.min(100, 100 - (accuracy / 100)));
            document.getElementById('accuracyBar').style.width = `${accuracyPercentage}%`;

            if (!marker) {
                marker = L.marker([latitude, longitude]).addTo(map);
                path = L.polyline([], {
                    color: var(--primary),
                    weight: 3,
                    opacity: 0.8
                }).addTo(map);
            }

            marker.setLatLng([latitude, longitude]);
            map.setView([latitude, longitude]);
            
            positions.push([latitude, longitude]);
            trackData.positions.push({
                lat: latitude,
                lng: longitude,
                timestamp: Date.now()
            });
            
            path.setLatLngs(positions);

            if (positions.length > 1) {
                const lastPos = positions[positions.length - 2];
                totalDistance += calculateDistance(
                    lastPos[0], lastPos[1],
                    latitude, longitude
                );
            }

            updateStats(position);
            
            // Update weather every 5 minutes
            if (positions.length % 300 === 0) {
                getWeather(latitude, longitude);
            }
        }

        // 🌡️ Weather Integration
        async function getWeather(lat, lon) {
            try {
                const response = await fetch(
                    `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=YOUR_API_KEY&units=metric`
                );
                const data = await response.json();
                document.getElementById('weather').innerHTML = 
                    `<i class="fas fa-temperature-high"></i> ${data.main.temp}°C ${data.weather[0].description}`;
            } catch (err) {
                console.error('Weather error:', err);
            }
        }

        // 💾 Save Track Data
        document.getElementById('saveBtn').addEventListener('click', () => {
            const trackJson = JSON.stringify(trackData);
            const blob = new Blob([trackJson], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `track_${new Date().toISOString()}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });

        // 📤 Share Location
        document.getElementById('shareBtn').addEventListener('click', () => {
            if (positions.length > 0) {
                const lastPos = positions[positions.length - 1];
                const url = `https://www.google.com/maps?q=${lastPos[0]},${lastPos[1]}`;
                if (navigator.share) {
                    navigator.share({
                        title: 'ตำแหน่งของฉัน',
                        text: 'นี่คือตำแหน่งปัจจุบันของฉัน!',
                        url: url
                    }).catch(console.error);
                } else {
                    window.open(url, '_blank');
                }
            }
        });

        // 🎮 Start/Stop Controls
        document.getElementById('startBtn').addEventListener('click', () => {
            if (!isTracking) {
                navigator.geolocation.getCurrentPosition((position) => {
                    startTime = new Date().getTime();
                    isTracking = true;
                    positions = [];
                    totalDistance = 0;
                    trackData = {
                        timestamps: [],
                        speeds: [],
                        positions: []
                    };
                    
                    document.getElementById('startBtn').style.display = 'none';
                    document.getElementById('stopBtn').style.display = 'block';
                    
                    navigator.geolocation.watchPosition(updatePosition, null, {
                        enableHighAccuracy: true,
                        maximumAge: 0
                    });
                });
            }
        });

        document.getElementById('stopBtn').addEventListener('click', () => {
            isTracking = false;
            document.getElementById('startBtn').style.display = 'block';
            document.getElementById('stopBtn').style.display = 'none';
            document.getElementById('connectionStatus').innerHTML = 
                '<i class="fas fa-signal"></i> การนำทางสิ้นสุด';
        });

        // 🚀 Initialize
        initMap();

        // ⏰ Update time
        setInterval(() => {
            document.getElementById('timeStatus').innerHTML = 
                `<i class="fas fa-clock"></i> ${new Date().toLocaleTimeString()}`;
        }, 1000);

        // 📡 Check connection
        window.addEventListener('online', () => {
            document.getElementById('connectionStatus').innerHTML = 
                '<i class="fas fa-signal"></i> ออนไลน์';
        });

        window.addEventListener('offline', () => {
            document.getElementById('connectionStatus').innerHTML = 
                '<i class="fas fa-signal-slash"></i> ออฟไลน์';
        });
    </script>
</body>
</html>
