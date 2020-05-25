laravel-db-backup [![Build Status](https://travis-ci.org/EDOUNA/laravel-db-backup.svg?branch=develop)](https://travis-ci.org/EDOUNA/laravel-db-backup) ![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/EDOUNA/laravel-db-backup/badges/quality-score.png?b=develop) [![StyleCI](https://github.styleci.io/repos/266398642/shield?branch=develop)](https://github.styleci.io/repos/266398642)
==============

Personal Laravel 6.x back-up & restore tool

## Installation

1. Run the following command:

```bash
$ composer require edouna/laravel-db-backup
```

## Usage

#### Backup
Creates a dump file in `app/storage/db-backups`
This folder can be changed in `config/db-backup.php`

```sh
$ php artisan db:backup
```

#### Restore
The restore function will display a list of made back-ups and present a list to choose from.

```sh
$ php artisan db:restore
```
