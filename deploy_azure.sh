#!/bin/bash

# Configuration Variables
RESOURCE_GROUP="rg-vite-gourmand"
LOCATION="francecentral"
VM_NAME="vm-vite-gourmand"
IMAGE="Ubuntu2204"
ADMIN_USER="azureuser"
DNS_NAME="vite-gourmand-$RANDOM"

# 0. Generate Cloud-Init File
cat <<EOF > cloud-init.yaml
#cloud-config
package_upgrade: true
packages:
  - apache2
  - php
  - php-cli
  - php-sqlite3
  - php-xml
  - php-mbstring
  - php-curl
  - php-zip
  - php-intl
  - composer
  - git
  - npm
  - unzip
  - libapache2-mod-php
  - curl

write_files:
  - path: /etc/apache2/sites-available/vite-gourmand.conf
    content: |
      <VirtualHost *:80>
          DocumentRoot /var/www/html/public
          <Directory /var/www/html/public>
              AllowOverride All
              Require all granted
              FallbackResource /index.php
          </Directory>
          ErrorLog \${APACHE_LOG_DIR}/error.log
          CustomLog \${APACHE_LOG_DIR}/access.log combined
      </VirtualHost>

runcmd:
  - a2enmod rewrite
  - a2dissite 000-default
  - ln -s /etc/apache2/sites-available/vite-gourmand.conf /etc/apache2/sites-enabled/vite-gourmand.conf
  - systemctl restart apache2
  - cd /var/www/html
  - rm -rf *
  - echo "<?php phpinfo(); ?>" > /var/www/html/info.php
EOF

# 1. Create Resource Group
echo "Creating Resource Group: $RESOURCE_GROUP..."
az group create --name $RESOURCE_GROUP --location $LOCATION

# 2. Create Virtual Machine (with Cloud-Init)
echo "Creating VM: $VM_NAME..."
az vm create \
  --resource-group $RESOURCE_GROUP \
  --name $VM_NAME \
  --image $IMAGE \
  --admin-username $ADMIN_USER \
  --generate-ssh-keys \
  --public-ip-address-dns-name $DNS_NAME \
  --custom-data cloud-init.yaml \
  --size Standard_B1s

# 3. Open Ports
echo "Opening Ports (80, 443)..."
az vm open-port --port 80 --resource-group $RESOURCE_GROUP --name $VM_NAME --priority 100
az vm open-port --port 443 --resource-group $RESOURCE_GROUP --name $VM_NAME --priority 101

# 4. Output Connection Info
IP_ADDRESS=$(az vm show -d -g $RESOURCE_GROUP -n $VM_NAME --query publicIps -o tsv)
echo "---------------------------------------------------"
echo "VM Deployed Successfully!"
echo "Public IP: $IP_ADDRESS"
echo "DNS Name: $DNS_NAME.$LOCATION.cloudapp.azure.com"
echo "---------------------------------------------------"
echo "To connect via SSH:"
echo "ssh $ADMIN_USER@$IP_ADDRESS"
echo "---------------------------------------------------"
echo "Note: It may take a few minutes for the web server to start."
