<?php

namespace Kolossal\Multiplex\Tests;

use Illuminate\Database\Eloquent\Collection;
use Kolossal\Multiplex\DataType;
use Kolossal\Multiplex\Tests\Mocks\Post;

class DataTypeModelCollectionHandlerTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_handle_non_existing_models()
    {
        $models = Post::factory(3)->make();
        $handler = new DataType\ModelCollectionHandler;

        $this->assertFalse($handler->canHandleValue($models->first()));
        $this->assertTrue($handler->canHandleValue($models));

        $serialized = $handler->serializeValue($models);
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertInstanceOf(Collection::class, $unserialized);
        $this->assertCount(3, $unserialized);

        $unserialized->every(fn ($item) => $this->assertInstanceOf(Post::class, $item));
    }

    /**
     * @test
     */
    public function it_can_handle_existing_models()
    {
        Post::factory()->create(['title' => 'a']);
        Post::factory()->create(['title' => 'b']);
        Post::factory()->create(['title' => 'c']);

        $handler = new DataType\ModelCollectionHandler;

        $this->assertFalse($handler->canHandleValue(Post::first()));
        $this->assertTrue($handler->canHandleValue(Post::get()));

        $serialized = $handler->serializeValue(Post::get());
        $unserialized = $handler->unserializeValue($serialized);

        $this->assertInstanceOf(Collection::class, $unserialized);
        $this->assertCount(3, $unserialized);

        $this->assertEquals(
            ['a', 'b', 'c'],
            $unserialized->pluck('title')->sort()->toArray()
        );
    }
}
