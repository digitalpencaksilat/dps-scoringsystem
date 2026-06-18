<?php

namespace Tests\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;

/**
 * Integration test untuk routing & controller response.
 * Tes bahwa route scoring utama mengembalikan response yang valid.
 *
 * @group routes
 */
final class ScoringRoutesTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    // ═══════════════════════════════════════════════════════════════════════════
    //  Halaman Publik (tanpa auth)
    // ═══════════════════════════════════════════════════════════════════════════

    public function testHomePageLoads(): void
    {
        $result = $this->controller(\App\Controllers\Pertandingan\PerangkatPertandingan::class)
            ->execute('index');

        $this->assertTrue(
            in_array($result->response()->getStatusCode(), [200, 302]),
            'Home page harus return 200 atau 302'
        );
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Semua controller harus instantiable
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * @dataProvider controllerProvider
     */
    public function testControllerCanBeInstantiated(string $class): void
    {
        $controller = new $class();
        $this->assertInstanceOf($class, $controller);
    }

    public static function controllerProvider(): array
    {
        return [
            'Juri' => ['App\Controllers\Pertandingan\Juri'],
            'KetuaPertandingan' => ['App\Controllers\Pertandingan\KetuaPertandingan'],
            'SekretarisPertandingan' => ['App\Controllers\Pertandingan\SekretarisPertandingan'],
            'Layar' => ['App\Controllers\Pertandingan\Layar'],
            'BroadcastOperator' => ['App\Controllers\Pertandingan\BroadcastOperator'],
            'Monitoring' => ['App\Controllers\Pertandingan\Monitoring'],
            'PerangkatPertandingan' => ['App\Controllers\Pertandingan\PerangkatPertandingan'],
            'Home' => ['App\Controllers\Home'],
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  Home controller via execute
    // ═══════════════════════════════════════════════════════════════════════════

    public function testHomeControllerIndex(): void
    {
        $result = $this->controller(\App\Controllers\Home::class)
            ->execute('index');

        $this->assertSame(200, $result->response()->getStatusCode());
    }
}
