# PUB CampusMate — Test Suite

This directory contains automated test suites for verifying application architecture, database connectivity, routing, and security.

## Directory Structure
- `Unit/`: Isolated tests for framework components, database singletons, routers, and helper functions.
- `Integration/`: Database and environment integration tests connecting to the MySQL/MariaDB database.

## Running Tests

### Option A: Using Composer (PHPUnit)
If PHPUnit is installed via Composer:
```bash
composer test
# or directly:
./vendor/bin/phpunit
```

### Option B: Running the Standalone Environment and Database Checkers
```bash
# 1. Environment & Pre-flight verification
php scripts/check_environment.php

# 2. Complete Database & Schema verification
php scripts/verify_database.php
```
