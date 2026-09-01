@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..") do set "ROOT_DIR=%%~fI"
set "PORT=8088"
set "REQUIRE_DIR=%ROOT_DIR%\dev\platform\require"
set "DEP_DIR=%ROOT_DIR%\dev\dependencies"
set "PHP_VERSION_PREFIX=php-v-8.5"
set "PHP_EXE="

if exist "%REQUIRE_DIR%\install-scssphp.bat" (
    call "%REQUIRE_DIR%\install-scssphp.bat" auto
)

call :FindLocalPhp
if defined PHP_LOCAL set "PHP_EXE=%PHP_LOCAL%"

if not defined PHP_EXE (
    if exist "%REQUIRE_DIR%\install-php.bat" (
        call "%REQUIRE_DIR%\install-php.bat" auto
    )
    call :FindLocalPhp
    if defined PHP_LOCAL set "PHP_EXE=%PHP_LOCAL%"
)

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
exit /b 0

:FindLocalPhp
rem Finds the local PHP install regardless of the exact patch-version
rem folder name (e.g. php-v-8.5, php-v-8.5.10, php-v-8.5.11, ...).
set "PHP_LOCAL="
if not exist "%DEP_DIR%" exit /b 0
for /d %%D in ("%DEP_DIR%\%PHP_VERSION_PREFIX%*") do (
    if not defined PHP_LOCAL if exist "%%D\current\php.exe" set "PHP_LOCAL=%%D\current\php.exe"
)
exit /b 0
