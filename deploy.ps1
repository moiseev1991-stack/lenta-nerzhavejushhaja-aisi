# Deploy to Space Web (SFTP + SSH)
# Run from project root: .\deploy.ps1
# You will be asked for SSH password TWICE (SFTP, then SSH). Use your Space Web account password.

$ErrorActionPreference = "Stop"
$ProjectRoot = $PSScriptRoot
$Remote = "infogkmeta@77.222.40.49"

Set-Location $ProjectRoot

Write-Host "=== Step 1: Upload files via SFTP ===" -ForegroundColor Cyan
Write-Host "When prompted for password, enter your Space Web account password." -ForegroundColor Yellow
Write-Host ""

& sftp -P 22 -b "$ProjectRoot\deploy-sftp-batch.txt" $Remote

if ($LASTEXITCODE -ne 0) {
    Write-Host "SFTP upload failed. Check password and connection." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== Step 2: Set permissions via SSH ===" -ForegroundColor Cyan
Write-Host "Enter the same password again when prompted." -ForegroundColor Yellow
Write-Host ""

$sshCommands = "cd lenta-nerzhavejushhaja-aisi; chmod -R 755 storage; chmod 664 storage/database.sqlite 2>/dev/null; chmod -R 755 public_html/uploads 2>/dev/null; echo DONE"
& ssh -p 22 $Remote $sshCommands

if ($LASTEXITCODE -ne 0) {
    Write-Host "SSH chmod failed. You can run manually: ssh $Remote" -ForegroundColor Yellow
    Write-Host "Then: cd lenta-nerzhavejushhaja-aisi && chmod -R 755 storage && chmod -R 755 public_html/uploads" -ForegroundColor Gray
} else {
    Write-Host ""
    Write-Host "Deploy finished. Open https://www.lenta-nerzhavejushhaja-aisi.ru" -ForegroundColor Green
}
