# Plugins SDK for PHP

## Requisitos
* PHP >= 5.6
* Composer >= 1

## Instalación
Ejecute el siguiente comando en su proyecto:
```
composer require mobbexco/php-plugins-sdk
```

## Configuración

Antes de utilizar el SDK, debe configurarlo mediante los métodos `init` de las clases `\Mobbex\Platform` y `\Mobbex\Api`. De estas se extrae mucha de la información que se utiliza luego en los módulos.

A continuación, un ejemplo de como se puede realizar esta configuración:
```php
<?php

\Mobbex\Platform::init('tests', '1.0.0', 'localhost:8000', [], [
    'api_key'      => 'zJ8LFTBX6Ba8D611e9io13fDZAwj0QmKO1Hn1yIj',
    'access_token' => 'd31f0721-2f85-44e7-bcc6-15e19d1a53cc',
    'test'         => true,
    'embed'        => false,
]);
\Mobbex\Api::init();
```

## Desarrollo
- Si clonó el repositorio mediante Git, puede utilizar los comandos `composer run-script test` o `composer test` para ejecutar las pruebas.

- Si desea realizar la instalación mediante composer y quiere obtener las pruebas de forma local, añada el parámetro `--prefer-source` al final del comando de instalación.