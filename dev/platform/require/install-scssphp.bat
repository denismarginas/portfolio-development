@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..\..") do set "ROOT_DIR=%%~fI"
set "DEP_DIR=%ROOT_DIR%\dev\dependencies"
set "SCSSPHP_DIR=%DEP_DIR%\scssphp"

call "%SCRIPT_DIR%path-instal.bat" auto

if exist "%SCSSPHP_DIR%\.git" (
    echo scssphp is already installed in %SCSSPHP_DIR%!
    goto END
)

echo Cloning scssphp into %SCSSPHP_DIR%...
git clone https://github.com/scssphp/scssphp.git "%SCSSPHP_DIR%"

:END
if "%~1" neq "auto" pause
endlocal
