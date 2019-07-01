# Observables de Mithril (streams), en PHP

mithril-stream facilita un estilo de desarrollo basado en el patrón Observador,
reactivo, de manera muy simple

No incluye muchos operadores, ni schedulers, al contrario busca la atomicidad
en las actualizaciones, es decir todos los dependientes son actualizados en una
sola llamada, evitando así efectos secundarios indeseables.

## API
Esto está siendo definido para llegar a una API cómoda y funcional, inicialmente
seguiremos las pruebas y API de JS hasta donde es posible con PHP. Posteriormente
iremos haciendo ajustes si es necesario.

Para referencia inicial, consulta la API de la librería inspiradora: https://mithril.js.org/stream.html

Puedes ver las pruebas para ejemplos y documentación.

### Crear un stream

```php
$stream = new Stream();
```

Con un valor inicial

```php
$stream = new Stream("hello World");
```

### Métodos estáticos (combinar, fusionar, lift)

#### combine

Integra la salida de dos o más streams, con una función propia.
El resultado es un Stream que emite cuando alguno de los streams
de origen es actualizado, y la función lo determina así (podríamos
emitir nuevos valores solo bajo ciertas condiciones por ejemplo).

```php
Stream::combine(callable $combiner, array $streams);

// combiner recibe el array de streams, y un array indicando cuales cambiaron
// TODO: definir signature del callable en PHP
```

#### merge

Uno de los métodos más simples, recibe un array de Stream y devuelve un Stream
que emite un array con todos los valores de los Streams de origen en ese momento.

```php
Stream::merge(array $streams); // returns Stream([...$streamValues])
```

#### lift

Una opción más sencilla de utilizar combine, en lugar de pasar los Stream de origen
en un array, podemos especificarlos como argumentos separados:

```php
Stream::lift(callable $combiner, Stream ...$streams);
```

#### scan

Básicamente `reduce` para streams. Acumula los resultados emitidos por otro Stream,
con la lógica de la función reductora.

#### scanMerge

Recibe un valor inicial, y un array de tuplas de valores iniciales y Streams de
callables, devuelve un stream con resultados acumulativos.

### Métodos de instancia

#### map (subscribe en otras librerías, el método fundamental)

Devuelve un nuevo Stream que recibe los valores del Stream de origen, pero aplicada
la función de mapeo. Esta puede devolver `Stream::SKIP` para descartar actualizaciones
en los Streams dependientes.

```php
$stream->map(callable $mapper);
```

#### end

Termina un Stream, desconectándolo de recibir actualizaciones de sus orígenes, así
como enviar actualizaciones a sus Stream observadores.

```php
$stream->end(true);
```

#### apply (`fantasy-land/ap`)

Una de las funciones más interesantes, recibe otro Stream como argumento, el cual **debe**
contener un `callable`, y devuelve un Stream que emite valores resultado de aplicar el
*callable* a los valores del stream original.

```php
$stream->apply(Stream $callableStream);
```

Se espera ir introduciendo nuevos operadores o facilitar un mecanismo para la extensión dinámica,
aunque básicamente muchos algoritmos pueden ser implementados a partir de map y apply.
