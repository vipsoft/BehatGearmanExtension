=================
Gearman-Extension
=================
`Behat <https://github.com/Behat/Behat>`_ integrated with `Gearman <http://php.net/gearman>`_ to distribute your BDD suite across multiple workers, so features are executed in parallel.

Installation
============
This extension requires:

* Behat 2.4+
* Mink 1.4+
* Gearman extension

Optional:

* `zlib <http://php.net/zlib>`_ extension - for compression

Through Composer
----------------
1. Set dependencies in your **composer.json**:

.. code-block:: js

    {
        "require": {
            ...
            "vipsoft/gearman-extension": "*"
        }
    }

2. Install/update your vendors:

.. code-block:: bash

    $ curl http://getcomposer.org/installer | php
    $ php composer.phar install

Through PHAR
------------
Download the .phar archive:

* `gearman_extension.phar <http://behat.org/downloads/gearman_extension.phar>`_

Configuration
=============
1. Activate extension in your **behat-client.yml**:

.. code-block:: yaml

    # behat-client.yml
    default:
      # ...
      extensions:
        VIPSoft\GearmanExtension\Extension:
          gearman_server:    ~
          task_name:         ~
          custom_task_names: ~
          access_token:      ~
          compression:       ~
          command_class:     VIPSoft\GearmanExtension\Console\Command\GearmanClientCommand

2. Activate extension in your **behat-worker.yml**:

.. code-block:: yaml

    # behat-worker.yml
    default:
      # ...
      formatter:
        name:    proxy
      # ...
      extensions:
        VIPSoft\GearmanExtension\Extension:
          gearman_server:    ~
          task_name:         ~
          custom_task_names: ~
          access_token:      ~
          compression:       ~
          command_class:     VIPSoft\GearmanExtension\Console\Command\GearmanWorkerCommand

Settings
--------
Obviously, the configuration settings below should be shared between the client and workers.

Configure the **gearman_server** setting to be a centralized Gearman job server.  The default is `127.0.0.1:4730` (i.e., port 4730 on the local host).

If using remote workers, make sure the Gearman job server allows connections from remote hosts.  On Ubuntu, you'll want to edit **/etc/default/gearman-job-server** as it defaults to only accepting local connections:

.. code-block:: bash

    PARAMS="--listen=127.0.0.1"

The default **task_name** is `behat`.

The default **custom_task_names** is `null`.  This is an array of allowable, feature-level tag names that will override **task name**.  Each feature-level tag corresponds to a task.

The following example shows how custom tags can be used to target specific workers (e.g., operating system and/or browser combinations):

.. code-block:: yaml

    # behat-client.yml
    default:
      # ...
      extensions:
        VIPSoft\GearmanExtension\Extension:
          # ...
          custom_task_names:
            - firefox
            - ie9

.. code-block:: yaml

    # behat-worker-1.yml
    default:
      # ...
      extensions:
        VIPSoft\GearmanExtension\Extension:
          # ...
          custom_task_names:
            - firefox

.. code-block:: yaml

    # behat-worker-2.yml
    default:
      # ...
      extensions:
        VIPSoft\GearmanExtension\Extension:
          # ...
          custom_task_names:
            - ie9

.. code-block:: gherkin

    @firefox
    Feature: A Firefox-specific test

.. code-block:: gherkin

    @firefox @ie9
    Feature: A cross-browser test


The default **access_token** is `null`.  In the case of remote workers, it is recommended that you set this to a secret value as a security precaution.

The default **compression** is `false`.

Usage
=====
After installing the extension, spin up one or more behat workers:

.. code-block:: bash

    $ php vendor/bin/behat --config behat-worker.yml


Then start up the behat client:

.. code-block:: bash

    $ php vendor/bin/behat --config behat-client.yml

Source
======
`Github <https://github.com/vipsoft/BehatGearmanExtension>`_

Copyright
=========
Copyright (c) 2012 Anthon Pang. See **LICENSE** for details.

Contributors
============
* Anthon Pang `(robocoder) <http://github.com/robocoder>`_
* `Others <https://github.com/vipsoft/BehatGearmanExtension/graphs/contributors>`_
