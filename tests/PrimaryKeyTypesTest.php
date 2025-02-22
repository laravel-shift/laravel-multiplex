<?php

namespace Kolossal\Multiplex\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Kolossal\Multiplex\Tests\Mocks\Post;

class PrimaryKeyTypesTest extends TestCase
{
    use RefreshDatabase;

    protected function refreshDatabaseWithType(string $type): void
    {
        config()->set('multiplex.morph_type', $type);

        $this->artisan('migrate:fresh', $this->migrateFreshUsing());
        $this->useDatabase();
    }

    /**
     * @test
     *
     * @dataProvider morphTypes
     * */
    public function it_uses_the_configured_column_type(string $type, string $column_type)
    {
        $this->refreshDatabaseWithType($type);

        if (version_compare(app()->version(), '10.0.0', '>')) {
            $this->assertSame($column_type, Schema::getColumnType('meta', 'id'));
        }

        $this->assertSame($type, config('multiplex.morph_type'));

        $meta = Post::factory()->create()->saveMeta('foo', 'bar');

        if (config('multiplex.morph_type') === 'uuid') {
            $this->assertTrue(Str::isUuid($meta->id));
        } elseif (config('multiplex.morph_type') === 'ulid') {
            $this->assertTrue(Str::isUlid($meta->id));
        } else {
            $this->assertIsInt($meta->id);
        }
    }

    public static function morphTypes(): array
    {
        return [
            'integer' => ['integer', 'integer'],
            'uuid' => ['uuid', 'varchar'],
            'ulid' => ['ulid', 'varchar'],
        ];
    }
}
