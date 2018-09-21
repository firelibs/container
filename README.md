FiReLibs\Container
==================

A small Dependency Injection Container for modern PHP development.
There are many container implementations on the market but none of them provides
a decent code completion capabilities. This one is designed with code-completion
in mind to minimize learning curve and reduce number of misspelled property names.


Installation
------------

Before using container in your project, add it to your ``composer.json`` file:

.. code-block:: bash

    $ ./composer.phar require firelibs\container
    

Usage
-----

Creating a container is a matter of extending a ``Container`` instance:

.. code-block:: php

    use FiReLibs\Container\Container;
    class MyContainer extends Container{
    }


Dependency injection container manages three different types of data  
**options**, **services**, **factories** and provides two types of method
names recognition to simplify **initialization** and **configuration**.

Configuration classes
---------------------

To provide code completion for almost every aspect of container-based application 
there is AbstractContainerOptions class that helps to build a nice objects with properly
described properties. Most of the services will require some configuration options and 
this is the easiest way to provide them. 

.. code-block:: php
 
        use FiReLibs\Container\AbstractContainerOptions;
        
        class EmailOptions extends AbstractContainerOptions {
            public $engine = 'smtp'
            public $host = 'localhost';
            public $port = 5553;
            public $user = 'user';
            public $password = 'password';
            public $mailFromAddress = 'admin@test.pl'
            public $mailFromName = 'Admin Test'
            public function getDns(){
                return $this->engine . ':' . $this->host . ':' . $this->port;
            }
        }

To make sure a single instance of a class is used across application a ``factory()``
method should be used to create it.

.. code-block:: php
        
        use FiReLibs\Container\Container;
        
        $container = new Container();
        $options = EmailOptions::factory($container);
        // code completion is available for $options.

Access to all properties is public so setting and getting values is very simple:

.. code-block:: php
        
        $options = EmailOptions::factory($container);
        $options->password = "secretPassword";
        $db = new Mailer($options->getDns(), $options->user, $options->password);

For security reasons it might be required to make the configuration options read-only.
For this purpose simply use ``protect()`` method.

.. code-block:: php
        
        $options = EmailOptions::factory($container);
        $options->password = "secretPassword";
        $options = $options->protect();
        
        // From now on all properties are read-only and any modification
        // will trigger a RuntimeException:
        
        $options2 = EmailOptions::factory($container);
        $options2->password = ""; // this will trigger a RuntimeException
        $options->password = "";  // and so is this

Configuration classes can import data from array and export data to it.
Be default a root key of array is a full class name with namespace and
all public properties are array members:

.. code-block:: php
        
        $options = EmailOptions::factory($container);
        print_r($options->toArray());
        
        // Will ouptput:
        // [
        //     "EmailOptions" => [
        //          "engine" => 'smtp',
        //          "host" => 'localhost',
        //          "port" => 5553,
        //          "user" => 'user',
        //          "password" => 'password',
        //          "mailFromAddress" => 'admin@test.pl',
                    "mailFromName" => 'Admin Test'
        //     ]
        // ]
        
        $newData = [
            "EmailOptions" => [
                "user" => 'new_user',
                "password" => 'new_assword'
            ]
        ];
        $options->fromArray($newData);
        
        echo ($options->user);          // will print new_user
        echo ($options->password);      // will print new_password

Automatic configuration is also possible simply by providing a configuration array
to a container constructor:

.. code-block:: php
        
        use FiReLibs\Container\Container;
        
        $container = new Container([
           "EmailOptions" => [
               "user" => 'new_user',
               "password" => 'new_assword'
           ]
        ]);
        
        $options = EmailOptions::factory($container);
        echo($options->user);       // new_user
        echo($options->password);   // new_password

 
Defining Services
-----------------

A service is an object that does something as part of a larger system. Examples
of services: a database connection, a templating engine, or a mailer. Almost
any **global** object can be a service.

Services are defined by **anonymous functions** that return an instance of an
object:

