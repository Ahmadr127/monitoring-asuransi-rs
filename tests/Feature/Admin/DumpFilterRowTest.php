<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DumpFilterRowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dump_filter_row(): void
    {
        $this->loginAsAdmin();

        $html = $this->get(route('admin.users.index'))->getContent();

        // dump td of filter row
        $start = strpos($html, 'table-filter-row');
        $tbodyStart = strpos($html, '<tbody id="tableBody">');
        $end = strpos($html, 'table-row', $start);
        echo "\n=====TBDY SNIPPET=====\n".substr($html, $tbodyStart, 2600)."\n=====END=====\n";
        echo "\n=====CSS STYLE BLOCK=====\n";
        $s = strpos($html, '<style>');
        $e = strpos($html, '</style>', $s);
        echo substr($html, $s, $e - $s)."\n";
        $this->assertTrue(true);
    }
}