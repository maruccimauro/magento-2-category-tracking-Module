# Category Tracking Module

This Magento 2 module monitors and records category page views.The module is designed to help developers and store administrators gain insights into customer browsing patterns at the category level.

---

## Description

`Mauro_CategoryTracking` captures category view events and stores them in a custom database table. It maintains daily counters, updates records when a category is revisited, and includes a console command to monitor statistics in real time. The module tracks how many times each category is viewed per day and allows displaying the most and least visited categories directly from the Magento CLI.

![result](doc/result.gif)

---

## Magento Concepts Covered

- Event Observers (`catalog_controller_category_init_after`)
- Resource Models and Collections
- Custom Database Tables via `db_schema.xml`
- Custom Magento Console Command (MonitorViews)
- Dependency Injection (`di.xml`)
- Model-ResourceModel pattern
- Unique constraints and indexes in Magento schema

![result](doc/lifecyclesvg.svg)

---

## Installation

### Prerequisites

This module was developed on Magento 2.4.8.  
It is recommended to use a local Magento environment [linked to https://github.com/markshust/docker-magento], such as markshust/docker-magento, for development and testing.

### Installation Steps

Navigate to your Magento project root directory.
Create the module directory and clone the repository:

```bash
    mkdir -p app/code/Mauro/CategoryTracking
    git clone https://github.com/maruccimauro/magento-2-category-tracking-Module.git src/app/code/Mauro/CategoryTracking
```

### Enable the module

```bash
bin/magento module:enable Mauro_CategoryTracking
php bin/magento setup:upgrade
php bin/magento cache:flush
```

---

### Module Structure

```
Mauro/CategoryTracking
├── etc/
│   ├── module.xml
│   ├── db_schema.xml
│   ├── events.xml
│   └── di.xml
├── Observer/
│   └── CategoryViewObserver.php
├── Model/
│   ├── CategoryView.php
│   └── ResourceModel/
│       ├── CategoryView.php
│       └── CategoryView/Collection.php
├── Console/
│   └── Command/MonitorViews.php
└── registration.php
```

---

## Author

Mauro Marucci
