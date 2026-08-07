@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..\..") do set "ROOT_DIR=%%~fI"
set "DEP_DIR=%ROOT_DIR%\dev\dependencies"
set "PHP_VERSION=8.5"
set "PHP_DIR=%DEP_DIR%\php-v-%PHP_VERSION%"

call "%SCRIPT_DIR%path-instal.bat" auto

if exist "%PHP_DIR%\current\php.exe" (
    echo PHP %PHP_VERSION% is already installed in %PHP_DIR%!
    goto END
)

echo Installing PHP %PHP_VERSION% into %PHP_DIR%...
powershell -NoProfile -ExecutionPolicy Bypass -Command "& ([ScriptBlock]::Create((irm 'https://www.php.net/include/download-instructions/windows.ps1'))) -Version %PHP_VERSION% -Scope Custom -CustomPath '%PHP_DIR%'"

:END
if "%~1" neq "auto" pause
endlocal
