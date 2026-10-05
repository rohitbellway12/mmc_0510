#!/bin/bash
(crontab -l | grep -v "ssfssdsds /Applications/MAMP/htdocs/Demandium-Admin/artisan email:send-renewal-reminder") | crontab -
(crontab -l; echo "0 0 * * * ssfssdsds /Applications/MAMP/htdocs/Demandium-Admin/artisan email:send-renewal-reminder") | crontab -
