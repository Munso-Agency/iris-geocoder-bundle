# iris-geocoder-bundle
Symfony  bundle to reverse geocoding  from  address to French IRIS Areas .

Based on : https://github.com/garaud/pyris writed in python.

# Installation

## Requirements

You have to install postgreSQL and PostGIS. For Debian:
````bash
    sudo apt-get install postgresql postgis
````

You have to be a PostgreSQL superuser to create the postgis extension for your database. If it's not the case, you can do:
````SQL
    su
    su - postgres
    psql DATABASENAME -c "CREATE EXTENSION postgis;"
````

Download the last available IRIS shape file at the URL : https://www.data.gouv.fr/fr/datasets/contour-des-iris-insee-tout-en-un/

## Configuration

Create a database and add a new doctrine connection in _app/config.yml_ [Symfony Documentation](http://symfony.com/doc/current/doctrine/multiple_entity_managers.html)
````yaml
doctrine:
    dbal:
        connections:
            #[...] 
            psql:
                driver: pdo_pgsql
                host:     "%psql_database_host%"
                port:     "%psql_database_port%"
                dbname:   "%psql_database_name%"
                user:     "%psql_database_user%"
                password: "%psql_database_password%"
                charset:  UTF8
````

Add 'geometry' mapping column type:
````yaml
doctrine:
    dbal:
        types:
            geometry: Jsor\Doctrine\PostGIS\Types\GeometryType
````            
Add a new doctrine entity_manager for the connection recently added:
````yaml
    orm:
        entity_managers:
            #[...]
            geocodage:
                connection: psql 
                mappings:
                    MunsoIRISGeocoderBundle: ~
                dql:
                    numeric_functions:
                        ST_MakePoint: Jsor\Doctrine\PostGIS\Functions\ST_MakePoint
                        ST_Contains: Jsor\Doctrine\PostGIS\Functions\ST_Contains
                        ST_SetSRID: Jsor\Doctrine\PostGIS\Functions\ST_SetSRID
````

### Customization

If you have already a postgresSQL connection configured on your project, you could edit entity_manager name used. 

If the columns mapped in your *.shp file does not fit with IrisItem entity, you can create your own entity by editing `munso.iris_geocoder.entity_name` parameter.
````yaml
    munso.iris_geocoder.entity_manager.name: 'geocodage'
    munso.iris_geocoder.entity_name: 'MunsoIRISGeocoderBundle:IrisItem'
````

## Import

Import your shape into postgresSQL database by running the command:

````
    php bin/console munso:iris:import-shape path/to/file.shp 
````

The .shx and .dbf files must be in the same directory than the *.shp file.
The SQL table will be truncated unless you use `--append` option.

#Usage
  ## IRIS Code by address
    
Use service `munso.iris_geocoder`:

````php
   $IrisItem = $this->get('munso.iris_geocoder')->getIRISByAddress('2b Allée Forain Francois verdier');
````

