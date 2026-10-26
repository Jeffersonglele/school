# Base MariaDB pour Render (Private Service + disque persistant sur /var/lib/mysql).
# Render ne propose pas de MySQL managé : on l'héberge nous-mêmes.
FROM mariadb:10.11

# Un disque Render fraîchement monté contient un dossier "lost+found" :
# on dit à MariaDB de ne pas le prendre pour une base de données.
CMD ["mariadbd", "--ignore-db-dirs=lost+found"]
