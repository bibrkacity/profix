<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class ComissionTest extends TestCase
{
    public function testCanBeCreatedFromValidFile(): void
    {
        $this->assertInstanceOf(
            Comission::class,
            Comission::fromString('input.txt')
        );
    }



}