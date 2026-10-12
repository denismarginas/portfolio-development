# Splits a project's image/video folders into "<web>" and "<media>" projects.
#   img\projects\<base>  ->  img\projects\<web>     (web\, web highlights, web thumbnail)
#                        ->  img\projects\<media>   (media\, logo\, highlights\media-*, media thumbnail)
#   vid\projects\<base>  ->  vid\projects\<media>
# Run from anywhere:  powershell -ExecutionPolicy Bypass -File split-project-folders.ps1

$ErrorActionPreference = 'Stop'
$root = 'D:\Projects Files\Web\portfolio-development\src\content'
$img  = Join-Path $root 'img\projects'
$vid  = Join-Path $root 'vid\projects'

function Split-Project([string]$base, [string]$web, [string]$media) {
    $src = Join-Path $img $base
    if (-not (Test-Path -LiteralPath $src)) { Write-Host "skip $base (folder not found)"; return }
    $dstMedia = Join-Path $img $media
    New-Item -ItemType Directory -Force -Path (Join-Path $dstMedia 'thumbnail'), (Join-Path $dstMedia 'highlights') | Out-Null

    foreach ($dir in 'media', 'logo') {
        $p = Join-Path $src $dir
        if (Test-Path -LiteralPath $p) { Move-Item -LiteralPath $p -Destination (Join-Path $dstMedia $dir) }
    }
    $hl = Join-Path $src 'highlights'
    if (Test-Path -LiteralPath $hl) {
        Get-ChildItem -LiteralPath $hl -Directory -Filter 'media-*' | ForEach-Object {
            Move-Item -LiteralPath $_.FullName -Destination (Join-Path $dstMedia "highlights\$($_.Name)")
        }
    }

    $th = Join-Path $src 'thumbnail'
    $thMedia = Join-Path $th "thumbnail_${base}_media.webp"
    $thWeb   = Join-Path $th "thumbnail_${base}_web.webp"
    if (Test-Path -LiteralPath $thMedia) { Move-Item -LiteralPath $thMedia -Destination (Join-Path $dstMedia "thumbnail\thumbnail_$media.webp") }
    if (Test-Path -LiteralPath $thWeb)   { Rename-Item -LiteralPath $thWeb -NewName "thumbnail_$web.webp" }

    Rename-Item -LiteralPath $src -NewName $web

    $v = Join-Path $vid $base
    if (Test-Path -LiteralPath $v) { Rename-Item -LiteralPath $v -NewName $media }

    Write-Host "$base -> $web + $media"
}

Split-Project 'agromir' 'agromir-online-shop' 'agromir-media'
