# The Expo app now lives beside Laravel in the project root.
$projectRoot = Split-Path -Parent $PSScriptRoot
& (Join-Path $projectRoot 'start-mobile.ps1')
