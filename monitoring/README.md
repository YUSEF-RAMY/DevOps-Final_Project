# Prometheus & Grafana Monitoring — Depi E-Commerce

> **Branch:** `feature/prometheus-monitoring`  
> **Deliverable:** Project 4 — Prometheus dashboards for service monitoring

---

## Overview

This folder contains a **standalone monitoring stack** that is completely separate from the application and Terraform code. It monitors the Laravel e-commerce platform using:

| Tool | Role | Port |
|------|------|------|
| **Prometheus** | Collects and stores metrics | `9090` |
| **Grafana** | Visualizes metrics via dashboards | `3000` |
| **Node Exporter** | Host CPU, RAM, disk metrics | `9100` |
| **cAdvisor** | Docker container metrics | `8082` |
| **MySQL Exporter** | Database performance metrics | `9104` |
| **Redis Exporter** | Cache performance metrics | `9121` |
| **Blackbox Exporter** | HTTP uptime probes (storefront) | `9115` |

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    monitoring/ stack                         │
│                                                              │
│  ┌────────────┐    ┌────────────┐    ┌──────────────────┐   │
│  │ Prometheus │◄───│  Grafana   │    │  Alert Rules     │   │
│  │   :9090    │    │   :3000    │    │  (CPU, MySQL…)   │   │
│  └─────┬──────┘    └────────────┘    └──────────────────┘   │
│        │ scrapes every 15s                                   │
│        ▼                                                     │
│  ┌──────────┬──────────┬──────────┬──────────┬────────────┐ │
│  │  Node    │ cAdvisor │  MySQL   │  Redis   │  Blackbox  │ │
│  │ Exporter │          │ Exporter │ Exporter │  (HTTP)    │ │
│  └────┬─────┴────┬─────┴────┬─────┴────┬─────┴─────┬──────┘ │
│       │          │          │          │           │         │
└───────┼──────────┼──────────┼──────────┼───────────┼─────────┘
        │          │          │          │           │
        └──────────┴──────────┴──────────┴───────────┘
                          │
              depi-network (external Docker network)
                          │
        ┌─────────────────┴─────────────────┐
        │  app │ nginx │ db │ redis          │
        │  (main docker-compose.yml)          │
        └─────────────────────────────────────┘
```

---

## File Structure

```
monitoring/
├── docker-compose.yml
├── .env.example
├── prometheus/
│   ├── prometheus.yml
│   ├── blackbox.yml
│   └── rules/alerts.yml
├── grafana/
│   ├── provisioning/datasources/prometheus.yml
│   ├── provisioning/dashboards/default.yml
│   └── dashboards/
│       ├── depi-ecommerce-overview.json
│       ├── docker-containers.json
│       └── mysql-redis.json
├── k8s/monitoring-stack.yaml
└── README.md
```

---

## Quick Start (Docker)

### Step 1 — Start the e-commerce application first

```bash
docker compose up -d
```

### Step 2 — Find your Docker network name

```bash
docker network ls | findstr depi
```

### Step 3 — Configure and start monitoring

```bash
cd monitoring
cp .env.example .env
docker compose --env-file .env up -d
```

### Step 4 — Open dashboards

| URL | Credentials | Description |
|-----|-------------|-------------|
| http://localhost:3000 | `admin` / `admin` | Grafana dashboards |
| http://localhost:9090 | — | Prometheus UI |
| http://localhost:9090/alerts | — | Active alerts |

Grafana dashboards are auto-loaded into folder **Depi DevOps**.

---

## Pre-built Grafana Dashboards

1. **Depi E-Commerce — Service Overview** — uptime, CPU, memory, HTTP probes
2. **Depi E-Commerce — Docker Containers** — per-container CPU, memory, network
3. **Depi E-Commerce — MySQL & Redis** — database and cache metrics

---

## Alert Rules

| Alert | Condition | Severity |
|-------|-----------|----------|
| ServiceDown | Target unreachable 2min | critical |
| HighCpuUsage | CPU > 85% for 5min | warning |
| HighMemoryUsage | Memory > 90% for 5min | warning |
| DiskSpaceLow | Disk < 10% free | warning |
| MySQLDown | mysql_up == 0 | critical |
| RedisDown | redis_up == 0 | critical |
| StorefrontUnreachable | HTTP probe fails 2min | critical |

---

## Kubernetes Deployment

```bash
kubectl apply -f monitoring/k8s/monitoring-stack.yaml
```

| Service | NodePort |
|---------|----------|
| Prometheus | `30090` |
| Grafana | `30300` |

---

## Ansible Integration

The `ansible/roles/monitoring` role copies this folder to the server and runs `docker compose up -d`.

---

## Troubleshooting

### Exporters cannot reach MySQL/Redis

Ensure the app is running first, then verify the Docker network name in `monitoring/.env`.

### Grafana shows No data

Check Prometheus targets: http://localhost:9090/targets — all should be **UP**.
