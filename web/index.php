<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring & Kendali IoT</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/paho-mqtt/1.0.1/mqttws31.min.js"></script>
    
    <style>
        body { font-family: sans-serif; background: #f0f2f5; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: auto; }
        .card { background: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .row { display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
        .nilai-box { font-size: 24px; font-weight: bold; }
        .btn { padding: 15px 30px; font-size: 16px; margin: 5px; border: none; border-radius: 5px; cursor: pointer; color: white; transition: 0.3s; }
        .btn-on { background-color: #28a745; }
        .btn-off { background-color: #dc3545; }
        .btn:hover { opacity: 0.8; }
        .status-aman { color: green; }
        .status-warning { color: orange; }
        .status-bahaya { color: red; font-weight: bold; }
        canvas { max-height: 400px; }
    </style>
</head>
<body>

<div class="container">
    <h1>Monitoring Suhu & Kelembaban Real-time</h1>
    <h3>NIM: <span id="nim-display">-</span></h3>

    <div class="row">
        <div class="card" style="flex:1;">
            <h3>Suhu (°C)</h3>
            <div id="temp-val" class="nilai-box">-</div>
            <div id="temp-status">-</div>
        </div>
        <div class="card" style="flex:1;">
            <h3>Kelembaban (%)</h3>
            <div id="hum-val" class="nilai-box">-</div>
            <div id="hum-status">-</div>
        </div>
    </div>

    <div class="card">
        <canvas id="myChart"></canvas>
    </div>

    <div class="card">
        <h2>Kendali LED NodeMCU</h2>
        <div class="row">
            <div>
                <h4>LED 1 (D6)</h4>
                <button class="btn btn-on" onclick="kirimPerintah('LED1=1')">ON</button>
                <button class="btn btn-off" onclick="kirimPerintah('LED1=0')">OFF</button>
            </div>
            <div>
                <h4>LED 2 (D7)</h4>
                <button class="btn btn-on" onclick="kirimPerintah('LED2=1')">ON</button>
                <button class="btn btn-off" onclick="kirimPerintah('LED2=0')">OFF</button>
            </div>
            <div>
                <h4>LED 3 (D8)</h4>
                <button class="btn btn-on" onclick="kirimPerintah('LED3=1')">ON</button>
                <button class="btn btn-off" onclick="kirimPerintah('LED3=0')">OFF</button>
            </div>
        </div>
    </div>
</div>

<script>
    // --- KONFIGURASI SESUAI SOAL ---
    const mqtt_server = "x2.revolusi-it.com";
    const mqtt_port = 9001; // Port WebSocket (Bukan 1883)
    
    // GANTI INI DENGAN NIM KAMU!
    const TOPIC_NIM = "iot/G.231.23.0050"; 
    // Client ID Web: NIM + Angka Acak (Poin 2)
    const CLIENT_ID = "G.231.23.0050-" + Math.floor(Math.random() * 100000);

    const mqtt_user = "usm";
    const mqtt_pass = "usmjaya25";

    // Inisialisasi Chart
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Suhu (°C)',
                borderColor: 'red',
                data: []
            }, {
                label: 'Kelembaban (%)',
                borderColor: 'blue',
                data: []
            }]
        }
    });

    // Setup MQTT Client
    const client = new Paho.MQTT.Client(mqtt_server, mqtt_port, CLIENT_ID);

    client.onConnectionLost = function(responseObject) {
        console.log("Koneksi Putus: " + responseObject.errorMessage);
        setTimeout(connectMQTT, 5000); // Reconnect
    };

    client.onMessageArrived = function(message) {
        console.log("Pesan Masuk: " + message.payloadString);
        try {
            const data = JSON.parse(message.payloadString);
            updateDashboard(data);
            simpanKeDatabase(data);
        } catch (e) {
            console.error("Bukan format JSON", e);
        }
    };

    function connectMQTT() {
        client.connect({
            userName: mqtt_user,
            password: mqtt_pass,
            onSuccess: function() {
                console.log("Terhubung ke MQTT!");
                client.subscribe(TOPIC_NIM);
            },
            onFailure: function(e) {
                console.log("Gagal Konek: " + e.errorMessage);
            }
        });
    }

    connectMQTT();

    function kirimPerintah(cmd) {
        message = new Paho.MQTT.Message(cmd);
        message.destinationName = TOPIC_NIM;
        client.send(message);
    }

    // --- LOGIKA UTAMA (UPDATE GUI & BEEP) ---
    function updateDashboard(data) {
        document.getElementById('nim-display').innerText = data.nim;
        document.getElementById('temp-val').innerText = data.t;
        document.getElementById('hum-val').innerText = data.h;

        // Update Chart
        const now = new Date().toLocaleTimeString();
        if(myChart.data.labels.length > 20) { // Batasi 20 data
            myChart.data.labels.shift();
            myChart.data.datasets[0].data.shift();
            myChart.data.datasets[1].data.shift();
        }
        myChart.data.labels.push(now);
        myChart.data.datasets[0].data.push(data.t);
        myChart.data.datasets[1].data.push(data.h);
        myChart.update();

        // --- LOGIKA ALARM (POIN 5) ---
        let totalBeep = 0;
        let humStatusText = "";

        // 1. Cek Suhu
        if (data.t > 29 && data.t < 30) { totalBeep = Math.max(totalBeep, 1); }
        else if (data.t >= 30 && data.t <= 31) { totalBeep = Math.max(totalBeep, 2); }
        else if (data.t > 31) { totalBeep = Math.max(totalBeep, 3); }

        // 2. Cek Kelembaban
        if (data.h >= 30 && data.h < 60) {
            humStatusText = "Kering/Aman";
            document.getElementById('hum-status').className = "status-aman";
        } else if (data.h >= 60 && data.h < 70) {
            humStatusText = "Mulai banyak uap air/Normal";
            document.getElementById('hum-status').className = "status-warning";
            totalBeep = Math.max(totalBeep, 1); // Beep 1x
        } else if (data.h >= 70) {
            humStatusText = "Terdapat banyak uap air";
            document.getElementById('hum-status').className = "status-bahaya";
            totalBeep = Math.max(totalBeep, 3); // Beep 3x
        }
        document.getElementById('hum-status').innerText = humStatusText;

        // Eksekusi Beep
        if (totalBeep > 0) {
            mainkanBeep(totalBeep);
        }
    }

    // Fungsi Beep menggunakan AudioContext (Biar gak perlu file mp3)
    function mainkanBeep(jumlah) {
        const AudioContext = window.AudioContext || window.webkitAudioContext;
        if (!AudioContext) return;
        
        const audioCtx = new AudioContext();
        let waktu = audioCtx.currentTime;

        for (let i = 0; i < jumlah; i++) {
            const oscillator = audioCtx.createOscillator();
            const gainNode = audioCtx.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioCtx.destination);
            
            oscillator.type = 'sine';
            oscillator.frequency.value = 1000; // Frekuensi Beep
            
            // Timing beep (bunyi 200ms, jeda 100ms)
            oscillator.start(waktu + (i * 0.3));
            oscillator.stop(waktu + (i * 0.3) + 0.2);
        }
    }

    // Fungsi Simpan ke Database via PHP
    function simpanKeDatabase(data) {
        const formData = new FormData();
        formData.append('suhu', data.t);
        formData.append('kelembaban', data.h);

        fetch('simpan_data.php', {
            method: 'POST',
            body: formData
        }).then(response => response.text())
          .then(res => console.log("DB Saved: " + res))
          .catch(err => console.error("DB Error: " + err));
    }
</script>

</body>
</html>