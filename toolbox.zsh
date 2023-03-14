PROJECT_ROOT='/path/to/project/root'
GCP_RESULTS_BUCKET_NAME='bucket-name'
GCP_SERVICE_ACCOUNT='some-compute@developer.gserviceaccount.com'
GCP_PROJECT_NAME='your-project-name'
GCP_ZONE='e.g.: europe-west3-c'

## Local development

function mymo-up() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo up -d
}

function mymo-down() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo down --volumes
}

function mymo-mongo-run-all() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo run --env=NETTE__TARGET=mongo client php ./bin/console run:all
}

function mymo-mysql-run-all() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo run --env=NETTE__TARGET=mysql client php ./bin/console run:all
}

function mymo-cli() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo run client php ./bin/console "${@}"
}

function mymo-client() {
  docker-compose -f $PROJECT_ROOT/.docker/docker-compose.yaml --project-name=mymo run -it client bash
}


## GCP Control commands

function setup-vm-client() {
  TARGET=$1

  gcloud compute instances create-with-container client-"$TARGET"  \
      --project="$GCP_PROJECT_NAME" \
      --zone="$GCP_ZONE" \
      --machine-type=e2-medium \
      --network-interface=network-tier=PREMIUM,subnet=default \
      --maintenance-policy=MIGRATE \
      --provisioning-model=STANDARD \
      --service-account="$GCP_SERVICE_ACCOUNT" \
      --scopes=https://www.googleapis.com/auth/cloud-platform \
      --image=projects/cos-cloud/global/images/cos-stable-101-17162-40-56 \
      --boot-disk-size=10GB \
      --boot-disk-type=pd-balanced \
      --boot-disk-device-name=mymo-client-test \
      --container-image=europe-west3-docker.pkg.dev/tu-csb-test-1/vlcek-csb-tu/mymo-client:latest \
        --container-privileged \
        --container-restart-policy=never \
        --container-env=NETTE__TARGET="$TARGET" \
      --no-shielded-secure-boot \
      --shielded-vtpm \
      --shielded-integrity-monitoring \
      --labels=container-vm=cos-stable-101-17162-40-56
}

function setup-vm-mysql() {
  gcloud compute instances create-with-container sut-mysql \
    --project="$GCP_PROJECT_NAME" \
    --zone="$GCP_ZONE" \
    --machine-type=n2-standard-2 \
    --network-interface=network-tier=PREMIUM,subnet=default \
    --maintenance-policy=MIGRATE \
    --provisioning-model=STANDARD \
    --service-account="GCP_SERVICE_ACCOUNT" \
    --scopes=https://www.googleapis.com/auth/devstorage.read_only,https://www.googleapis.com/auth/logging.write,https://www.googleapis.com/auth/monitoring.write,https://www.googleapis.com/auth/servicecontrol,https://www.googleapis.com/auth/service.management.readonly,https://www.googleapis.com/auth/trace.append \
    --image=projects/cos-cloud/global/images/cos-stable-101-17162-40-56 \
    --boot-disk-size=10GB \
    --boot-disk-type=pd-balanced \
    --boot-disk-device-name=sut-mysql \
    --container-image=mysql:8.0 \
      --container-privileged \
      --container-restart-policy=always \
      --container-env=MYSQL_ROOT_PASSWORD=password,MYSQL_USER=user \
    --no-shielded-secure-boot \
    --shielded-vtpm \
    --shielded-integrity-monitoring \
    --labels=container-vm=cos-stable-101-17162-40-56
}

function setup-vm-mongo() {
  gcloud compute instances create-with-container sut-mongo \
    --project="GCP_PROJECT_NAME" \
    --zone="$GCP_ZONE" \
    --machine-type=n2-standard-2 \
    --network-interface=network-tier=PREMIUM,subnet=default \
    --maintenance-policy=MIGRATE \
    --provisioning-model=STANDARD \
    --service-account="$GCP_SERVICE_ACCOUNT" \
    --scopes=https://www.googleapis.com/auth/devstorage.read_only,https://www.googleapis.com/auth/logging.write,https://www.googleapis.com/auth/monitoring.write,https://www.googleapis.com/auth/servicecontrol,https://www.googleapis.com/auth/service.management.readonly,https://www.googleapis.com/auth/trace.append \
    --image=projects/cos-cloud/global/images/cos-stable-101-17162-40-56 \
    --boot-disk-size=10GB \
    --boot-disk-type=pd-balanced \
    --boot-disk-device-name=sut-mongo \
    --container-image=mongo:6.0 \
      --container-privileged \
      --container-restart-policy=always \
      --container-env=MONGO_INITDB_ROOT_USERNAME=user,MONGO_INITDB_ROOT_PASSWORD=password \
    --no-shielded-secure-boot \
    --shielded-vtpm \
    --shielded-integrity-monitoring \
    --labels=container-vm=cos-stable-101-17162-40-56
}

function mymo-gcp-up() {
  setup-vm-mysql
  setup-vm-mongo
  echo 'Waiting for 10 seconds...'
  sleep 10
  setup-vm-client mongo
  setup-vm-client mysql
}

function mymo-gcp-down() {
  # Download the last two result files
  gsutil ls -l gs://"$GCP_RESULTS_BUCKET_NAME"/ | awk '{print $3,$4,$5,$8}' | sort | tail -3 | head -2 | xargs -I % gsutil cp -n % ./results/

  # Shut down all VMs
  gcloud compute instances delete sut-mongo sut-mysql client-mongo client-mysql \
      --project="$GCP_PROJECT_NAME" \
      --zone="$GCP_ZONE"
}


## Helpers

function add-firewall-rule() {
  NAME=$1
  PORT=$2

  gcloud compute --project="$GCP_PROJECT_NAME" firewall-rules create "$NAME" --direction=INGRESS --priority=1000 --network=default --action=ALLOW --rules=tcp:"$PORT" --source-ranges=0.0.0.0/0
}
