@echo off
setlocal

set "SCRIPT_DIR=%~dp0"
for %%I in ("%SCRIPT_DIR%..\..\..") do set "ROOT_DIR=%%~fI"
set "DEP_DIR=%ROOT_DIR%\dev\dependencies"

if not exist "%DEP_DIR%" (
    mkdir "%DEP_DIR%"
)

if exist "%DEP_DIR%" (
    echo Dependencies folder is ready: %DEP_DIR%
) else (
    echo Failed to create dependencies folder: %DEP_DIR%
    if "%~1" neq "auto" pause
    exit /b 1
)

if "%~1" neq "auto" pause
endlocal
