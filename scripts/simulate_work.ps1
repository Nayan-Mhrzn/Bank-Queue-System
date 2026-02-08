param (
    [switch]$Demo
)

Set-Location "c:\xampp\htdocs\Bank_queue"

$commitMessages = @(
    "Update styling for customer view",
    "Fix padding issue in token display",
    "Refactor database connection logic",
    "WIP: optimizing query performance",
    "Update staff dashboard layout",
    "Fix typo in admin panel",
    "Add comments to call_next.php",
    "Adjust color scheme for night mode",
    "Prepare sql schema for updates",
    "Minor bug fix in token generation",
    "Update README documentation",
    "Refactor session handling",
    "Clean up unused variables",
    "Optimize image loading",
    "Update footer copyright year"
)

# Total target time ~10 hours = 600 minutes
# 10 commits -> ~9 intervals
# Average sleep ~66 minutes. Let's say random between 40 and 80 minutes.

$logFile = "changelog.md"

Write-Host "Starting work simulation..."
if ($Demo) {
    Write-Host "DEMO MODE: Sleep times will be minimal." -ForegroundColor Yellow
}

for ($i = 1; $i -le 10; $i++) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $msg = $commitMessages | Get-Random
    
    # Update a file so there is something to commit
    $logEntry = "- [$timestamp] $msg"
    Add-Content -Path $logFile -Value $logEntry
    
    # Git operations
    git add $logFile
    git commit -m "$msg"
    git push

    Write-Host "[$i/10] Committed: '$msg' at $timestamp"

    if ($i -lt 10) {
        if ($Demo) {
            $sleepSeconds = Get-Random -Minimum 2 -Maximum 5
            Write-Host "Sleeping for $sleepSeconds seconds (Demo)..."
            Start-Sleep -Seconds $sleepSeconds
        } else {
            # Random sleep between 40 and 80 minutes
            $sleepMinutes = Get-Random -Minimum 40 -Maximum 80
            $sleepSeconds = $sleepMinutes * 60
            Write-Host "Sleeping for $sleepMinutes minutes..."
            Start-Sleep -Seconds $sleepSeconds
        }
    }
}

Write-Host "Daily work simulation complete!" -ForegroundColor Green
