# ─── EC2 Application Servers ─────────────────────────────────────────────────
resource "aws_instance" "app" {
  count = var.ec2_instance_count

  ami                    = data.aws_ami.amazon_linux.id
  instance_type          = var.ec2_instance_type
  subnet_id              = aws_subnet.public[count.index % length(aws_subnet.public)].id
  vpc_security_group_ids = [aws_security_group.ec2.id]
  iam_instance_profile   = aws_iam_instance_profile.ec2_app.name
  key_name               = var.ec2_key_name != "" ? var.ec2_key_name : null

  user_data = base64encode(templatefile("${path.module}/templates/user_data.sh.tpl", {
    instance_index = count.index + 1
    alb_dns_name = aws_lb.main.dns_name
    db_host      = aws_db_instance.main.address
    db_name      = var.db_name
    db_username  = var.db_username
    db_password  = var.db_password
    s3_bucket    = aws_s3_bucket.app_storage.id
    aws_region   = var.aws_region
  }))

  root_block_device {
    volume_size = 30
    volume_type = "gp3"
    encrypted   = true
  }

  tags = {
    Name = "${local.name_prefix}-app-${count.index + 1}"
    Role = "ecommerce-web"
  }

  depends_on = [
    aws_db_instance.main,
    aws_s3_bucket.app_storage
  ]
}
