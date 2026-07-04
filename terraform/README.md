# ☁️ Terraform — AWS Infrastructure for Depi E-Commerce


---

## Overview

This module provisions the **production AWS foundation** for the Laravel e-commerce platform. It implements Infrastructure as Code (IaC) using Terraform and covers all four AWS services required by the project brief:

| AWS Service | Terraform Resource | Purpose |
|-------------|-------------------|---------|
| **EC2** | `aws_instance` | Application servers running Docker + Laravel stack |
| **RDS** | `aws_db_instance` | Managed MySQL 8.0 database (`depi_devops`) |
| **S3** | `aws_s3_bucket` | Encrypted object storage for uploads + Terraform state |
| **ELB** | `aws_lb` (ALB) | Application Load Balancer distributing HTTP traffic |

---

## Architecture

```
                         Internet
                            │
                            ▼
              ┌─────────────────────────┐
              │  Application Load       │
              │  Balancer (ALB / ELB)   │
              │  Port 80 → 8081         │
              └───────────┬─────────────┘
                          │
         ┌────────────────┼────────────────┐
         ▼                ▼                ▼
   ┌──────────┐    ┌──────────┐    (scales via
   │  EC2 #1  │    │  EC2 #2  │     ec2_instance_count)
   │ Docker   │    │ Docker   │
   │ Laravel  │    │ Laravel  │
   └────┬─────┘    └────┬─────┘
        │               │
        │    ┌──────────┴──────────┐
        │    ▼                     ▼
        │  ┌────────────┐   ┌─────────────┐
        └──│ RDS MySQL  │   │  S3 Bucket  │
           │ (private)  │   │  (storage)  │
           └────────────┘   └─────────────┘

VPC 10.0.0.0/16
├── Public subnets  → ALB + EC2
└── Private subnets → RDS only
```

---

## File Structure

```
terraform/
├── versions.tf              # Terraform & provider version constraints
├── providers.tf             # AWS provider + default tags
├── variables.tf             # Input variables
├── locals.tf                # Computed naming conventions
├── data.tf                  # AMI lookup, availability zones
├── vpc.tf                   # VPC, subnets, route tables, IGW
├── security_groups.tf       # ALB, EC2, RDS security groups
├── iam.tf                   # EC2 IAM role for S3 access
├── s3.tf                    # App storage + Terraform state buckets
├── rds.tf                   # MySQL RDS instance
├── ec2.tf                   # Application EC2 instances
├── alb.tf                   # Application Load Balancer (ELB)
├── outputs.tf               # Endpoints for Ansible / CI/CD
├── templates/
│   └── user_data.sh.tpl     # EC2 bootstrap script
├── terraform.tfvars.example # Example variable overrides
├── .gitignore               # Excludes state files & secrets
└── README.md                # This file
```

---

## Prerequisites

1. [Terraform](https://developer.hashicorp.com/terraform/install) >= 1.5
2. [AWS CLI](https://aws.amazon.com/cli/) configured with credentials
3. An AWS account with permissions for EC2, RDS, S3, ELB, VPC, IAM

```bash
aws configure
# Enter: Access Key, Secret Key, Region (us-east-1), output format (json)
```

---

## Quick Start

```bash
cd terraform

# 1. Copy and edit variables (set db_password!)
cp terraform.tfvars.example terraform.tfvars

# 2. Initialize Terraform (downloads providers)
terraform init

# 3. Preview changes
terraform plan

# 4. Provision infrastructure
terraform apply

# 5. View outputs (ALB URL, RDS endpoint, S3 bucket)
terraform output
```

After `apply`, open the **Application URL**:

```bash
terraform output -raw application_url
```

---

## Integration with Other Project Components

| Component | How Terraform connects |
|-----------|----------------------|
| **Docker** | EC2 `user_data` installs Docker and runs `docker compose up` |
| **Ansible** | Use `terraform output ansible_inventory_hint` to populate `ansible/inventory.ini` |
| **Jenkins** | Pipeline can run `terraform apply` before K8s/Docker deploy |
| **Laravel** | RDS credentials injected into `.env.docker` on EC2 bootstrap |

---

## Security Highlights

- RDS in **private subnets** — not publicly accessible
- Security groups follow **least-privilege** (ALB → EC2 → RDS only)
- S3 buckets: **encryption at rest**, versioning, public access blocked
- EC2 root volumes: **encrypted gp3**
- IAM role grants EC2 **scoped S3 access** only
- `terraform.tfvars` is **gitignored** — secrets never committed

---

## Remote State (Optional)

After the first `apply`, enable remote state by uncommenting the `backend "s3"` block in `versions.tf` and using the output buckets:

```hcl
backend "s3" {
  bucket         = "<output: s3_terraform_state_bucket>"
  key            = "ecommerce/production/terraform.tfstate"
  region         = "us-east-1"
  encrypt        = true
  dynamodb_table = "<output: dynamodb_lock_table>"
}
```

Then run `terraform init -migrate-state`.

---

## Destroy

```bash
terraform destroy
```

> **Warning:** This deletes all provisioned AWS resources including RDS data.

---

