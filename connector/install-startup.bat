@echo off
setlocal
cd /d "%~dp0"
if not exist "%~dp0.env" (
    copy "%~dp0.env.example" "%~dp0.env"
    echo Created .env. Edit it with the current F495_IP and website key, then run this file again.
    pause
    exit /b 1
)
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "$startup=[Environment]::GetFolderPath('Startup'); $target=Join-Path $startup 'F-G495 Connector.lnk'; $w=New-Object -ComObject WScript.Shell; $s=$w.CreateShortcut($target); $s.TargetPath='%~dp0start-connector.bat'; $s.WorkingDirectory='%~dp0'; $s.Save(); Write-Host ('Startup shortcut created: ' + $target)"
echo The connector will start when this Windows user logs in.
pause