.. code-block:: php

        use FiReLibs\Container\Container;
        class MyContainer extends Container{
            /**
             * Container constructor.
             *
             * @param array $configs
             * @param null|array $initMethods Allows to filter initialisation methods.
             * @param null|array $optionMethods Allows to filter configuration methods.
             */
            public function __construct($configs = [], $initMethods = null, $optionMethods = null){
                
                // define a mailer service:
                $this->service(Mailer::class, function (ContainerInterface $container, $key){
                    $config = $this->mailerProviderOptions();
                    return new Mailer($config->getDns(), $config->user, $config->password);
                });
                
                parent::__construct($config, $initMethods, $optionMethods);
            }
            
            /**
             * Method names ending with ``ProviderOptions`` are used
             * to generate configuration template. And they allways need to
             * return ContainerOptionsInterface instance.
             *
             * @return EmailOptions
             */
            protected function mailerProviderOptions(){
                /**
                 * @var ContainerInterface $this
                 */
                return EmailOptions::factory($this);
            }

            /**
             * Retrieves mailer service in lazy mode.
             *
             * @return Mailer
             */
            public function getMailer(){
                /**
                 * @var ContainerInterface $this
                 */
                return $this->service(Mailer::class);
            }
        }

Notice that the anonymous function has access to the current container
instance, allowing references to other services or parameters.

As objects are only created when you get them, the order of the definitions
does not matter.

Using the defined services is also very easy:

.. code-block:: php

    // get the session object
    $db = $container->getMailer();
    // inteligent editros will know right away that we are dealing with ``Mailer`` class.
    

Defining Factory Services
-------------------------

Each time you get a service, container returns the **same instance**
of it. If you want a different instance to be returned for all calls, a factory service should be
defined. So in previous paragraph we defined a mailer service used to send messages now let's
create a factory that will generate new instance of a EmailMessage class.

.. code-block:: php


    use FiReLibs\Container\Container;
    class MyContainer extends Container{
        /**
         * Container constructor.
         *
         * @param array $configs
         * @param null|array $initMethods Allows to filter initialisation methods.
         * @param null|array $optionMethods Allows to filter configuration methods.
         */
        public function __construct($configs = [], $initMethods = null, $optionMethods = null){
            
            // define a mailer service:
            $this->service(Mailer::class, function (ContainerInterface $container, $key){
                $config = $this->mailerProviderOptions();
                return new Mailer($config->getDns(), $config->user, $config->password);
            });
            
            // define a message factory:
            $this->factory(EmailMessage::class, function (ContainerInterface $container, $key){
                $config = $this->mailerProviderOptions();
                // new message is already assigned to a mailer instance.
                $message = new EmailMessage($container->getMailer());
                $message->setFrom($config->mailFromAddress);
                return $message;
            });
            
            parent::__construct($config, $initMethods, $optionMethods);
        }
        
        /**
         * Method names ending with ``ProviderOptions`` are used
         * to generate configuration template. And they allways need to
         * return ContainerOptionsInterface instance.
         *
         * @return EmailOptions
         */
        protected function mailerProviderOptions(){
            return EmailOptions::factory($this);
        }

        /**
         * Retrieves mailer service in lazy mode.
         *
         * @return Mailer
         */
        public function getMailer(){
            return $this->service(Mailer::class);
        }
        /**
         * Create new message each time.
         *
         * @return EmailMessage
         */
        public function createEmailMessage(){
            return $this->factory(EmailMessage::class);
        }
        
        
    }

Now, each call to ``$container->createEmail()`` returns a new instance of the
``EmailMessage`` class with the same instance of a ``Mailer`` class attached.

Automatic methods
-----------------
Once container instance is created all methods with names ending with ``ProviderInit`` suffix
will be executed to define required components. Method names ending with ``ProviderOptions`` suffix
are executed on demand. When ``generateConfig()`` method is invoked - all configurations method are
called to generate a complete configuration template. This behavior can be overwritten by
manually setting container parameters:

.. code-block:: php
        
        use FiReLibs\Container\Container;
        
        $container = new Container([], ["mailerProviderInit"], ["mailerProviderOptions"]);
        // only mailerProviderInit method will be invoked and only mailerProviderOptions 
        // class will be used to generate a configuration template.

