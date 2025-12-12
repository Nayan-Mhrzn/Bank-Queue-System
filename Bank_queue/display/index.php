<?php
$pageTitle = 'Live Queue Display';
require __DIR__ . '/../config/db.php';
include __DIR__ . '/../includes/header.php';
?>
<section class="card">
    <div class="card-header">
        <h2>Live Queue</h2>
        <span class="pill">Lobby Screen</span>
    </div>

    <h3>Currently Serving</h3>
    <table>
        <thead>
            <tr>
                <th>Counter</th>
                <th>Service</th>
                <th>Token</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="currentBody"></tbody>
    </table>

    <div class="spacer"></div>
    <h3>Waiting Tokens</h3>
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th>Token</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="waitingBody"></tbody>
    </table>
</section>

<script>
    function refreshDisplay() {
        fetch('api.php')
            .then(r => r.json())
            .then(d => {
                let cur = '';
                d.current.forEach(row => {
                    cur += `<tr>
                        <td>${row.counter_name}</td>
                        <td>${row.service_name}</td>
                        <td>${row.token_code || '-'}</td>
                        <td>${row.status || '-'}</td>
                    </tr>`;
                });
                document.getElementById('currentBody').innerHTML = cur;

                let wait = '';
                d.waiting.forEach(row => {
                    wait += `<tr>
                        <td>${row.service_name}</td>
                        <td>${row.token_code}</td>
                        <td>${row.status}</td>
                    </tr>`;
                });
                document.getElementById('waitingBody').innerHTML = wait;
            });
    }
    setInterval(refreshDisplay, 3000);
    refreshDisplay();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
