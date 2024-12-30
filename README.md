# whitelabelpress-wlp
CMS core plugins - everything you need to get started with WLP, DID.json, and more.

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
