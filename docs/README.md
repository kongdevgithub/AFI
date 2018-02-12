# Documentation Outline

The purpose of this document is to provide an understanding of how to deploy and maintain a development copy of the software, as well as an insight into the workings of the system.

## What is this system?

Product configurator:

- admin can configure product templates to be used in quotes
- product templates define which options and components will be used in items within a product

Quoting system:

- sales rep enters job - which is a list of products, each one with options like size and material
- products contain items, items contain components
- quoting system will give a price for the components

Manufacturing system:

- each job has multiple products
- products are made from multiple items
- each item goes through a manufacturing process (using a status field in the database)



## Access

Login details for all technical resources are in the [AFI Branding Passwords File](https://docs.google.com/spreadsheets/d/0Av2J6-jZFt5odHlsSG9fM1ZILUFkeUdtZ0pmUGg3a1E).

Most services were setup using the email `webmaster@afibranding.com.au` - contact Richard [richard@afibranding.com.au](mailto:richard@afibranding.com.au) to get access to this account if needed.


## Backups

The server is backed up daily with several copies stored locally, plus weekly remote archives stored for 1 year.

See the [Backup](backup.md) documentation for more detailed information.


## Server

Setup the server using the [Docker](docker.md) guide.

The following are guides and information for configuring and managing the live server:

* [Deployment](deployment.md)
* [Security](security.md)
* [Commands](commands.md)


## Source Code

The application is built using the [Yii2 Framework](http://www.yiiframework.com/doc-2.0/guide-index.html).

The source code is version controlled using Git and is currently hosted on [bitbucket.org](https://bitbucket.org/afibranding/console/). See the [Git](git.md) guide for more information.
