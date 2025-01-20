# whitelabelpress-wlp
CMS core plugins for CP/WP/WLP/other compatible ecosystems - everything you need to get started with WLP, DID.json, and more.

## Install
Clone WLP into your public directory (ex. /var/www), then clone this directory into `./wlp-core-plugins` to ensure proper integration. Make sure you use a (local) DOMAIN (ex. /etc/hosts define wlp1.local and in /etc/apache2/apache2.conf link the directory). 

```bash
# Clone the main WLP repository
git clone https://github.com/wlp-builders/wlp wlp1.local

# Navigate into the WLP directory
cd wlp1.local

# Create the core plugins directory if it doesn't already exist
mkdir -p wlp-core-plugins

# Clone the core plugins into the appropriate directory
git clone https://github.com/wlp-builders/whitelabelpress-wlp wlp-core-plugins
```


### Install help for Apache2
One of the simplest way to add a (local) domain is to add a Virtual Host inside the apache2.conf and then reload. 
```
APACHE_CONF="/etc/apache2/apache2.conf"
DOMAIN_NAME="wlp1.local"
FOLDER_PATH="/var/www/wlp1.local"

# Append virtual host configuration to apache2.conf
sudo tee -a $APACHE_CONF > /dev/null <<EOF 
<VirtualHost *:80>
    DocumentRoot $FOLDER_PATH
    ServerName $DOMAIN_NAME

    <Directory $FOLDER_PATH>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
EOF
sudo systemctl reload apache2
echo "Added virtual host for $DOMAIN_NAME to $APACHE_CONF"
```

### Install help hosts file
Add the following line to your /etc/hosts file:
```
127.0.0.1 wlp1.local
```

### Install help for New Container Hosting Partners
See <a href="https://github.com/wlp-builders/hosting-cli">https://github.com/wlp-builders/hosting-cli</a>

### Install help for Windows

You can definitely run this on Windows. However most support will be for Linux (to encourage people to start new hosting companies with VPS/Docker/Podman). Here's a concise guide to start using Linux for more (development) freedom:

1. **Download Ubuntu 24.04 ISO**:
   - Obtain the latest Ubuntu 24.04 LTS ISO from the official website: [Download Ubuntu](https://ubuntu.com/download/desktop).

2. **Prepare a USB Flash Drive**:
   - Ensure you have an empty USB flash drive with at least 8 GB capacity.

3. **Create a Bootable USB**:
   - **Using Rufus on Windows**:
     - Download and install Rufus: [Rufus Download](https://rufus.ie/).
     - Open Rufus, select your USB drive, choose the downloaded Ubuntu ISO, and click 'Start' to create the bootable USB.

4. **Configure BIOS/UEFI Settings**:
   - Restart your computer and access the BIOS/UEFI settings (commonly by pressing F2, F10, F12, or Del during startup).
   - Disable Secure Boot if enabled.
   - Set the USB drive as the primary boot device.

5. **Boot from USB**:
   - Insert the bootable USB and restart your computer.
   - The system should boot into the Ubuntu live environment.

6. **Start Ubuntu Installation**:
   - In the live environment, double-click 'Install Ubuntu'.
   - Follow the on-screen instructions
   - Enjoy more (development) freedom

 
### License
Main license is Spirit of Time 1.0. This is basically MIT with restricted usage for some already big established players/competitors specified by name. 
- The core plugins that use mostly core code/require core files are licensed GPL, as they should.
- Core plugins that contain only CP/WP/Other COMPATIBLE PHP code are licensed AGPL (no strict requires to core files).
- Everything else falls under the Spirit of Time 1.0 (MIT like) license.