Providing this parameters could also be useful for optimisation purpose as this will
prevent looping through all class method names and checking for suffix. Granted it's not much
but if we are fighting for every nanosecond for some very fast API and container has many methods
it might just make a difference.
  

Extending a Container
---------------------

If you use the same libraries over and over, you might want to reuse some
services from one project to the next one; package your services into a **provider** 
by creating a ServiceProviderTrait. If ``Automatic methods`` chapter of this document
was skipped now will be a good time to read it as it is a base of how Providers works.
Let's move previous example of mailing system to a provider trait:
  

.. code-block:: php

    use FiReLibs\Container\ContainerInterface;

    trait MailingServiceProvider
    {
        /**
         * Method names ending with ``ProviderOptions`` are used
         * to generate configuration template. And they allways need to
         * return ContainerOptionsInterface instance.
         *
         * @return EmailOptions
         */
        protected function mailerProviderOptions(){
            /**
             * @var ContainerInterface $this
             */
            return EmailOptions::factory($this);
        }
        
        /**
         * Method names ending with ``ProviderInit`` are used
         * to define services once container is created.
         */
        protected function mailerProviderInit(){
            // define a mailer service:
            $this->service(Mailer::class, function (ContainerInterface $container, $key){
                $config = $this->mailerProviderOptions();
                return new Mailer($config->getDns(), $config->user, $config->password);
            });
            
            // define a message factory:
            $this->factory(EmailMessage::class, function (ContainerInterface $container, $key){
                $config = $this->mailerProviderOptions();
                // new message is already assigned to a mailer instance.
                $message = new EmailMessage($container->getMailer());
                $message->setFrom($config->mailFromAddress);
                return $message;
            });
            
            /**
             * Retrieves mailer service in lazy mode.
             *
             * @return Mailer
             */
            public function getMailer(){
                return $this->service(Mailer::class);
            }
            
            /**
             * Create new message each time.
             *
             * @return EmailMessage
             */
            public function createEmailMessage(){
                return $this->factory(EmailMessage::class);
            }
        }
    }

Then, simply use that trait in container:

.. code-block:: php

    use FiReLibs\Container\Container;
    
    class MyContainer extends Container{
        use MailingServiceProvider;
    }

Modifying Services after Definition
-----------------------------------

In some cases you may want to modify a service definition after it has been
defined. You can use the ``extend()`` method to define additional code to be
run on your service just after it is created:

.. code-block:: php


    use FiReLibs\Container\Container;
    class MyContainer extends Container{
        use MailingServiceProvider;
        
        /**
         * Container constructor.
         *
         * @param array $configs
         * @param null|array $initMethods Allows to filter initialisation methods.
         * @param null|array $optionMethods Allows to filter configuration methods.
         */
        public function __construct($configs = [], $initMethods = null, $optionMethods = null){
            
            // let's call original constructor to define services thyy need to be defined before
            // they can be extended.
                        
            parent::__construct($config, $initMethods, $optionMethods);
            
            // now we can extend message factory to include not only
            // address but also a name:
            
            $this->extend(EmailMessage::class, static::FACTORY, function($original, ContainerInterface $container, $key){
                
                $config = $this->mailerProviderOptions();
                // invoke original method to get a message instance:
                /**
                 * @var EmailMessage $message
                 */
                $message = $original($container, $key);
                
                // update message `from` field with name and address :
                $message->setFrom($config->mailFromAddress, $config->mailFromName);
                
                // return message instance
                return $message;
                
            }); 
            
        }
    }

The first argument is the name of the service to extend, the second is a type and
third one is a function that gets access to the original creation method.


Fetching the Service Creation Function
--------------------------------------

When you access an object, container automatically calls the anonymous function
that you defined, which creates the service object for you. If you want to get
raw access to this function, you can use the ``raw()`` method. Container also provides
a couple of useful functions ``has()`` to check if service is defined,
``isService()`` and ``isFactory()`` to determine the type:

.. code-block:: php

    if($container->isFactory(EmailMessage::class)){
        $callback = $container->raw(EmailMessage::class);
    }
    

