@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
set "PHP_ROOT=C:\php"
set "PHP_VERSION=8.5"

if exist "%PHP_ROOT%\current\php.exe" (
    echo PHP %PHP_VERSION% is already installed in %PHP_ROOT%!
    goto END
)

if not exist "%PHP_ROOT%" (
    mkdir "%PHP_ROOT%"
)

echo Installing PHP %PHP_VERSION% into %PHP_ROOT%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "& ([ScriptBlock]::Create((irm 'https://www.php.net/include/download-instructions/windows.ps1'))) -Version %PHP_VERSION% -Scope Custom -CustomPath '%PHP_ROOT%'"

:END
pause
endlocal
