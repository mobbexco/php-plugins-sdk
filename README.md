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

## Integridad (attestation)

Protege contra plugins adulterados: antes de crear un checkout, el SDK pide un
*challenge* a Mobbex y adjunta al request dos valores que el servidor recomputa
contra el release publicado. Si no coinciden, el checkout no se genera.

Habilitarlo en un plugin es **una línea**, después de `\Mobbex\Platform::init()`:

```php
\Mobbex\Integrity\Attestation::init('prestashop', __DIR__);
```

- El primer argumento es el **nombre del repo del plugin en GitHub**
  (`prestashop`, `woocommerce`, `magento-2`, …). Así resuelve el servicio contra
  qué release verificar: si no coincide, la verificación falla siempre.
- El segundo es el **directorio de instalación del plugin**. Las rutas de los
  archivos que pide el servidor son relativas a él.
- La versión sale de `\Mobbex\Platform::$version` y **tiene que existir como tag
  publicado** con su asset. Un tercer argumento opcional permite forzarla.

No hay nada más que hacer: la clase se registra como *header provider* y adjunta
los `x-integrity-*` sólo a la creación de checkouts.

### URL de la página de checkout (opcional pero recomendado)

Un cuarto argumento con la URL de la página de pago permite además detectar
*skimming*, visitando el checkout como lo haría un comprador. Cada plataforma la
construye distinto, por eso el SDK la recibe en vez de calcularla.

```php
// PrestaShop
\Mobbex\Integrity\Attestation::init('prestashop', __DIR__, null, function () {
    return \Context::getContext()->link->getPageLink('order', true);
});

// WooCommerce
\Mobbex\Integrity\Attestation::init('woocommerce', WC_MOBBEX_DIR, null, 'wc_get_checkout_url');
```

Acepta un **callable** (preferible: `init()` corre en cada request, el header sólo
al crear un checkout) o un **string**. Si el callable no puede resolverla —sin
contexto, cron, back office— que devuelva `null`: cae al host de la tienda.

La URL se normaliza a esquema + host + path, sin query string ni fragmento, para
no guardar tokens de sesión o de carrito. Si no es `http`/`https`, si tiene
caracteres fuera de ASCII imprimible o si supera los 512 caracteres, **se omite el
header** en vez de mandar una versión mutilada: una URL equivocada manda al
crawler a mirar a un tercero, y eso cuesta más que no mandar nada.

### Garantías

La attestation **nunca rompe un pago**. Ante cualquier fallo — sin red, timeout,
disco lento, error interno — degrada en silencio a `x-integrity-mode: static` y
deja pasar el request. La decisión de bloquear es del servidor, nunca del plugin.

### Contrato con el servicio

El cómputo está atado a golden vectors compartidos con el servicio de
verificación (`tests/mac_vectors.json`), generados por una implementación
independiente. **No los edite sin actualizar las dos puntas:** una divergencia se
ve en producción igual que un plugin adulterado, y rompe a todos los comercios a
la vez.

```bash
php tests/mac_vectors_runner.php   # sin dependencias, corre desde PHP 5.6
composer test                      # suite completa, requiere PHP 7.3+
```

El runner existe además de PHPUnit porque los plugins corren en PHP 5.6 y en
builds de 32 bits, donde PHPUnit 9 no puede correr — y es justo ahí donde una
divergencia de enteros pasaría inadvertida.

## Desarrollo
- Si clonó el repositorio mediante Git, puede utilizar los comandos `composer run-script test` o `composer test` para ejecutar las pruebas.

- Si desea realizar la instalación mediante composer y quiere obtener las pruebas de forma local, añada el parámetro `--prefer-source` al final del comando de instalación.