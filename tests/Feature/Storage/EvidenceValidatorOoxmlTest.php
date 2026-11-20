<?php

namespace Tests\Feature\Storage;

use App\Exceptions\InvalidEvidence;
use App\Services\Storage\EvidenceValidator;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * P2.2: la firma ZIP no es suficiente para DOCX/XLSX. Se valida la estructura OOXML
 * (partes obligatorias, tipo de contenido y relaciones) sin extraer indiscriminadamente,
 * rechazando ZIP genéricos, extensiones cruzadas, archivos corruptos y paquetes que
 * excedan límites defensivos.
 */
class EvidenceValidatorOoxmlTest extends TestCase
{
    public function test_documento_word_valido_pasa_validacion(): void
    {
        $validator = new EvidenceValidator;
        $archivo = $this->archivo($this->empaquetar($this->partesDocx()), 'informe.docx');

        $resultado = $validator->validate($archivo);

        $this->assertSame('docx', $resultado['extension']);
        $this->assertSame('informe.docx', $resultado['original_name']);
        $this->assertStringStartsWith('application/vnd.openxmlformats-officedocument', $resultado['mime']);
    }

    public function test_hoja_de_calculo_valida_pasa_validacion(): void
    {
        $validator = new EvidenceValidator;
        $archivo = $this->archivo($this->empaquetar($this->partesXlsx()), 'datos.xlsx');

        $resultado = $validator->validate($archivo);

        $this->assertSame('xlsx', $resultado['extension']);
        $this->assertSame('datos.xlsx', $resultado['original_name']);
    }

    public function test_zip_generico_sin_estructura_ooxml_es_rechazado_como_docx(): void
    {
        $this->expectException(InvalidEvidence::class);
        $contenido = $this->empaquetar(['readme.txt' => 'hola mundo']);
        $validator = new EvidenceValidator;
        $validator->validate($this->archivo($contenido, 'falso.docx'));
    }

    public function test_archivo_corrupto_es_rechazado(): void
    {
        $this->expectException(InvalidEvidence::class);
        $validator = new EvidenceValidator;
        $validator->validate($this->archivo('no es un zip valido', 'corrupto.docx'));
    }

    public function test_extension_cruzada_estructura_xlsx_con_nombre_docx_es_rechazada(): void
    {
        $this->expectException(InvalidEvidence::class);
        $validator = new EvidenceValidator;
        $validator->validate($this->archivo($this->empaquetar($this->partesXlsx()), 'falso.docx'));
    }

    public function test_docx_sin_parte_principal_es_rechazado(): void
    {
        $this->expectException(InvalidEvidence::class);
        $validator = new EvidenceValidator;
        $partes = $this->partesDocx();
        unset($partes['word/document.xml']);
        $validator->validate($this->archivo($this->empaquetar($partes), 'incompleto.docx'));
    }

    public function test_pdf_jpg_png_siguen_validandose_por_magia_de_bytes(): void
    {
        $validator = new EvidenceValidator;

        $pdf = $this->archivo("%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\nxref\n0 2\n0000000000 65535 f \n0000000009 00000 n \ntrailer\n<< /Size 2 /Root 1 0 R >>\nstartxref\n56\n%%EOF", 'documento.pdf');
        $this->assertSame('pdf', $validator->validate($pdf)['extension']);

        $jpg = $this->archivo("\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00\xFF\xD9", 'foto.jpg');
        $this->assertSame('jpg', $validator->validate($jpg)['extension']);

        $png = $this->archivo("\x89PNG\r\n\x1A\n\x00\x00\x00\rIHDR\x00\x00\x00\x01\x00\x00\x00\x01\x08\x06\x00\x00\x00\x1f\x15\xc4\x89", 'imagen.png');
        $this->assertSame('png', $validator->validate($png)['extension']);
    }

    public function test_paquete_con_demasiadas_entradas_es_rechazado(): void
    {
        $this->expectException(InvalidEvidence::class);
        $partes = [];
        for ($i = 0; $i < 3001; $i++) {
            $partes["archivo{$i}.txt"] = str_repeat('x', 100);
        }
        $validator = new EvidenceValidator;
        $validator->validate($this->archivo($this->empaquetar($partes), 'bomba.docx'));
    }

    public function test_content_types_xml_mal_formado_no_genera_500_se_rechaza_como_invalido(): void
    {
        $this->expectException(InvalidEvidence::class);

        $partes = $this->partesDocx();
        // XML mal formado: etiqueta de cierre faltante en el Override.
        $partes['[Content_Types].xml'] = '<Types>
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"
</Types>';

        $validator = new EvidenceValidator;
        $validator->validate($this->archivo($this->empaquetar($partes), 'malformado.docx'));
    }

    public function test_relacion_sin_tipo_office_document_correcto_es_rechazada(): void
    {
        $this->expectException(InvalidEvidence::class);

        $partes = $this->partesDocx();
        // La relación apunta a la parte principal pero su Type no es officeDocument.
        $partes['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata" Target="word/document.xml"/>
</Relationships>';

        $validator = new EvidenceValidator;
        $validator->validate($this->archivo($this->empaquetar($partes), 'tipo-incorrecto.docx'));
    }

    private function partesDocx(): array
    {
        return [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>',
            'word/document.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p></w:p></w:body></w:document>',
        ];
    }

    private function partesXlsx(): array
    {
        return [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets><sheet name="Hoja1" sheetId="1" state="visible"/></sheets></workbook>',
        ];
    }

    private function empaquetar(array $partes): string
    {
        $ruta = tempnam(sys_get_temp_dir(), 'oxml_');
        $zip = new \ZipArchive;
        $zip->open($ruta, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($partes as $nombre => $contenido) {
            $zip->addFromString($nombre, $contenido);
        }
        $zip->close();

        return file_get_contents($ruta);
    }

    private function archivo(string $contenido, string $nombre): UploadedFile
    {
        $ruta = tempnam(sys_get_temp_dir(), 'aris_');
        file_put_contents($ruta, $contenido);

        return new UploadedFile($ruta, $nombre, null, null, true);
    }
}
