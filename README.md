# Event Pager

This project provides a webservice exposing a webbased GUI and API to dispatch messages to various transport including IntelPage hardware. The project was created to span a text based communication network across events for distribution of critical information to members of large event teams, which can both utilise independently operated services (IntelPage analog pager) and widely availble channels (phone networks, internet). For smaller events the project can run standalone on small hardware, for large events the service can be integrated into sso/user federation and montitoring environments.

## Features (Planned)

- Hirarchical message receiver structure to align with your requirements (groups, roles, people)
- Mutli page web application to serve as a cross platform GUI
- HTTP API to send messages from third systems
- Easily extend with custom transports
- Track message status (send, received, errors)
- Local or OIDC user authentication
- First party support for IntelPage hardware pagers
- Observability through OpenTelemetry instrumentation

### Performance
The project is planned to run without issues for 500 users, of which 100 are sending 10 messages per day on average (1000 incoming messages) to avg. 5 channels (50000 outgoing messages) each.
Efficency depends highly on the efficency of your transports and its important to optimize your recipient structure for higher throughput of incoming messages.

## Repository Structure

The main service is a Symfony PHP project located in the `webservice/` folder. The folder contains a README.md providing more details on the service.
The project root includes global configuration files and Docker Compose files to run the project.

## Contributing

Please check the README.md of each component for details on how to develop. Your contributions in the form of bug reports & triaging, documentation improvements, addition of further transports, bugfixes, test cases or new features are welcome.
We recommend to start your contribution by creating an issue to allow for discussion. Smaller fixes, documentation and new test cases can be contributed through a pull request without a prior issue.

Please consider the licence of this project and the Developer Certificate of Origin (https://developercertificate.org/) for your contributions.

## Security

As the project is not production ready, please disclose vulnerabilites through GitHub issues.