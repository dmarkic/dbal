# Async database abstraction layer

This project provides DBAL for [blrf/orm](https://blrf.net/orm) to use with any [ReactPHP](https://reactphp.org/) project.
Full example project using [Framework X](https://framework-x.org/) is available on [Orm bookstore example](https://github.com/dmarkic/orm-bookstore-example) GitHub repository.

## Install

Use [Composer](https://getcomposer.org/) to install `blrf/dbal` package.

```
composer install blrf/dbal
```

See [Quickstart](quickstart/index.md) for quick example.

## Drivers

Currently these drivers are directly supported:

- MySql: using [ReactPHP Mysql](https://github.com/friends-of-reactphp/mysql/)

See [Config](api/config.md) on how to use the drivers.