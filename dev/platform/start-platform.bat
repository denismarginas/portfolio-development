@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..") do set "ROOT_DIR=%%~fI"
set "PORT=8088"
set "PHP_LOCAL=C:\php\current\php.exe"
set "INSTALLER=%ROOT_DIR%\dev\platform\require\install-php.bat"
set "PHP_EXE="

if exist "%PHP_LOCAL%" (
    set "PHP_EXE=%PHP_LOCAL%"
) else (
    for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP_EXE set "PHP_EXE=%%P"
    if not defined PHP_EXE (
        if exist "%INSTALLER%" (
            call "%INSTALLER%"
        ) else (
            echo PHP was not found and dev\platform\require\install-php.bat is missing.
            pause
            exit /b 1
        )
    )
    if exist "%PHP_LOCAL%" set "PHP_EXE=%PHP_LOCAL%"
)

if not defined PHP_EXE (
    echo PHP was not found.
    echo Install PHP or let dev\platform\require\install-php.bat bootstrap the local dependency.
    pause
    exit /b 1
)

start "Platform PHP Server" "%PHP_EXE%" -S 127.0.0.1:%PORT% -t "%ROOT_DIR%"
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:%PORT%/dev/platform/"

endlocal
