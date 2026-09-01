@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
set "FAVICON=%SCRIPT_DIR%dev\platform\assets\img\favicon\favicon.ico"
set "START_EXE=%SCRIPT_DIR%start.exe"
set "BUILD_EXE_BAT=%SCRIPT_DIR%build-start-exe.bat"

rem Keep start.exe's icon in sync with the site favicon automatically:
rem if the favicon changed (or start.exe doesn't exist yet), rebuild it
rem silently before starting the server. Best-effort only - if this
rem fails for any reason (csc.exe missing, start.exe momentarily locked
rem because it's the one currently running, ...) it never blocks the
rem actual dev server from starting, and just retries next run.
set "NEEDS_BUILD="
if exist "%FAVICON%" (
    if not exist "%START_EXE%" (
        set "NEEDS_BUILD=1"
    ) else (
        for /f %%N in ('powershell -NoProfile -Command "if ((Get-Item -LiteralPath '%FAVICON%').LastWriteTime -gt (Get-Item -LiteralPath '%START_EXE%').LastWriteTime) { 'yes' } else { 'no' }" 2^>nul') do if "%%N"=="yes" set "NEEDS_BUILD=1"
    )
)

if defined NEEDS_BUILD if exist "%BUILD_EXE_BAT%" call "%BUILD_EXE_BAT%" auto

call "%SCRIPT_DIR%dev\platform\start-platform.bat"
endlocal
