@ECHO OFF

: PARAMETRES POUR EXECUTER LA TACHE PLANIFIEE SOUS WINDOWS
: ONGLET GENERAL: utilisation uniquement avec utilisateur connecté + cocher exécuter avec tous les privilèges
: DECLENCHEUR: ne pas oublier la répétition
: ACTION: démarrer un programme + chemin de cron.bat complet + Commencer dans [REP_ZORD]\scripts

: recuperer la date
set mydate=%date:~6,4%%date:~3,2%%date:~0,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set mydate=%mydate: =0%

: repertoire du PHP CLI (attention à la version configurée dans wamp, changer le rep en fonction
set REP_PHP=C:\wamp64\bin\php\php7.3.33\php.exe

cd ..
set ZPATH=%CD%

echo passage du tour %mydate% ...
%REP_PHP% -f %ZPATH%\crons\cron.php >> %ZPATH%\logs\crons\out\out_%mydate%.log

cd scripts