# Event Pager Webservice

A webservice exposing a webbased GUI and API to dispatch messages to various transport including IntelPage hardware.

## Development

To contribute code to this project, please clone or fork the repository. In addition, please activate MFA for your GitHub account and configure code signing.

After cloning the project, you may start the docker compose file in the project root directory to start the service. Please use `docker exec php bash` to enter the docker container. All dependencies should have been installed through composer during the intial build of the container.

To run our test suites, you may execute one of the folloing commands in the `/webservice` folder (`/app` in the container).

Before committing ensure all tests pass, your contribution is covered by new tests if applicable and the following composer scripts executed without an error:

- composer run code:style:check (if error is found run `composer run code:style:fix`)
- composer run code:sa:check

### Information on the project for developers

We consider stability to be more important than new features.

This has two main consequences:
- Code must be tested thoroughly
- Code must be structured to allow for extension with minimal impact to existing behaviour

To build on a great foundation we are utilizing the Symfony framework including Doctrine as ORM and Twig for templating. For testing this project uses PHPUnit. Please check the respective documentations for details on their APIs and inner workings.

The project does deviate from the generic symfony structure of the src/ folder to support a better mdoule/domain based software architecture loosly inspired by hexagonal architecture. This means 3 layers (View, Core, Infrastructure), each with different modules to seperate domains and concerns.

#### Frontend / Web GUI

The frontend is based on TWIG templates (HTML) with AssetMapper and Symfony UX

#### Testing

All tests are inside the tests/ folder, mirroring the structure of the src/ folder. Tests use attributes to assign themselfs to a group, as follows:

- Unit Tests (group: unit) - DTOs, Entities, Services
- Integration Tests (group: integration) - Services
- Application Tests (group: application) - e.g. Controllers, Commands
- End-to-End Tests (group: webgui) - Tests using a real browser

