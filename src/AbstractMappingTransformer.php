<?php

namespace Biig\Optimus;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class AbstractMappingTransformer
{
    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    public function __construct($accessor = null)
    {
        $this->accessor = $accessor ? $accessor : PropertyAccess::createPropertyAccessor();
    }

    /**
     * Transform the array given in parameter to an output array
     * Method goal:
     * - Load transformer config (yaml file)
     * - Transform input array to output formatted array
     *
     * @param array $data
     * @return mixed
     */
    abstract protected function transform(array $data);

    /**
     * Generate output array by given mapping & input array
     *
     * @param array $mapping The mapping to use to transform input array
     * @param array $data The input array
     * @return array The output array
     * @throws \Exception
     */
    protected function transformFromMapping(array $mapping, array $data)
    {
        $result = [];

        foreach ($mapping as $node) {
            // Only transform current node if no condition prevent it
            if ($this->areConditionsValidated($node, $data)) {
                // Get our node value
                $nodeValue = $this->getNodeValue($node, $data);

                // If we didn't get value but field was required, throw Exception
                if (isset($node['required']) && true === $node['required'] && null === $nodeValue) {
                    throw new \Exception('Field ' . $node['from'] . ' required.');
                }

                if (isset($node['dependencies'])) {
                    $dependenciesExist = true;

                    foreach ($node['dependencies'] as $dependency) {
                        if (null === $this->getValue($result, $mapping[$dependency]['to'])) {
                            $dependenciesExist = false;
                        }
                    }

                    if ($dependenciesExist && null === $nodeValue) {
                        throw new \Exception('Field ' . $node['from'] . ' required if dependencies true.');
                    }
                }

                // nodeValue was found && acceptable, add value to result array
                if (null !== $nodeValue) {
                    $this->accessor->setValue($result, $this->convertToBrackets($node['to']), $nodeValue);
                }
            }
        }

        return $result;
    }
    
    /**
     * Check if a node can be transformed
     *
     * @param array $node The node to check
     * @param array $data The input date to parse
     * @return bool True if node is transformable, False if node isn't transformable
     */
    private function areConditionsValidated(array $node, array $data)
    {
        // If node doesn't have condition, execute transformation
        if (!isset($node['condition'])) {
            return true;
        }

        // Only transform node if field exists in array to parse
        if (isset($node['condition']['exists'])
            && \is_null($this->getValue($data, $node['condition']['exists']))) {
            return false;
        }

        return true;
    }

    /**
     * Get the value related to a node
     *
     * @param array $node
     * @param array $data
     *
     * @return mixed
     */
    private function getNodeValue(array $node, array $data)
    {
        //1. Get function value if is set so
        //2. Else get raw value is no function is defined
        //3. If keyValue is null and a default value is set, return default value
        $nodeValue = null;

        if (isset($node['function'])) {
            $params = [];

            foreach ($node['function']['params'] as $param) {
                $params[] = $this->getValue($data, $param);
            }

            $nodeValue = call_user_func_array([$this, $node['function']['name']], $params);
        } else {
            if (isset($node['from'])) {
                $nodeValue = $this->getValue($data, $node['from']);
            }
        }

        if (null === $nodeValue && isset($node['default'])) {
            // If default value is an array, try to get it node value
            // Else return default value which is static
            if (\is_array($node['default'])) {
                $nodeValue = $this->getNodeValue($node['default'], $data);
            } else {
                $nodeValue = $node['default'];
            }
        }

        return $nodeValue;
    }

    /**
     * Get value set in the input array by finding them with key
     *
     * @param array $data The input array
     * @param string $key The key which is the location of the value in the input array
     *
     * @return mixed|null The related value, if value doesn't exists return null
     */
    private function getValue(array $data, $key)
    {
        return $this->accessor->getValue($data, $this->convertToBrackets($key));
    }

    /**
     * Convert path separated with dot to path separated with brackets
     * ie. path.to.convert will produce [path][to][convert]
     *
     * @param string $path The path to convert
     * @return string the converted path
     */
    private function convertToBrackets($path)
    {
        $keys = explode('.', $path);

        $bracketPath = '';

        foreach ($keys as $key) {
            $bracketPath .= '[' . $key . ']';
        }

        return $bracketPath;
    }
}
