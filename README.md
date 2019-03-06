# Optimus

The goal of the library is to map two arrays (in and out) with an YAML file config.

- **_This library respects [SemVer](https://semver.org/)._**

Maintainers:
- [@Awkan](https://github.com/Awkan)
- [@Valouleloup](https://github.com/Valouleloup)

## Install

You can install this library using composer with the following command: 
```bash
composer require biig/optimus
```




## Usage

To use this library, you have to create a transformer which needs to extends the `AbstractMappingTransformer` class.

This Transformer will read mapping and apply it to input data.

## Basic example

### Transformer

You first have to create your Transformer and implement `transform` method.    
Here we use `symfony/yaml ^3.4` to parse a YAML file.

```php
<?php
 
namespace Transformer;
 
use Biig\Optimus\AbstractMappingTransformer;
use Symfony\Component\Yaml\Yaml;
 
class MyTransformer extends AbstractMappingTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function transform(array $inputArray)
    {
        $config = Yaml::parseFile('/mapping.yaml');
        $result = $this->transformFromMapping($config['transformer']['mapping'], $inputArray);
        // ...
        return $result;
    }
}
```
The ` $result ` variable now contains the new array.

### Configuration

Assuming you got the PHP array `$inputArray` (input data)
```php
$inputArray = [
    'user' => [
        'civility' => 'M',
        'firstname' => 'John',
        'lastname' => 'Doe',
    ],
    'title' => 'A title',
];
```

And considering you want to transform it to the following array (expected output)
```php
$outputArray = [
    'title' => 'A title',
    'participants' => [
        0 => [
            'civility' => 'M',
            'name' => 'Doe',
        ],
    ],
];
```

You will have to implements the following YAML:

```yaml
# mapping.yaml
transformer:
    mapping:
        title:
            from: 'title'
            to: 'title'
        participants_1_civility:
            from: 'user.civility'
            to: 'participants.0.civility'
        participants_1_name:
            from: 'user.lastname'
            to: 'participants.0.name'
``` 


## Available mapping options

You can declare your node 
* `from` : string, the key path of the `input` array
* `to` : string, the key path of the `output` array
* `function` : array, the php function to use to transform the data
  * `name` : string, the function's name
  * `params` : [Optional] array, the function's parameters (key paths of the `input` array)
* `required` : boolean, if the key is `required`
* `condition` : array, the conditions that specify if the key is required or not

### Examples

#### Transform a key `a` to key `b`
```yaml
from: a
to: b
```

#### Get a key `b` by using a function
```yaml
to: b
function:
    name: getB
    params: [a]
```
*Note: Function `getB($arg1)` must be declared in your related transformer*
You can use functions to transform the input value to the expected value.
For example person civility (Mr, Mrs) to numbers (1, 2)
```php
public function getCivility($civility)
{
    $array = [
        'Mr' => 1,
        'Mrs' => 2,
    ];
    
    return $array[$civility];
}
```

#### Make a field required
Actually, if `from` value don't exist in your input array, the `to` field don't appear in the output array.    
If you want this field required, you can add `required` key.
```yaml
from: a
to: b
required: true # Default to false
```

#### Use a default value
Actually, if `from` value doesn't exist in your input array, the `to` field doesn't appear in the output array.    
If you want this field to get a default value, you can add `default` key.
```yaml
from: a
to: b
default: 1 # If key "a" don't exist, "b" value will be "1"
```

```yaml
from: a
to: b
default:
    function:
        name: getB # You can also use a function to define default value
```

#### Add a condition

If you want to transform a field A only if a field B exists.

```yaml
from: a
to: foo
condition:
    exists: b
``` 

You can put multiple fields in your condition :

```yaml
from: a
to: foo
condition:
    exists:
        - b
        - c
``` 

#### Get all errors mapping at a time

If you want to return all errors at a time you can set the next parameters in config

```yaml
# mapping.yaml
transformer:
    parameters:
      show_all_errors: true
    mapping:
        title:
            from: 'title'
            to: 'title'
        participants_1_civility:
            from: 'user.civility'
            to: 'participants.0.civility'
        participants_1_name:
            from: 'user.lastname'
            to: 'participants.0.name'
``` 


