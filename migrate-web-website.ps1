# Moves every project's web screenshots into web\website\:
#   web\desktop\                         -> web\website\desktop\
#   web\phone\                           -> web\website\phone\
#   web\overview\web_desktop_overview.*  -> web\website\desktop\web_desktop_0.*   (first image = cover)
#   web\overview\web_phone_overview.*    -> web\website\phone\web_phone_0.*
#   other old overview images (extra copies in web\overview\ or loose web\*overview* files) are deleted,
#   then the empty web\overview\ folder is removed; any non-overview file left in it is reported.
#   web\content-website\ and web\media-website\ are not touched.
#
# Dry run first (only prints):  powershell -ExecutionPolicy Bypass -File migrate-web-website.ps1 -DryRun
# Then for real:                powershell -ExecutionPolicy Bypass -File migrate-web-website.ps1

param([switch]$DryRun)

$ErrorActionPreference = 'Stop'
$img = 'D:\Projects Files\Web\portfolio-development\src\content\img\projects'
$warnings = New-Object System.Collections.Generic.List[string]

function Move-Entry([string]$from, [string]$to) {
    if ($DryRun) { Write-Host "    [dry] $from -> $to" ; return }
    Move-Item -LiteralPath $from -Destination $to
}

function New-Folder([string]$path) {
    if (Test-Path -LiteralPath $path) { return }
    if ($DryRun) { Write-Host "    [dry] mkdir $path"; return }
    New-Item -ItemType Directory -Force -Path $path | Out-Null
}

function Remove-Entry([string]$path) {
    if ($DryRun) { Write-Host "    [dry] delete $path"; return }
    Write-Host "    delete $path"
    Remove-Item -LiteralPath $path -Force
}

function Add-Warning([string]$text) {
    $warnings.Add($text)
    Write-Warning $text
}

$done = 0
foreach ($project in Get-ChildItem -LiteralPath $img -Directory) {
    $web = Join-Path $project.FullName 'web'
    if (-not (Test-Path -LiteralPath $web)) { continue }

    $site = Join-Path $web 'website'
    $overview = Join-Path $web 'overview'
    $looseOverview = @(Get-ChildItem -LiteralPath $web -File | Where-Object { $_.Name -like '*overview*' })
    $todo = (Test-Path -LiteralPath (Join-Path $web 'desktop')) -or (Test-Path -LiteralPath (Join-Path $web 'phone')) -or
            (Test-Path -LiteralPath $overview) -or ($looseOverview.Count -gt 0)
    if (-not $todo) { continue }

    Write-Host $project.Name
    $done++

    # 1. web\desktop, web\phone -> web\website\desktop, web\website\phone
    foreach ($device in 'desktop', 'phone') {
        $src = Join-Path $web $device
        if (-not (Test-Path -LiteralPath $src)) { continue }
        $dst = Join-Path $site $device
        if (-not (Test-Path -LiteralPath $dst)) {
            New-Folder $site
            Move-Entry $src $dst
            continue
        }
        # web\website\<device> already exists -> merge file by file
        foreach ($file in Get-ChildItem -LiteralPath $src -File) {
            $target = Join-Path $dst $file.Name
            if (Test-Path -LiteralPath $target) { Add-Warning "$($project.Name): $device\$($file.Name) already in website\$device - left in web\$device" }
            else { Move-Entry $file.FullName $target }
        }
        if (-not $DryRun -and -not (Get-ChildItem -LiteralPath $src -Force)) { Remove-Item -LiteralPath $src }
    }

    # 2. overview images -> web_<device>_0 (first image of the device folder)
    $candidates = @()
    if (Test-Path -LiteralPath $overview) { $candidates += @(Get-ChildItem -LiteralPath $overview -File) }
    $candidates += $looseOverview          # e.g. web\web_desktop_overview.webp outside the overview folder
    $used = @()
    foreach ($device in 'desktop', 'phone') {
        $found = @($candidates | Where-Object { $_.Name -like "*$device*" })
        if ($found.Count -eq 0) { continue }
        $file = $found[0]
        $dstDir = Join-Path $site $device
        New-Folder $site
        New-Folder $dstDir
        $target = Join-Path $dstDir ("web_${device}_0" + $file.Extension.ToLower())
        if (Test-Path -LiteralPath $target) {
            Add-Warning "$($project.Name): website\$device\$(Split-Path $target -Leaf) already exists - $($file.Name) not moved (kept)"
            $used += $file.FullName
            continue
        }
        Move-Entry $file.FullName $target
        $used += $file.FullName
    }

    # 3. clean up: every other old overview image is a leftover copy -> delete
    foreach ($file in $candidates) {
        if ($used -contains $file.FullName) { continue }
        if ($file.Name -like '*overview*' -or $file.Name -like '*desktop*' -or $file.Name -like '*phone*') { Remove-Entry $file.FullName }
    }

    # 4. remove the empty overview folder
    if (Test-Path -LiteralPath $overview) {
        $left = @(Get-ChildItem -LiteralPath $overview -Force)
        if ($DryRun) {
            $handled = @($left | Where-Object { $_.Name -like '*overview*' -or $_.Name -like '*desktop*' -or $_.Name -like '*phone*' })
            if ($left.Count -gt $handled.Count) { Add-Warning "$($project.Name): web\overview would keep $($left.Count - $handled.Count) other file(s)" }
            else { Write-Host "    [dry] rmdir $overview" }
        }
        elseif ($left.Count -eq 0) { Remove-Item -LiteralPath $overview }
        else { Add-Warning "$($project.Name): web\overview not empty: $(($left | ForEach-Object Name) -join ', ')" }
    }
}

Write-Host ""
Write-Host "$done project(s) $(if ($DryRun) { 'would be' } else { 'were' }) migrated."
if ($warnings.Count) {
    Write-Host "$($warnings.Count) warning(s):" -ForegroundColor Yellow
    $warnings | ForEach-Object { Write-Host "  $_" -ForegroundColor Yellow }
}
