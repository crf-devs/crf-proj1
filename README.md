# Projet ResOp - Réserve Opérationnelle

![Planning mockup](https://raw.githubusercontent.com/crf-devs/resop/master/doc/img/planning-mockup.png)

# Contributing

[![pipeline status](https://gitlab.com/mRoca/resop/badges/master/pipeline.svg)](https://gitlab.com/mRoca/resop/commits/master)
[![coverage report](https://gitlab.com/mRoca/resop/badges/master/coverage.svg)](https://gitlab.com/mRoca/resop/commits/master)

## Install

This stack is powered with Docker;
If you prefer to use a virtual machine, you can follow those steps

### Requirements

* git
* make
* docker >= 18.06
* docker-compose >= 1.23

### Install

#### Linux

```bash
git clone git@github.com:crf-devs/resop.git && cd resop
make
```

#### OS X

```bash
git clone git@github.com:crf-devs/resop.git && cd resop

make pre-configure
make configure

# Caution: you need to uncomment all `:cached` lines in the `the docker-compose.override.yml` file

make all
```

### Run

After the `make` command, go to [http://resop.vcap.me:7500/](http://resop.vcap.me:7500/),
or [https://resop.vcap.me:7583/](https://resop.vcap.me:7583/) for https (self signed certificate).

If you want to run a symfony or a php command: `bin/tools <command>`, example: `bin/tools bin/console`

### Run : after a first install

You don't need to buld all the stack every time. If there is no new vendor, you can simply run:

```bash
make start
```

### Access

The project is using a Traefik proxy in order to allow access to all the HTTP services of the project. This service is listening on the 7500 port of the host.
The `*.vcap.me` domain names are binded on localhost. In order to use them offline, you only have to add a
`127.0.0.1 adminer.vcap.me resop.vcap.me traefik.vcap.me` line on your `/etc/hosts` file.

### Stack

- [http://resop.vcap.me:7500](http://resop.vcap.me:7500)
- [http://adminer.vcap.me:7500](http://adminer.vcap.me:7500)
- [http://traefik.vcap.me:7500](http://traefik.vcap.me:7500)

Caution: the traefik proxy will only serve healthy containers. The api container can be unaccessible before the first healthcheck (5s).

### HTTPS

The nginx container is available over HTTPS. This url must be used in order to use Facebook, Gmaps, camera...

- [https://resop.vcap.me:7543](https://resop.vcap.me:7543) ou [https://resop.vcap.me:7583](https://resop.vcap.me:7583)

## Before commiting

Please always run the following commands before commiting, or the CI won't be happy ;-)

```bash
make fix-cs
make test
```

Hint: you can run `make fix-cs-php` instead of `make fix-cs` if you are one of those backend devs who don't touch any css or js file.

### Tests

```bash
make test # Run all tests except coverage
make test-cs # php-cs-fixer
make test-advanced # phpstan
make test-unit # phpunit
make test-unit-coverage # phpunit + phpdbg
```

## PHP

### Tools & commands

As the php-fpm docker container doesn't contain any dev tool as composer, all dev commands must be run on the `tools` container. For example:

```bash
bin/tools composer
bin/tools bin/console cache:clear
bin/tools # to open a shell on the tools container
```

### Blackfire

In order to profile the php app with [Blackfire](https://blackfire.io/), you need to have a Blackfire account, then:
- Add your [credentials](https://blackfire.io/my/settings/credentials) in the `.env` file
- Uncomment the `blackfire` service in the `docker-compose.override.yml` file
- Uncomment the blackfire env var for the `backend_php` service in the `docker-compose.override.yml` file
- `docker-compose up -d --force-recreate backend_php blackfire`
- That's it, you can [profile](https://blackfire.io/docs/cookbooks/profiling-http) the app!

## Node

A node container is available in order to run `yarn` commands for `webpack encore`:

```bash
bin/node-tools yarn encore dev

webpack-build-dev
make webpack-watch-dev
```

## Alternative Developpement stack based on a VM

If you prefer using a virtual machine using VirtualBox, follow the following procedure. It relies on Ansible roles to provision the VM under the [Manala](http://www.manala.io/) organization.

⚠️ Windows environment is untested for this stack (main concerns are the NFS sharing);

The VM will be provisionned with
- PHP 7.4
- PostgreSQL 11.x
- Nginx
- Node 10
- ...

You can find essential configuration inside the (app.yml)[ansible/group_vars/app.yml] file.

### Requirements

- make
- [VirtualBox 5.2.4+](https://www.virtualbox.org/wiki/Downloads)
- [Vagrant 2.2.5+](https://www.vagrantup.com/downloads.html)
- [Vagrant Landrush 1.2.0+](https://github.com/vagrant-landrush/landrush)

### Installation

    $ make setup
    $ vagrant ssh
    # uncomment the alternative DATABASE_URL inside the .env file (L39) and comment the default one;
    $ make install-app

### Boot the VM

    # execute outside the VM
    $ vagrant up

### Access the VM

    # execute outside the VM
    $ vagrant ssh

### Shutdown the VM

    # execute outside the VM
    $ vagrant halt

### Access the project

- Local : http://resop.vcap.vm


