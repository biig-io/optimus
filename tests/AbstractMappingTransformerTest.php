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
            'mapping' => [
                'node' => [
                    'from' => 'foo',
                    'to' => 'bar',
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'from' => 'foo1.foo2',
                    'to' => 'bar1.bar2.bar3',
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'to' => 'bar',
                    'function' => [
                        'name' => 'getString',
                        'params' => ['foo']
                    ]
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'from' => 'foo',
                    'to' => 'bar',
                    'default' => 'default',
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'to' => 'bar',
                    'default' => [
                        'function' => [
                            'name' => 'getString',
                            'params' => ['foo']
                        ],
                    ],
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'from' => 'foo',
                    'to' => 'bar',
                    'required' => true,
                ],
            ]
        ];

        $transformer = new ProxyDummyTransformer();
        $transformer->transformFromMapping($mapping, []);
    }

    public function testExistsCondition()
    {
        $mapping = [
            'mapping' => [
                'node' => [
                    'from' => 'foo1.foo2',
                    'to' => 'bar1.bar2.bar3',
                    'condition' => [
                        'exists' => 'baz'
                    ]
                ],
            ]
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
            'mapping' => [
                'node' => [
                    'from' => 'foo1.foo2',
                    'to' => 'bar1.bar2.bar3',
                    'condition' => [
                        'exists' => 'baz'
                    ]
                ],
            ]
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
            'mapping' => [
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
            ]
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
            'mapping' => [
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
            ]
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

    /**
     * @expectedException  \Biig\Optimus\Exception\JsonOptimusException
     * @expectedExceptionMessage ["Field foo required.","Field bar required."]
     */
    public function testItThrowAnJsonOptimusException()
    {
        $mapping = [
            'parameters' => [
                'show_all_errors' => true
            ],
            'mapping' => [
                'node' => [
                    'from' => 'foo',
                    'to' => 'bar',
                    'required' => true,
                ],
                'node2' => [
                    'from' => 'bar',
                    'to' => 'foo',
                    'required' => true,
                ],
            ]
        ];

        $transformer = new ProxyDummyTransformer();
        $transformer->transformFromMapping($mapping, []);
    }

    /**
     * @expectedException  \Biig\Optimus\Exception\OptimusException
     * @expectedExceptionMessage mapping key is missing.
     */
    public function testItThrowAnOptimusExceptionOnMissingMappingKey()
    {
        $mapping = [
            'node' => [
                'from' => 'foo',
                'to' => 'bar',
                'required' => true,
            ]
        ];

        $transformer = new ProxyDummyTransformer();
        $transformer->transformFromMapping($mapping, []);
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
