#!/bin/bash

# ============================================================
# Automated Rollback Script 
# This script validates the deployment and triggers a rollback if health checks fail.
# ============================================================

DEPLOYMENT_NAME="laravel-app-deployment"
NAMESPACE="depi-production"
MAX_RETRIES=5
RETRY_INTERVAL=10

echo " Starting Post-Deployment Health Check for $DEPLOYMENT_NAME..."

# 1. Wait for the rollout to finish
echo " Waiting for deployment rollout to finish..."
kubectl rollout status deployment/$DEPLOYMENT_NAME -n $NAMESPACE --timeout=60s

if [ $? -ne 0 ]; then
    echo "Deployment Failed! Initiating immediate rollback..."
    kubectl rollout undo deployment/$DEPLOYMENT_NAME -n $NAMESPACE
    exit 1
fi

# 2. Application Level Check (HTTP Check)
echo " Performing Application-Level Health Check..."
# We try to hit the service and check if it returns 200 OK
STATUS_CODE=$(kubectl exec -it -n $NAMESPACE deployments/$DEPLOYMENT_NAME -c nginx -- curl -o /dev/null -s -w "%{http_code}" http://localhost/login)

if [ "$STATUS_CODE" -ne 200 ]; then
    echo " Application is returning $STATUS_CODE instead of 200 OK."
    echo " Triggering Rollback to the last stable version..."
    kubectl rollout undo deployment/$DEPLOYMENT_NAME -n $NAMESPACE
    echo " Rollback completed successfully."
    exit 1
else
    echo " Health check passed! Application is stable."
    exit 0
fi