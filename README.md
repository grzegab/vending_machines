# Description of the task
I didn't want to overengineer things here but also wanted to show best practices. Because of that I've picked up the simplest solution 
that can be easily extended. The architecture is a mix of layers (presentation, UI, and persistence), divided into multiple dirs.

I've updated the PHP version in composer to use Symfony 7.3 packages and phpunit v.12.
If for some reason this project can't use php 8.2 symfony and phpunit should be downgraded in `composer.json`.

I've decided to use Symfony kernel to be able to automatically use DI. This way it was easier for testing and extending.

I didn't add cs-fixer nor PHPStan for this project, for this moment changes made by linters won't be visible.
I've also decided not to use deptrac for layer dependecy check,
the architecture is simple enough, and the project is tiny.

If in any future there is any other type of vending machine, it will implement an interface and use the 
appropriate strategy for returning coins.
As well, strategy can be easily extended. 
Of course those are only future assumptions, the whole app could be easily done in one file without any DI
or other sophisticated methods but just with a few functions inside one file.

## Interfaces
Original interfaces were intact to fit requirements.

## Testing
As mentioned PHPUnit v.12 was used.
To run php unit tests run `php vendor/bin/phpunit` in project root.
I've decided to make only a unit test to check logic in this project, 
no Integration tests for the console application were needed as it was quick and simple to test.

## Author
Greg, Lord of PHP Solutions, Master of Golang Services, Warden of RESTful Gates, Keeper of gRPC Streams, 
Protector of SQL Queries, Overlord of RabbitMQ Channels, Guardian of Redis Keys, Conqueror of Docker Containers, 
High Architect of Kubernetes Realms, and Whisperer to Artificial Intelligences.