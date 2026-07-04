output "vpc_id" {
  description = "ID of the created VPC."
  value       = aws_vpc.main.id
}

output "public_subnet_ids" {
  description = "IDs of public subnets."
  value       = aws_subnet.public[*].id
}

output "private_subnet_ids" {
  description = "IDs of private subnets."
  value       = aws_subnet.private[*].id
}

output "alb_dns_name" {
  description = "DNS name of the Application Load Balancer (public app URL)."
  value       = aws_lb.main.dns_name
}

output "alb_arn" {
  description = "ARN of the Application Load Balancer."
  value       = aws_lb.main.arn
}

output "ec2_instance_ids" {
  description = "IDs of EC2 application instances."
  value       = aws_instance.app[*].id
}

output "ec2_public_ips" {
  description = "Public IP addresses of EC2 instances."
  value       = aws_instance.app[*].public_ip
}

output "rds_endpoint" {
  description = "RDS MySQL endpoint (hostname only)."
  value       = aws_db_instance.main.address
}

output "rds_port" {
  description = "RDS MySQL port."
  value       = aws_db_instance.main.port
}

output "s3_app_storage_bucket" {
  description = "S3 bucket name for Laravel file uploads."
  value       = aws_s3_bucket.app_storage.id
}

output "s3_terraform_state_bucket" {
  description = "S3 bucket name for Terraform remote state."
  value       = aws_s3_bucket.terraform_state.id
}

output "dynamodb_lock_table" {
  description = "DynamoDB table for Terraform state locking."
  value       = aws_dynamodb_table.terraform_locks.name
}

output "application_url" {
  description = "Public URL to access the e-commerce application."
  value       = "http://${aws_lb.main.dns_name}"
}

output "ansible_inventory_hint" {
  description = "Example Ansible inventory line for provisioned EC2 hosts."
  value       = join("\n", [for ip in aws_instance.app[*].public_ip : "ec2-app ansible_host=${ip} ansible_user=ec2-user"])
}
