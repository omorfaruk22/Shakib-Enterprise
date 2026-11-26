$ErrorActionPreference = 'Stop'

$mysql = 'C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe'
$instanceDirectory = Join-Path $env:LOCALAPPDATA 'MySQL\SAKIB_ENTERPRISE'
$config = Join-Path $instanceDirectory 'my.ini'
$secretFile = Join-Path $instanceDirectory 'root-password.dpapi'
$listener = Get-NetTCPConnection -LocalPort 3306 -State Listen -ErrorAction SilentlyContinue | Select-Object -First 1

if (!$listener) {
    Write-Output 'No local MySQL listener is running on port 3306.'
    exit 0
}

$owner = Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)" -ErrorAction SilentlyContinue
if (!$owner -or $owner.Name -ne 'mysqld.exe' -or $owner.CommandLine -notlike "*$config*") {
    throw 'Port 3306 belongs to another process; nothing was stopped.'
}
if (!(Test-Path -LiteralPath $secretFile)) {
    throw 'The encrypted local MySQL administrator secret is missing.'
}

$cipherText = (Get-Content -LiteralPath $secretFile -Raw).Trim()
$securePassword = ConvertTo-SecureString -String $cipherText
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
try {
    $env:MYSQL_PWD = [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    & $mysql --protocol=tcp --host=127.0.0.1 --port=3306 --user=root --execute='SHUTDOWN' 2>$null
    if ($LASTEXITCODE -ne 0) {
        throw 'MySQL did not accept the shutdown command.'
    }
    Write-Output 'MySQL stopped cleanly.'
} finally {
    Remove-Item Env:\MYSQL_PWD -ErrorAction SilentlyContinue
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
}
