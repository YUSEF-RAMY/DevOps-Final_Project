pipeline {
    agent any

    environment {
        // ─── Docker Hub Configuration ───────────────────────────────────────────
        DOCKER_HUB_USER = 'rawanfawzy05'
        IMAGE_NAME      = 'devops-final-project'
        IMAGE_TAG       = "${env.BUILD_NUMBER}"

        // ─── Production Server (ec2-app-2) ──────────────────────────────────────
        TARGET_SERVER   = '98.94.24.164'
        TARGET_USER     = 'ec2-user'
    }

    stages {
        // ════════════════════════════════════════════════════════════════════════
        // Stage 1: Checkout Code — Clone from GitHub onto the Jenkins Controller
        // ════════════════════════════════════════════════════════════════════════
        stage('Checkout Code') {
            steps {
                echo '📥 Cloning repository from GitHub...'
                checkout([
                    $class: 'GitSCM',
                    branches: [[name: '*/develop']],
                    userRemoteConfigs: [[
                        url: 'https://github.com/YUSEF-RAMY/DevOps_Final_Project.git',
                        credentialsId: 'github-depi-final-project-creds'
                    ]]
                ])
            }
        }

        // ════════════════════════════════════════════════════════════════════════
        // Stage 2: Build Docker Images — Heavy lifting on the Jenkins Controller
        // ════════════════════════════════════════════════════════════════════════
        stage('Build Docker Images') {
            steps {
                echo "🔨 Building Docker image: ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG}"
                sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ."
                sh "docker tag ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
            }
        }

        // ════════════════════════════════════════════════════════════════════════
        // Stage 3: Login & Push to Docker Hub
        // ════════════════════════════════════════════════════════════════════════
        stage('Login & Push to Docker Hub') {
            steps {
                echo '🚀 Authenticating and pushing images to Docker Hub...'
                withCredentials([usernamePassword(
                    credentialsId: 'dockerhub-creds',
                    usernameVariable: 'DOCKER_USER',
                    passwordVariable: 'DOCKER_PASS'
                )]) {
                    sh 'echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin'
                    sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG}"
                    sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                }
            }
        }

        // ════════════════════════════════════════════════════════════════════════
        // Stage 4: Deploy to Production (ec2-app-2)
        //   - Only pulls pre-built images. No --build on the production server.
        // ════════════════════════════════════════════════════════════════════════
        stage('Deploy to Production (ec2-app-2)') {
            steps {
                echo "🌐 Deploying to production server: ${TARGET_SERVER}..."
                sshagent(credentials: ['ec2-ssh-key']) {
                    sh """
                        ssh -o StrictHostKeyChecking=no ${TARGET_USER}@${TARGET_SERVER} '
                            cd /var/www/ecommerce &&
                            docker compose pull &&
                            docker compose up -d
                        '
                    """
                }
            }
        }
    }

    post {
        always {
            echo '🧹 Cleaning up workspace and local Docker images...'
            sh "docker rmi ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} || true"
            sh "docker rmi ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest || true"
            sh 'docker image prune -f || true'
            cleanWs()
        }
        success {
            echo "✅ Pipeline #${env.BUILD_NUMBER} completed successfully! Image ${DOCKER_HUB_USER}/${IMAGE_NAME}:${IMAGE_TAG} is now live on ${TARGET_SERVER}."
        }
        failure {
            echo "❌ Pipeline #${env.BUILD_NUMBER} FAILED. Check the console output above to diagnose the issue."
        }
    }
}