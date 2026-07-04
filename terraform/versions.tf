terraform {
  required_version = ">= 1.5.0"

  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.0"
    }
    random = {
      source  = "hashicorp/random"
      version = "~> 3.6"
    }
  }

  # Uncomment after creating the S3 bucket + DynamoDB table for state locking.
  # backend "s3" {
  #   bucket         = "depi-devops-terraform-state"
  #   key            = "ecommerce/production/terraform.tfstate"
  #   region         = "us-east-1"
  #   encrypt        = true
  #   dynamodb_table = "depi-devops-terraform-locks"
  # }
}
