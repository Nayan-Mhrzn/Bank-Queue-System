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

<script>
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
                d.current.forEach(row => {
                    cur += `<tr>
                        <td style="font-weight:600; color:var(--primary-light);">${row.counter_name}</td>
                        <td>${row.service_name}</td>
                        <td style="font-size:1.2em; font-weight:700;">${row.token_code || '-'}</td>
                        <td>${getStatusPill(row.status)}</td>
                    </tr>`;
                });
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
            });
    }
    setInterval(refreshDisplay, 3000);
    refreshDisplay();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
