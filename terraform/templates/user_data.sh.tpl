#!/bin/bash
set -euo pipefail

exec > /var/log/user-data.log 2>&1
echo "=== Depi E-Commerce EC2 Bootstrap Started ==="

# ─── Set distinct Hostname ───────────────────────────────────────────────────
hostnamectl set-hostname app-server-${instance_index}
echo "127.0.0.1 app-server-${instance_index}" >> /etc/hosts


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

# ─── Setup 4GB Swap (Essential for t3.micro) ───────────────────────────────
if [ ! -f /swapfile ]; then
  echo "Creating 4GB swap space..."
  fallocate -l 4G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

# ─── Application directory ───────────────────────────────────────────────────
APP_DIR="/var/www/ecommerce"
mkdir -p "$APP_DIR"
chown ec2-user:ec2-user "$APP_DIR"

# ─── Clone repository ───────────────────────────────────────────────────────
if [ ! -d "$APP_DIR/.git" ]; then
  sudo -u ec2-user git clone -b develop \
    https://github.com/YUSEF-RAMY/DevOps_Final_Project.git "$APP_DIR"
fi

APP_KEY="base64:$(openssl rand -base64 32)"

# ─── Environment file for RDS + S3 ───────────────────────────────────────────
cat > "$APP_DIR/.env.docker" <<EOF
APP_NAME="Depi DevOps E-Commerce"
APP_ENV=production
APP_DEBUG=true
APP_URL=http://${alb_dns_name}

APP_KEY=$APP_KEY


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

REDIS_HOST=redis
REDIS_PORT=6379
EOF

chown ec2-user:ec2-user "$APP_DIR/.env.docker"

# ─── Create Production Docker Compose (uses RDS, no local MySQL) ─────────────
cat > "$APP_DIR/docker-compose.production.yml" <<'DEOF'
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: depi-devops-app
    restart: unless-stopped
    env_file:
      - .env.docker
    volumes:
      - app_public:/var/www/html/public
      - app_storage:/var/www/html/storage/app/public
    networks:
      - depi-network
    depends_on:
      redis:
        condition: service_started

  nginx:
    image: nginx:alpine
    container_name: depi-devops-nginx
    restart: unless-stopped
    ports:
      - "8080:80"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - app_public:/var/www/html/public:ro
    networks:
      - depi-network
    depends_on:
      - app

  redis:
    image: redis:alpine
    container_name: depi-devops-redis
    restart: unless-stopped
    networks:
      - depi-network

volumes:
  app_storage:
    driver: local
  app_public:
    driver: local

networks:
  depi-network:
    driver: bridge
DEOF

chown ec2-user:ec2-user "$APP_DIR/docker-compose.production.yml"

# ─── Start application stack ─────────────────────────────────────────────────
cd "$APP_DIR"

# Prevent container crash if RDS is already seeded
sed -i 's/php artisan db:seed --force/php artisan db:seed --force || true/' docker/entrypoint.sh

docker compose -f docker-compose.production.yml up -d --build || true

echo "=== Depi E-Commerce EC2 Bootstrap Completed ==="
