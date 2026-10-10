param(
    [string] $Image = 'snipe/snipe-it:latest',
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $PhpUnitArguments
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$dockerArguments = @(
    'run',
    '--rm',
    '--volume', "${projectRoot}:/var/www/html",
    '--volume', '/var/run/docker.sock:/var/run/docker.sock',
    '--workdir', '/var/www/html',
    $Image,
    'php',
    'tests/Run-Testcontainers.php'
) + $PhpUnitArguments

& docker @dockerArguments
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
