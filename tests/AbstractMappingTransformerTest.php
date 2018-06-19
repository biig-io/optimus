<?php

namespace Biig\Optimus\Tests;

use Biig\Optimus\Exception\OptimusException;
use PHPUnit\Framework\TestCase;
use Biig\Optimus\AbstractMappingTransformer;

class AbstractMappingTransformerTest extends TestCase
{
    public function testTransformFromTo()
    {
        $mapping = [
            'node' => [
                'from' => 'foo',
                'to' => 'bar',
            ],
        ];
        $data = [
            'foo' => 'baz',
        ];
        $expected = [
            'bar' => 'baz',
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testTransformFromToWithDepth()
    {
        $mapping = [
            'node' => [
                'from' => 'foo1.foo2',
                'to' => 'bar1.bar2.bar3',
            ],
        ];
        $data = [
            'foo1' => ['foo2' => 'foo']
        ];
        $expected = [
            'bar1' => ['bar2' => ['bar3' => 'foo']]
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testTransformToFunction()
    {
        $mapping = [
            'node' => [
                'to' => 'bar',
                'function' => [
                    'name' => 'getString',
                    'params' => ['foo']
                ]
            ],
        ];
        $data = [
            'foo' => 'baz',
        ];
        $expected = [
            'bar' => 'baz',
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testTransformFromToDefaultConstant()
    {
        $mapping = [
            'node' => [
                'from' => 'foo',
                'to' => 'bar',
                'default' => 'default',
            ],
        ];
        $data = [];
        $expected = [
            'bar' => 'default',
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testTransformFromToDefaultFunction()
    {
        $mapping = [
            'node' => [
                'to' => 'bar',
                'default' => [
                    'function' => [
                        'name' => 'getString',
                        'params' => ['foo']
                    ],
                ],
            ],
        ];
        $data = [
            'foo' => 'I am a string'
        ];
        $expected = [
            'bar' => 'I am a string',
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    /**
     * @expectedException  \Biig\Optimus\Exception\OptimusException
     * @expectedExceptionMessage Field foo required.
     */
    public function testItThrowAnException()
    {
        $mapping = [
            'node' => [
                'from' => 'foo',
                'to' => 'bar',
                'required' => true,
            ],
        ];

        $transformer = new ProxyDummyTransformer();
        $transformer->transformFromMapping($mapping, []);
    }

    public function testExistsCondition()
    {
        $mapping = [
            'node' => [
                'from' => 'foo1.foo2',
                'to' => 'bar1.bar2.bar3',
                'condition' => [
                    'exists' => 'baz'
                ]
            ],
        ];

        $data = [
            'foo1' => ['foo2' => 'foo'],
            'baz' => 'hello'
        ];

        $expected = [
            'bar1' => ['bar2' => ['bar3' => 'foo']]
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testNotValidatedCondition()
    {
        $mapping = [
            'node' => [
                'from' => 'foo1.foo2',
                'to' => 'bar1.bar2.bar3',
                'condition' => [
                    'exists' => 'baz'
                ]
            ],
        ];

        $data = [
            'foo1' => ['foo2' => 'foo']
        ];

        $expected = [];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testArrayCondition()
    {
        $mapping = [
            'node' => [
                'from' => 'foo1.foo2',
                'to' => 'bar1.bar2.bar3',
                'condition' => [
                    'exists' => [
                        'baz',
                        'boz'
                    ]
                ]
            ],
        ];

        $data = [
            'foo1' => ['foo2' => 'foo'],
            'baz' => 'hello',
            'boz' => 'bye'
        ];

        $expected = [
            'bar1' => ['bar2' => ['bar3' => 'foo']]
        ];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }

    public function testNotValidatedArrayCondition()
    {
        $mapping = [
            'node' => [
                'from' => 'foo1.foo2',
                'to' => 'bar1.bar2.bar3',
                'condition' => [
                    'exists' => [
                        'baz',
                        'boz'
                    ]
                ]
            ],
        ];

        $data = [
            'foo1' => ['foo2' => 'foo'],
            'baz' => 'hello',
        ];

        $expected = [];

        $transformer = new ProxyDummyTransformer();
        $result = $transformer->transformFromMapping($mapping, $data);

        $this->assertEquals($expected, $result);
    }
}

class ProxyDummyTransformer extends AbstractMappingTransformer
{
    public function transform(array $data) { return null; }

    public function transformFromMapping(array $mapping, array $data)
    {
        return parent::transformFromMapping($mapping, $data);
    }

    public function getString($value)
    {
        return $value;
    }
}
