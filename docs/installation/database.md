[Joomla! CLI](../index.md): [Installation](index.md)
# Database

During installation a database is created according to the settings in the environment definition (`environment.database.name`).

## Core Tables

It is populated with the core data from the installation package (e.g., `installation/sql/mysql/joomla.sql`).
 
## Users

By default, three users are added to the database:

* Super User (`admin:admin`)
* Manager (`manager:manager`)
* User (`user:user`)

If an admin account is specified during installation, that admin is created instead.

## Sample Data

If specified, sample data will be installed into the database as well.
