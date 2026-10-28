@echo off
chcp 65001 >nul 2>nul
setlocal EnableExtensions EnableDelayedExpansion
title KKTC Mekan Secici - Tek Tik Kurulum
color 0B

echo.
echo  ===============================================
echo   KKTC MEKAN SECICI - TEK TIK KURULUM
echo  ===============================================
echo.

:: --- Proje klasorunu bul ---
set "KOK=%~dp0"
if not exist "%KOK%config.php" (
    echo  [X] HATA: Bu dosya proje klasorunde degil!
    echo      KUR.bat dosyasini proje klasorune tasin ve tekrar calistirin.
    echo.
    pause
    exit /b 1
)

:: --- database klasoru ---
if not exist "%KOK%database" mkdir "%KOK%database" >nul 2>nul

:: --- PHP bul ---
set "PHP_YOL="
where php >nul 2>nul && for /f "delims=" %%i in ('where php') do if not defined PHP_YOL set "PHP_YOL=%%i"

if not defined PHP_YOL if exist "C:\xampp\php\php.exe" set "PHP_YOL=C:\xampp\php\php.exe"
if not defined PHP_YOL if exist "C:\php\php.exe" set "PHP_YOL=C:\php\php.exe"

:: Winget PHP 8.3
if not defined PHP_YOL (
    for /f "delims=" %%i in ('dir /b /s /a-d "C:\Users\*\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.*_Microsoft.Winget.Source_*\php.exe" 2^>nul') do (
        if not defined PHP_YOL set "PHP_YOL=%%i"
    )
)

:: --- XAMPP var mi? ---
set "XAMPP_VAR=0"
if exist "C:\xampp\xampp_start.exe" set "XAMPP_VAR=1"
if exist "C:\xampp\apache_start.bat" set "XAMPP_VAR=1"

echo  [1/4] Ortam kontrol ediliyor...
if defined PHP_YOL (
    echo      PHP bulundu: !PHP_YOL!
) else (
    echo      PHP bulunamadi (sadece XAMPP uzerinden denenecek).
)
if "%XAMPP_VAR%"=="1" (
    echo      XAMPP bulundu: C:\xampp
) else (
    echo      XAMPP bulunamadi.
)
echo.

:: === SENARYO 1: XAMPP var ===
if "%XAMPP_VAR%"=="1" goto XAMPP_KUR

:: === SENARYO 2: PHP var, XAMPP yok ===
if defined PHP_YOL goto PHP_KUR

:: === SENARYO 3: Hicbirisi yok ===
goto YOK_KUR

:: -------------------------------------------------------
:XAMPP_KUR
echo  [2/4] Proje XAMPP htdocs'a kopyalaniyor...

set "HEDEF=C:\xampp\htdocs\kktc_mekan_secici"
if exist "%HEDEF%" rmdir /s /q "%HEDEF%" >nul 2>nul

:: Exclude dosyasi olustur
> "%TEMP%\kktc_exclude.txt" (
    echo .git\
    echo database\
    echo KUR.bat
)

xcopy "%KOK%*" "%HEDEF%\" /E /I /Q /Y /EXCLUDE:"%TEMP%\kktc_exclude.txt" >nul 2>nul
del "%TEMP%\kktc_exclude.txt" >nul 2>nul

if not exist "%HEDEF%\index.php" (
    echo  [X] Kopyalama basarisiz! Manuel kopyalayin:
    echo      Kaynak: %KOK%
    echo      Hedef : %HEDEF%
    echo.
    pause
    exit /b 1
)
echo      OK: %HEDEF%
echo.

echo  [3/4] Apache ve MySQL baslatiliyor...

if exist "C:\xampp\apache_start.bat" (
    start "XAMPP Apache" /min "C:\xampp\apache_start.bat"
    timeout /t 2 /nobreak >nul
)
if exist "C:\xampp\mysql_start.bat" (
    start "XAMPP MySQL" /min "C:\xampp\mysql_start.bat"
    timeout /t 2 /nobreak >nul
)

net start Apache2.4 >nul 2>nul
net start MySQL >nul 2>nul

timeout /t 3 /nobreak >nul
echo      OK
echo.

echo  [4/4] Site aciliyor...
echo.
echo  ===============================================
echo   KURULUM TAMAMLANDI!
echo  ===============================================
echo.
echo   Site:    http://localhost/kktc_mekan_secici
echo   Admin:   http://localhost/kktc_mekan_secici/admin/login.php
echo   Giris:   admin / admin123
echo.
echo   Veritabani otomatik olusur: %HEDEF%\database\kktc.db
echo.
echo  ===============================================
echo.
start "" "http://localhost/kktc_mekan_secici"
timeout /t 2 /nobreak >nul
echo  Tarayici acildi. Siteyi kullanabilirsiniz.
echo.
pause
exit /b 0

:: -------------------------------------------------------
:PHP_KUR
echo  [2/4] PHP bulundu, built-in server baslatilacak...
echo      PHP: %PHP_YOL%
echo.

echo  [3/4] Proje klasoru hazir: %KOK%
echo.

echo  [4/4] Server baslatiliyor (port 8000)...
echo.
echo  ===============================================
echo   KURULUM TAMAMLANDI!
echo  ===============================================
echo.
echo   Site:    http://localhost:8000
echo   Admin:   http://localhost:8000/admin/login.php
echo   Giris:   admin / admin123
echo.
echo   Veritabani otomatik olusur: %KOK%database\kktc.db
echo.
echo   Kapatmak icin: Ctrl+C veya pencereyi kapatin
echo  ===============================================
echo.

timeout /t 2 /nobreak >nul
start "" "http://localhost:8000"

"%PHP_YOL%" -S localhost:8000 -t "%KOK%"
pause
exit /b 0

:: -------------------------------------------------------
:YOK_KUR
echo  [!] PHP ve XAMPP bulunamadi!
echo.
echo  ===============================================
echo   KURULUM YAPILAMADI
echo  ===============================================
echo.
echo  Cozum yontemleri:
echo.
echo  1) XAMPP kurun (EN KOLAY - onerilen):
echo     https://www.apachefriends.org/
echo     Kurduktan sonra tekrar bu dosyayi calistirin.
echo.
echo  2) PHP'yi elle kurun ve PATH'e ekleyin:
echo     https://windows.php.net/download/
echo     Sonra bu dosyayi tekrar calistirin.
echo.
echo  3) Manuel kurulum icin README.md dosyasina bakin.
echo.
pause
exit /b 1
