@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..") do set "ROOT_DIR=%%~fI"
set "PORT=8088"
set "REQUIRE_DIR=%ROOT_DIR%\dev\platform\require"
set "PHP_LOCAL=%ROOT_DIR%\dev\dependencies\php-v-8.5\current\php.exe"
set "PHP_EXE="

call "%REQUIRE_DIR%\path-instal.bat" auto

if exist "%PHP_LOCAL%" (
    set "PHP_EXE=%PHP_LOCAL%"
) else (
    if exist "%REQUIRE_DIR%\install-php.bat" (
        call "%REQUIRE_DIR%\install-php.bat" auto
    )
)

if not defined PHP_EXE if exist "%PHP_LOCAL%" set "PHP_EXE=%PHP_LOCAL%"

if not defined PHP_EXE (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
)

if not defined PHP_EXE (
    echo PHP was not found.
    echo Install PHP or let %REQUIRE_DIR%\install-php.bat bootstrap the local dependency.
    pause
    exit /b 1
)

start "Platform PHP Server" "%PHP_EXE%" -S 127.0.0.1:%PORT% -t "%ROOT_DIR%"
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:%PORT%/dev/platform/"

endlocal
