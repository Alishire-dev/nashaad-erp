<?php

if (! function_exists('esc_pdf')) {
    /**
     * FPDF's core fonts only support Windows-1252 — item names, supplier/
     * customer names etc. may contain UTF-8. Converts safely, falling back
     * to stripping anything unconvertible rather than letting FPDF throw
     * or emit garbage bytes.
     */
    function esc_pdf(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $text);
        return $converted !== false ? $converted : preg_replace('/[^\x20-\x7E]/', '', $text);
    }
}
