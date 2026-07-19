@echo off
echo Starting Portfolio Engine Dev Server...
echo.
echo Available at: http://localhost:8000
echo Use ?post_id=home to preview a page
echo.
echo Press Ctrl+C to stop the server.
echo.
php -S localhost:8000 "%~dp0server.php"
pause
