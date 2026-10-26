<?php

namespace App\Service\Export;

interface ExportInterface
{
    /**
     * Returns the binary content of the generated file.
     */
    public function export(): string;
}
