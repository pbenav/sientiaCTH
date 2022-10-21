#!/bin/bash
echo "Cambiando a la carpeta fuente..."
cd /home/dinamizador/Desarrollo/WEB/PHP/Laravel/cth
echo "Actualizando Sientia..."
rsync -aprv --exclude-from=".gitignore" --exclude=".git*/" --exclude="storage/" . sientia@sientia.com:~/domains/cth.sientia.com/cth/
echo "Actualizando Saturno..."
rsync -aprv --exclude-from=".gitignore" --exclude=".git*/" --exclude="storage/" . root@192.168.10.104:/home/raid/ayunzafa/docker/cth/
echo "Actualización finalizada."
echo -e "\n***"
echo "Fin de impresión."