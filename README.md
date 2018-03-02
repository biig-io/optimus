# Array mapping

The goal of the library is to map two arrays (in and out) with an YAML file config.

## Install

WIP, not official lib yet

```bash
composer require valouleloup/array-mapping
```

## Usage

#### Yaml Mapping

```yaml
transformer:
    mapping:
        civilite:
            from: 'demandeur.civilite'
            to: 'civ'
            function:
                name: 'buildCivilite'
                params:
                    - 'demandeur.civilite'
        nom:
            from: 'demandeur.nomUsage'
            to: 'nom'
            required: true
        prenom:
            from: 'demandeur.prenom'
            to: 'prenom'
            required: true
        dateNaissance:
            from: 'demandeur.dateNaissance'
            to: 'dnat'
            function:
                name: 'convertDate'
                params:
                    - 'demandeur.dateNaissance'
        villeNaissance:
            from: 'demandeur.naissanceVille'
            to: 'villenaiss'
``` 

* ` from ` : string, the key's path of the ` in ` array
* ` to ` : string, the key's path of the ` out ` array
* ` function ` : array, the php function to use to transform the data
  * ` name ` : string, the function's name
  * ` params ` : array, the function's parameters
* ` required ` : boolean, if the key is ` required `
* WIP ` condition ` : array, the conditions that specify if the key is required or not

#### Transformer

Your transformer needs to extends the ` MappingTransformer ` class : 

```php
<?php
 
namespace Transformer;
 
use DateTime;
use Valouleloup\ArrayMapping\AbstractMappingTransformer;
use Symfony\Component\Yaml\Yaml;
 
class MyTransformer extends AbstractMappingTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function transform(array $dataBefore)
    {
        // TODO use parseFile for SF >= 3.4
        $config = Yaml::parse(file_get_contents(__DIR__ . '/mapping.yml'));
        $result = $this->transformFromMapping($config['transformer']['mapping'], $dataBefore);
        ...
    }
    
    /**
     * @param $value
     *
     * @return string
     */
    public function convertDate($value)
    {
        $date = new DateTime($value);

        return $date->format('d/m/Y');
    }

}
```

The ` $result ` variable now contains the new array.
