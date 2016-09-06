# SupervisorBundle [![Build Status](https://secure.travis-ci.org/yzalis/SupervisorBundle.png)](http://travis-ci.org/yzalis/SupervisorBundle)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/1e931f6c-2098-41a8-8f6d-8570f125a1e8/small.png)](https://insight.sensiolabs.com/projects/1e931f6c-2098-41a8-8f6d-8570f125a1e8)

## About

This is the official bundle of the [Supervisor PHP library](https://github.com/yzalis/Supervisor).

## Installation

### Step 1: Install YZSupervisorBundle using [Composer](http://getcomposer.org)

Add YZSupervisorBundle in your `composer.json`:

``` json
{
    "require": {
        "yzalis/supervisor-bundle": "1.0.*@dev"
    }
}
```

Now tell composer to download the bundle by running the command:

    $ php composer.phar update yzalis/supervisor-bundle

### Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
# app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new YZ\SupervisorBundle\YZSupervisorBundle(),
        // ...
    );
}
```

### Step 3: Configure your `config.yml` file

``` php
# app/config/config.yml
yz_supervisor:
    default_environment: dev
    servers:
        prod:
            SUPERVISOR_01:
                host: 192.168.0.1
                username: guest
                password: password
                port: 9001
            SUPERVISOR_02:
                host: 192.168.0.2
                username: guest
                password: password
                port: 9001
        dev:
            locahost:
                host: 127.0.0.1
                username: guest
                password: password
                port: 9001
                groups: ['example_site']
```

The group option limits access to specific process groups. When no groups are provided, all groups are listed.

# Usage

Iterate over all supervisor servers:
``` php
$supervisorManager = $this->container->get('supervisor.manager');

foreach ($supervisorManager->getSupervisors() as $supervisor) {
    echo $supervisor->getKey();
    // ...
}
```

Retrieve a specific supervisor servers:
```
$supervisorManager = $this->container->get('supervisor.manager');

$supervisor = $supervisorManager->getSupervisorByKey('uniqueKey');

echo $supervisor->getKey();
```

# User interface

You can access to a beautiful user interface to monitor all your supervisor servers an process.

Import the routing definition in `routing.yml`:
``` yaml
# app/config/routing.yml
YZSupervisorBundle:
    resource: "@YZSupervisorBundle/Resources/config/routing.xml"
    prefix: /supervisor
```

Here is the result

![Supervisor Bundle screenshot](https://github.com/yzalis/SupervisorBundle/raw/master/Resources/doc/SupervisorBundle-1.png)

# Unit Tests

To run unit tests, you'll need a set of dependencies you can install using Composer:
```
php composer.phar install
```

Once installed, just launch the following command:
```
phpunit
```

You're done.

## Credits

* Benjamin Laugueux <benjamin@yzalis.com>
* [All contributors](https://github.com/yzalis/SupervisorBundle/contributors)

## License

Supervisor is released under the MIT License. See the bundled LICENSE file for details.
