<?php
$pageTitle = 'Live Queue Display';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <h2>Live Queue</h2>
        <span class="pill"><i class="fa-solid fa-tv"></i> Lobby Screen</span>
    </div>

    <h3 style="color: var(--primary-light); margin-bottom: 1rem;"><i class="fa-solid fa-bell"></i> Currently Serving</h3>
    <table>
        <thead>
            <tr>
                <th><i class="fa-solid fa-desktop"></i> Counter</th>
                <th><i class="fa-solid fa-list-ul"></i> Service</th>
                <th><i class="fa-solid fa-ticket"></i> Token</th>
                <th><i class="fa-solid fa-circle-info"></i> Status</th>
            </tr>
        </thead>
        <tbody id="currentBody"></tbody>
    </table>

    <div class="spacer"></div>
    <h3 style="color: var(--text-muted); margin-bottom: 1rem;"><i class="fa-regular fa-clock"></i> Waiting Tokens</h3>
    <table>
        <thead>
            <tr>
                <th><i class="fa-solid fa-list-ul"></i> Service</th>
                <th><i class="fa-solid fa-ticket"></i> Token</th>
                <th><i class="fa-solid fa-circle-info"></i> Status</th>
            </tr>
        </thead>
        <tbody id="waitingBody"></tbody>
    </table>
</section>

    <div style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
        <button id="soundBtn" onclick="toggleSound()" style="padding: 10px 20px; border-radius: 50px; border: none; background: var(--text-muted); color: white; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
            <i class="fa-solid fa-volume-xmark"></i> Sound Off
        </button>
    </div>

    <!-- Visual Speaking Indicator -->
    <div id="speakingIndicator" style="display: none; position: fixed; top: 20px; right: 20px; background: rgba(0,0,0,0.8); color: white; padding: 10px 20px; border-radius: 5px; z-index: 2000;">
        <i class="fa-solid fa-volume-high fa-beat"></i> Speaking...
    </div>

    <script>
        let soundEnabled = false;
        let lastServedTokens = new Map(); 
        let isFirstLoad = true; // Track if it's the first data fetch

        function toggleSound() {
            soundEnabled = !soundEnabled;
            const btn = document.getElementById('soundBtn');
            if (soundEnabled) {
                btn.innerHTML = '<i class="fa-solid fa-volume-high"></i> Sound On';
                btn.style.background = 'var(--primary)';
                btn.style.boxShadow = '0 0 15px var(--primary)';
                const u = new SpeechSynthesisUtterance('');
                window.speechSynthesis.speak(u);
            } else {
                btn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i> Sound Off';
                btn.style.background = 'var(--text-muted)';
                btn.style.boxShadow = 'none';
            }
        }



        function announce(token, counter) {
            if (!soundEnabled) return;
            
            const text = `Token ${token}, please proceed to ${counter}`;
            console.log("Speaking:", text);

            const indicator = document.getElementById('speakingIndicator');
            indicator.style.display = 'block';
            
            window.speechSynthesis.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'en-US';
            utterance.rate = 0.9;
            utterance.pitch = 1;

            utterance.onend = function() {
                indicator.style.display = 'none';
            };
            utterance.onerror = function(e) {
                console.error("Speech Error:", e);
                indicator.style.display = 'none';
            };

            window.speechSynthesis.speak(utterance);
        }

        function getStatusPill(status) {
            if (!status) return '-';
            const lower = status.toLowerCase();
            let icon = '';
            if (lower === 'serving') icon = '<i class="fa-solid fa-spinner fa-spin-pulse"></i> ';
            if (lower === 'calling') icon = '<i class="fa-solid fa-bullhorn fa-shake"></i> ';
            if (lower === 'waiting') icon = '<i class="fa-regular fa-clock"></i> ';
            
            return `<span class="status-pill status-${lower}">${icon}${status}</span>`;
        }

        function refreshDisplay() {
            fetch('api.php')
                .then(r => r.json())
                .then(d => {
                    let cur = '';
                    const currentTokens = new Map();

                    d.current.forEach(row => {
                        cur += `<tr>
                            <td style="font-weight:600; color:var(--primary-light);">${row.counter_name}</td>
                            <td>${row.service_name}</td>
                            <td style="font-size:1.2em; font-weight:700;">${row.token_code || '-'}</td>
                            <td>${getStatusPill(row.status)}</td>
                        </tr>`;

                        if (row.token_code) {
                            const key = row.token_code;
                            const newVal = row.counter_name;
                            currentTokens.set(key, newVal);

                            // Announcement Logic
                            // If NOT first load, and (token is new OR moved counter)
                            if (!isFirstLoad) {
                                if (!lastServedTokens.has(key) || lastServedTokens.get(key) !== newVal) {
                                    console.log("New update detected:", key);
                                    announce(row.token_code, row.counter_name);
                                }
                            }
                        }
                    });

                    // Update track map
                    lastServedTokens = currentTokens;
                    
                    // Mark first load as done
                    if (isFirstLoad) {
                        console.log("First load complete. Initial tokens:", lastServedTokens);
                        isFirstLoad = false;
                    }

                    document.getElementById('currentBody').innerHTML = cur;

                    let wait = '';
                    d.waiting.forEach(row => {
                        wait += `<tr>
                            <td>${row.service_name}</td>
                            <td>${row.token_code}</td>
                            <td>${getStatusPill(row.status)}</td>
                        </tr>`;
                    });
                    document.getElementById('waitingBody').innerHTML = wait;
                })
                .catch(err => console.error("API Error:", err));
        }
        setInterval(refreshDisplay, 3000);
        refreshDisplay();
    </script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
