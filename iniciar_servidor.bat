@echo off
title Motor Express - Servidor de Taller Automotriz (Django + MariaDB)
color 0B
cls
echo ==========================================================
echo    SISTEMA DE GESTION DE TALLER AUTOMOTRIZ - MOTOR EXPRESS
echo          Plataforma: Python / Django + MariaDB
echo ==========================================================
echo.
echo [*] Iniciando con deteccion inteligente de puertos desocupados...
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0iniciar_servidor.ps1"

pause
