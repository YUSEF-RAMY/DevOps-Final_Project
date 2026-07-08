variable "aws_region" {
  description = "AWS region for all resources."
  type        = string
  default     = "us-east-1"
}

variable "project_name" {
  description = "Short project name used in resource naming."
  type        = string
  default     = "depi-ecommerce"
}

variable "environment" {
  description = "Deployment environment label."
  type        = string
  default     = "production"
}

variable "vpc_cidr" {
  description = "CIDR block for the VPC."
  type        = string
  default     = "10.0.0.0/16"
}

variable "public_subnet_cidrs" {
  description = "Public subnet CIDR blocks (ALB + EC2)."
  type        = list(string)
  default     = ["10.0.1.0/24", "10.0.2.0/24"]
}

variable "private_subnet_cidrs" {
  description = "Private subnet CIDR blocks (RDS)."
  type        = list(string)
  default     = ["10.0.10.0/24", "10.0.20.0/24"]
}

variable "ec2_instance_type" {
  description = "EC2 instance type for the application server."
  type        = string
  default     = "t3.small"
}

variable "ec2_instance_count" {
  description = "Number of EC2 instances behind the load balancer."
  type        = number
  default     = 2
}

variable "ec2_key_name" {
  description = "Existing AWS EC2 key pair name for SSH access."
  type        = string
  default     = "yusef_new_key"
}

variable "allowed_ssh_cidr" {
  description = "CIDR allowed to SSH into EC2 instances."
  type        = string
  default     = "0.0.0.0/0"
}

variable "db_name" {
  description = "MySQL database name (matches Laravel .env.docker)."
  type        = string
  default     = "depi_devops"
}

variable "db_username" {
  description = "MySQL master username."
  type        = string
  default     = "yusef"
}

variable "db_password" {
  description = "MySQL master password. Override via terraform.tfvars (never commit secrets)."
  type        = string
  sensitive   = true
}

variable "db_instance_class" {
  description = "RDS instance class."
  type        = string
  default     = "db.t3.micro"
}

variable "db_allocated_storage" {
  description = "RDS storage in GB."
  type        = number
  default     = 20
}

variable "s3_force_destroy" {
  description = "Allow Terraform to delete the S3 bucket even if it contains objects."
  type        = bool
  default     = false
}

variable "health_check_path" {
  description = "ALB health check path."
  type        = string
  default     = "/"
}
