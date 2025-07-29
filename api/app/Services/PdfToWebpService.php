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
        
        // Configurações importantes para PDFs com CMYK
        $imagick->setOption('pdf:use-cropbox', 'true');
        $imagick->setOption('pdf:use-trimbox', 'true');
        
        // Define colorspace RGB para evitar problemas com CMYK
        $imagick->setColorspace(Imagick::COLORSPACE_SRGB);
        
        // Lê o PDF inteiro; cada página vira um frame
        $imagick->readImage($fullPdfPath);

        $generated = [];

        foreach ($imagick as $index => $page) {
            // Força conversão para RGB se estiver em CMYK
            if ($page->getImageColorspace() === Imagick::COLORSPACE_CMYK) {
                $page->transformImageColorspace(Imagick::COLORSPACE_SRGB);
            }
            
            // Remove canal alpha para eliminar transparência
            $page->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            
            // Define fundo branco sólido
            $page->setImageBackgroundColor('white');
            
            // Normaliza a imagem para melhorar o contraste
            $page->normalizeImage();
            
            // Cria uma nova imagem com fundo branco
            $background = new Imagick();
            $background->newImage($page->getImageWidth(), $page->getImageHeight(), 'white');
            $background->setImageColorspace(Imagick::COLORSPACE_SRGB);
            
            // Compõe a página sobre o fundo branco
            $background->compositeImage($page, Imagick::COMPOSITE_OVER, 0, 0);
            
            // Achata as camadas para garantir fundo sólido
            $background = $background->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

            // Define o formato de saída e compressão
            $background->setImageFormat('webp');
            $background->setImageCompressionQuality(80);
            
            // Aplica uma leve redução de ruído se necessário
            $background->despeckleImage();

            // Nome de arquivo: pdf-nome-página-{i}.webp
            $filename = Str::slug(pathinfo($pdfPath, PATHINFO_FILENAME))
                      . "-page-{$index}.webp";

            $relativePath = "{$outputDir}/{$filename}";

            // grava no disco public
            $disk->put($relativePath, $background->getImageBlob());

            $generated[] = $relativePath;
            
            // Limpa a imagem de fundo da memória
            $background->clear();
            $background->destroy();
        }

        // Libera memória
        $imagick->clear();
        $imagick->destroy();

        return $generated;
    }
}
