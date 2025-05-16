<?php

namespace App\Services;

use Imagick;
use ImagickException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfToWebpService
{
    /**
     * Converte cada página de um PDF em imagens .webp.
     *
     * @param  string  $pdfPath      Caminho no disco (Storage) até o PDF.
     * @param  string  $outputDir    Diretório de saída em Storage (ex: 'public/webp-pages').
     * @param  int     $resolution   DPI de renderização por página.
     * @return array                 Lista de paths (Storage) das imagens geradas.
     *
     * @throws ImagickException
     */
    public function convert(string $pdfPath, string $outputDir, int $resolution = 150): array
    {
        // usar o disco public
        $disk = Storage::disk('public');
        $disk->makeDirectory($outputDir);

        // caminho físico no disco public
        $fullPdfPath = $disk->path($pdfPath);

        $imagick = new Imagick();

        // Define a resolução antes de ler (importante para qualidade)
        $imagick->setResolution($resolution, $resolution);

        // Lê o PDF inteiro; cada página vira um frame
        $imagick->readImage($fullPdfPath);

        $generated = [];

        foreach ($imagick as $index => $page) {
            // Define o formato de saída
            $page->setImageFormat('webp');
            // Opcional: ajustar a compressão
            $page->setImageCompressionQuality(80);

            // Nome de arquivo: pdf-nome-página-{i}.webp
            $filename = Str::slug(pathinfo($pdfPath, PATHINFO_FILENAME))
                      . "-page-{$index}.webp";

            $relativePath = "{$outputDir}/{$filename}";

            // grava no disco public
            $disk->put($relativePath, $page->getImageBlob());

            $generated[] = $relativePath;
        }

        // Libera memória
        $imagick->clear();
        $imagick->destroy();

        return $generated;
    }
}
