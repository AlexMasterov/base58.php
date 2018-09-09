<?php
declare(strict_types=1);

namespace Base58\Test;

use PHPUnit\Framework\TestCase;
use function Base58\{base58_decode, base58_encode};
use function random_bytes;

final class FunctionsTest extends TestCase
{
    /** @test */
    public function it_work_correctly(): void
    {
        $bytes = random_bytes(10);

        $encoded = base58_encode($bytes);
        $decoded = base58_decode($encoded);

        self::assertSame($bytes, $decoded);
    }
}
