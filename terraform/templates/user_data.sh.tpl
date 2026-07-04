#!/bin/bash
set -euo pipefail

exec > /var/log/user-data.log 2>&1
echo "=== Depi E-Commerce EC2 Bootstrap Started ==="

# ─── System packages ─────────────────────────────────────────────────────────
dnf update -y
dnf install -y docker git

systemctl enable docker
systemctl start docker
usermod -aG docker ec2-user

# ─── Docker Compose plugin ───────────────────────────────────────────────────
mkdir -p /usr/local/lib/docker/cli-plugins
curl -SL "https://github.com/docker/compose/releases/download/v2.21.0/docker-compose-linux-x86_64" \
  -o /usr/local/lib/docker/cli-plugins/docker-compose
chmod +x /usr/local/lib/docker/cli-plugins/docker-compose

# ─── Application directory ───────────────────────────────────────────────────
APP_DIR="/var/www/ecommerce"
mkdir -p "$APP_DIR"
chown ec2-user:ec2-user "$APP_DIR"

# ─── Clone repository (Ansible can take over for full config) ──────────────────
if [ ! -d "$APP_DIR/.git" ]; then
  sudo -u ec2-user git clone -b develop \
    https://github.com/YUSEF-RAMY/DevOps_Final_Project.git "$APP_DIR"
fi

# ─── Environment file for RDS + S3 ───────────────────────────────────────────
cat > "$APP_DIR/.env.docker" <<EOF
APP_NAME="Depi DevOps E-Commerce"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://${alb_dns_name}

DB_CONNECTION=mysql
DB_HOST=${db_host}
DB_PORT=3306
DB_DATABASE=${db_name}
DB_USERNAME=${db_username}
DB_PASSWORD=${db_password}

FILESYSTEM_DISK=s3
AWS_DEFAULT_REGION=${aws_region}
AWS_BUCKET=${s3_bucket}
AWS_USE_PATH_STYLE_ENDPOINT=false

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
EOF

chown ec2-user:ec2-user "$APP_DIR/.env.docker"

# ─── Start application stack ─────────────────────────────────────────────────
cd "$APP_DIR"
docker compose up -d --build || true

echo "=== Depi E-Commerce EC2 Bootstrap Completed ==="
