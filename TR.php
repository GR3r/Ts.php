<?php
// 🤪 เช็คว่าเข้ามาแบบ POST หรือเปล่าวะ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    
    // 🗃️ เก็บข้อมูล GPS ลง Session 
    session_start();
    if (!isset($_SESSION['track_points'])) {
        $_SESSION['track_points'] = [];
    }
    
    $_SESSION['track_points'][] = [
        'lat' => $data['lat'],
        'lng' => $data['lng'],
        'speed' => $data['speed'],
        'timestamp' => time()
    ];
    
    // 🧮 คำนวณระยะทางและความเร็ว
    $points = $_SESSION['track_points'];
    $total_distance = 0;
    $avg_speed = 0;
    
    if (count($points) > 1) {
        for ($i = 1; i < count($points); $i++) {
            $total_distance += calculateDistance(
                $points[$i-1]['lat'], 
                $points[$i-1]['lng'],
                $points[$i]['lat'], 
                $points[$i]['lng']
            );
        }
        $time_diff = end($points)['timestamp'] - $points[0]['timestamp'];
        $avg_speed = ($time_diff > 0) ? ($total_distance / $time_diff) * 3600 : 0;
    }
    
    die(json_encode([
        'status' => '👍',
        'distance' => round($total_distance, 2),
        'avg_speed' => round($avg_speed, 2)
    ]));
}

// 📏 ฟังก์ชันคำนวณระยะทาง
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    return $dist * 60 * 1.1515 * 1.609344;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚗 ระบบติดตาม GPS สุดเจ๋ง</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        /* 🎨 CSS สุดแจ่ม */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Kanit', sans-serif;
            background: #1a1a1a;
            color: #fff;
        }
        
        #map {
            height: 70vh;
            width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .container {
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .stats {
            background: #333;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .stat-box {
            text-align: center;
            padding: 10px;
            background: #444;
            border-radius: 8px;
        }
        
        .btn {
            background: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .btn:disabled {
            background: #666;
        }
        
        .title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="title">🚀 GPS Tracker ตัวเทพ!</h1>
        
        <div class="stats">
            <div class="stat-box">
                <h3>⚡ ความเร็ว</h3>
                <div id="speed">0 กม./ชม.</div>
            </div>
            <div class="stat-box">
                <h3>📏 ระยะทาง</h3>
                <div id="distance">0 กม.</div>
            </div>
            <div class="stat-box">
                <h3>⏱️ เวลา</h3>
                <div id="time">00:00:00</div>
            </div>
        </div>

        <button id="startBtn" class="btn">▶️ เริ่มการติดตาม</button>
        <button id="stopBtn" class="btn" disabled>⏹️ หยุดการติดตาม</button>
        
        <div id="map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // 🎮 JavaScript แบบโหดๆ
        let map, marker, path;
        let tracking = false;
        let startTime = null;
        let watchId = null;
        
        // 🗺️ ตั้งค่าแผนที่
        map = L.map('map').setView([13.7563, 100.5018], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        
        // 👊 ปุ่มควบคุม
        document.getElementById('startBtn').onclick = startTracking;
        document.getElementById('stopBtn').onclick = stopTracking;
        
        function startTracking() {
            if (!navigator.geolocation) {
                alert('เบราว์เซอร์ของคุณไม่รองรับ GPS 😭');
                return;
            }
            
            tracking = true;
            startTime = new Date();
            document.getElementById('startBtn').disabled = true;
            document.getElementById('stopBtn').disabled = false;
            
            // 📍 เริ่มติดตาม GPS
            watchId = navigator.geolocation.watchPosition(
                updatePosition,
                (error) => alert('เกิดข้อผิดพลาด: ' + error.message),
                {
                    enableHighAccuracy: true,
                    maximumAge: 0
                }
            );
            
            // เริ่มอัพเดทเวลา
            setInterval(updateTime, 1000);
        }
        
        function stopTracking() {
            tracking = false;
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
            
            if (watchId !== null) {
                navigator.geolocation.clearWatch(watchId);
                watchId = null;
            }
        }
        
        function updatePosition(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            const speed = position.coords.speed ? position.coords.speed * 3.6 : 0;
            
            // 🎯 อัพเดทตำแหน่งบนแผนที่
            if (!marker) {
                marker = L.marker([lat, lng]).addTo(map);
                path = L.polyline([], {color: '#ff0000'}).addTo(map);
            }
            
            marker.setLatLng([lat, lng]);
            path.addLatLng([lat, lng]);
            map.setView([lat, lng]);
            
            // 📊 อัพเดทสถิติ
            document.getElementById('speed').innerHTML = 
                `${speed.toFixed(1)} กม./ชม.`;
            
            // 🌐 ส่งข้อมูลไปเซิร์ฟเวอร์
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    lat: lat,
                    lng: lng,
                    speed: speed
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('distance').innerHTML = 
                    `${data.distance} กม.`;
            });
        }
        
        function updateTime() {
            if (!tracking || !startTime) return;
            
            const now = new Date();
            const diff = now - startTime;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            
            document.getElementById('time').innerHTML = 
                `${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
        }
        
        function pad(num) {
            return num.toString().padStart(2, '0');
        }
    </script>
</body>
</html>
