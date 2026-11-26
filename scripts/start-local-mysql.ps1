$ErrorActionPreference = 'Stop'

$server = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysqld.exe'
$config = Join-Path $env:LOCALAPPDATA 'MySQL\SAKIB_ENTERPRISE\my.ini'

if (!(Test-Path -LiteralPath $server) -or !(Test-Path -LiteralPath $config)) {
    throw 'MySQL is not installed or its per-user configuration is missing.'
}

$listener = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1
if ($listener) {
    $owner = Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)" -ErrorAction SilentlyContinue
    if ($owner -and $owner.Name -eq 'mysqld.exe' -and $owner.CommandLine -like "*$config*") {
        Write-Output 'MySQL is already running on 127.0.0.1:3306.'
        exit 0
    }
    throw 'Port 3306 is occupied by another process.'
}

$process = Start-Process -FilePath $server -ArgumentList "--defaults-file=$config" -WindowStyle Hidden -PassThru
$ready = $false
for ($attempt = 0; $attempt -lt 30; $attempt++) {
    try {
        $client = [System.Net.Sockets.TcpClient]::new()
        $client.Connect('127.0.0.1', 3306)
        $client.Close()
        $ready = $true
        break
    } catch {
        Start-Sleep -Milliseconds 500
    }
}

if (!$ready) {
    $log = Join-Path (Join-Path $env:LOCALAPPDATA 'MySQL\SAKIB_ENTERPRISE') 'mysql-error.log'
    if (Test-Path -LiteralPath $log) {
        Get-Content -LiteralPath $log -Tail 12
    }
    throw 'MySQL did not become ready. Check the error log above.'
}

Write-Output "MySQL is running on 127.0.0.1:3306 (PID $($process.Id))."
