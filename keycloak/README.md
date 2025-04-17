# Keycloak 

## Service

The Keycloak service is preconfigured in compose.yaml. On startup the exported REALM located under keycloak/config/DEV_BuLa_SanSi-realm.json is automatically loaded.

You can open the keycloak administration interface under http://localhost:7080/

Default login credentials:

    username: admin
    password: admin

## Configuration

The REALM configuration can be customised in the administration interface. To save this, the Keycloak service must first be stopped. The configuration can then be exported with the following command.

    docker compose run --rm keycloak export --dir /opt/keycloak/data/import --users realm_file --realm DEV_BuLa_SanSi

The exported configuration can now be pushed into the GIT and is thus also available to others.

