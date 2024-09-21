# README

## OttWatch

OttWatch is a tool for monitoring and analyzing municipal activities in Ottawa, providing citizens with transparent access to local government operations.

## Exporting ottwatch.v1 database

```
MYUSER="root"
MYPASS="XXX"
MYSQLDUMP=" mysqldump --complete-insert --extended-insert=false -u $MYUSER --password=$MYPASS "

$MYSQLDUMP ottwatch \
  election \
  candidate \
  candidate_return \
  candidate_donation \
  > ottwatch_v1_snapshot.sql
```

## Getting Started

Follow these steps to set up and run OttWatch locally on your machine.

### Prerequisites

- Git
- GitHub account
- Docker (or Podman for alternative container runtime)

### Installation

1. Fork the repository:
   - Visit the OttWatch GitHub repository: https://github.com/original-owner/ottwatch
   - Click the "Fork" button in the top-right corner to create your own copy of the repository

2. Clone your forked repository:
   ```
   git clone https://github.com/your-username/ottwatch.git
   cd ottwatch
   ```

3. Build the Docker image:
   ```
   cd docker
   ./dev-build.sh  # Use ./pdev-build.sh if using Podman
   ```

4. Run the Docker container:
   ```
   ./dev-run.sh  # Use ./pdev-run.sh if using Podman
   ```

5. Inside the container, start MySQL and set up the database:
   ```
   /etc/init.d/mysql start
   cd ottwatch
   bin/rails db:setup
   ```

6. Start the Rails server:
   ```
   rails s
   ```

7. Access the application:
   Open your web browser and navigate to `http://localhost:33000`

## Development

For subsequent runs, you only need to execute `./dev-run.sh` (or `./pdev-run.sh`) from the `docker` directory to start the container and access the development environment.
