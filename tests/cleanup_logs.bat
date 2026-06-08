@echo off
REM Nettoie les fichiers de log dans ce dossier tests/
echo Nettoyage des fichiers de logs dans %~dp0 ...
DEL /Q "%~dp0debug_*.log" 2>nul
DEL /Q "%~dp0*.log" 2>nul
necho Suppression terminée.
echo Fichiers restants:
dir /B "%~dp0*.log" || echo Aucun fichier .log restant.
exit /B 0
