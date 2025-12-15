<?php
/**
 * Export Module - Excel Export Functionality
 */

function exportToExcel($pdo, $backup_dir) {
    try {
        // Get all customers
        $stmt = $pdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($customers)) {
            return ['success' => false, 'message' => 'No customers to export'];
        }
        
        // Ensure backup directory exists
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        // Generate filename with crm_yymmdd format
        $timestamp = date('ymd_His');
        $filename = 'crm_' . $timestamp . '.xlsx';
        $filepath = $backup_dir . '/' . $filename;
        
        // Check if PhpSpreadsheet is available
        $phpspreadsheetAvailable = false;
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
            $phpspreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet');
        }
        
        if ($phpspreadsheetAvailable) {
            // Use PhpSpreadsheet for proper Excel format
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Get column headers from first customer
            $headers = array_keys($customers[0]);
            
            // Write headers
            $col = 1;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col, 1, $header);
                $col++;
            }
            
            // Style header row
            $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow(count($headers), 1)->getCoordinate())
                  ->getFont()->setBold(true);
            $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow(count($headers), 1)->getCoordinate())
                  ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FF667EEA');
            $sheet->getStyle('A1:' . $sheet->getCellByColumnAndRow(count($headers), 1)->getCoordinate())
                  ->getFont()->getColor()->setARGB('FFFFFFFF');
            
            // Write data rows
            $row = 2;
            foreach ($customers as $customer) {
                $col = 1;
                foreach ($headers as $header) {
                    $value = isset($customer[$header]) ? $customer[$header] : '';
                    $sheet->setCellValueByColumnAndRow($col, $row, $value);
                    $col++;
                }
                $row++;
            }
            
            // Auto-size columns
            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            
            // Write file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filepath);
            
        } else {
            // Fallback: Try to create Excel using ZipArchive
            if (class_exists('ZipArchive')) {
                $result = createSimpleExcel($customers, $filepath);
                if (!$result['success']) {
                    return $result;
                }
            } else {
                // Final fallback: Export as CSV (no ZIP required)
                $csvFilename = str_replace('.xlsx', '.csv', $filename);
                $csvFilepath = str_replace('.xlsx', '.csv', $filepath);
                $result = exportToCSV($customers, $csvFilepath);
                if (!$result['success']) {
                    return $result;
                }
                // Update return values to reflect CSV
                $filename = $csvFilename;
                $filepath = $csvFilepath;
            }
        }
        
        // Determine file type for message
        $fileType = (strpos($filename, '.csv') !== false) ? 'CSV' : 'Excel';
        $message = $fileType . ' file exported successfully';
        if ($fileType === 'CSV') {
            $message .= ' (Excel export requires ZIP extension or PhpSpreadsheet)';
        }
        
        return [
            'success' => true, 
            'message' => $message, 
            'filename' => $filename,
            'filepath' => $filepath
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Export failed: ' . $e->getMessage()];
    }
}

function exportToCSV($customers, $filepath) {
    // Export as CSV (no ZIP or PhpSpreadsheet required)
    try {
        $fp = fopen($filepath, 'w');
        if (!$fp) {
            return ['success' => false, 'message' => 'Failed to create CSV file'];
        }
        
        // Write headers
        if (!empty($customers)) {
            $headers = array_keys($customers[0]);
            fputcsv($fp, $headers);
            
            // Write data rows
            foreach ($customers as $customer) {
                $row = [];
                foreach ($headers as $header) {
                    $row[] = isset($customer[$header]) ? $customer[$header] : '';
                }
                fputcsv($fp, $row);
            }
        }
        
        fclose($fp);
        
        return [
            'success' => true,
            'message' => 'CSV file exported successfully (Excel export requires ZIP extension or PhpSpreadsheet)',
            'filename' => basename($filepath),
            'filepath' => $filepath
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'CSV export failed: ' . $e->getMessage()];
    }
}

function createSimpleExcel($customers, $filepath) {
    // Check if ZipArchive class is available (most reliable check)
    if (!class_exists('ZipArchive')) {
        // This should not be reached if we check before calling, but just in case
        return ['success' => false, 'message' => 'ZIP extension is not available. CSV export is available as an alternative.'];
    }
    
    $headers = array_keys($customers[0]);
    $zip = new ZipArchive();
    
    $zipResult = $zip->open($filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($zipResult !== TRUE) {
        $errorMsg = 'Failed to create Excel file';
        switch ($zipResult) {
            case ZipArchive::ER_EXISTS:
                $errorMsg .= ': File already exists';
                break;
            case ZipArchive::ER_INCONS:
                $errorMsg .= ': Archive inconsistent';
                break;
            case ZipArchive::ER_INVAL:
                $errorMsg .= ': Invalid argument';
                break;
            case ZipArchive::ER_MEMORY:
                $errorMsg .= ': Memory allocation failure';
                break;
            case ZipArchive::ER_NOZIP:
                $errorMsg .= ': Not a zip archive';
                break;
            case ZipArchive::ER_OPEN:
                $errorMsg .= ': Cannot open file';
                break;
            case ZipArchive::ER_READ:
                $errorMsg .= ': Read error';
                break;
            case ZipArchive::ER_SEEK:
                $errorMsg .= ': Seek error';
                break;
        }
        return ['success' => false, 'message' => $errorMsg . '. Error code: ' . $zipResult];
    }
    
    // Create proper Excel .xlsx structure
    // 1. [Content_Types].xml
    $zip->addFromString('[Content_Types].xml', getContentTypesXml());
    
    // 2. _rels/.rels
    $zip->addFromString('_rels/.rels', getRelsXml());
    
    // 3. xl/workbook.xml
    $zip->addFromString('xl/workbook.xml', getWorkbookXml());
    
    // 4. xl/_rels/workbook.xml.rels
    $zip->addFromString('xl/_rels/workbook.xml.rels', getWorkbookRelsXml());
    
    // 5. Build shared strings first (needed for worksheet)
    $sharedStringsData = buildSharedStrings($customers, $headers);
    
    // 6. xl/worksheets/sheet1.xml (proper format)
    $worksheetXml = getWorksheetXml($customers, $headers, $sharedStringsData);
    $zip->addFromString('xl/worksheets/sheet1.xml', $worksheetXml);
    
    // 7. xl/sharedStrings.xml (for string values)
    $sharedStringsXml = getSharedStringsXml($sharedStringsData);
    $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
    
    // 8. xl/styles.xml (basic styles)
    $zip->addFromString('xl/styles.xml', getStylesXml());
    
    // 9. docProps/app.xml
    $zip->addFromString('docProps/app.xml', getAppXml());
    
    // 10. docProps/core.xml
    $zip->addFromString('docProps/core.xml', getCoreXml());
    
    if (!$zip->close()) {
        return ['success' => false, 'message' => 'Failed to finalize Excel file'];
    }
    
    return ['success' => true, 'message' => 'Excel file exported successfully', 'filename' => basename($filepath), 'filepath' => $filepath];
}

function buildSharedStrings($customers, $headers) {
    $sharedStrings = [];
    
    // Add headers to shared strings
    foreach ($headers as $header) {
        $sharedStrings[] = $header;
    }
    
    // Add all data values to shared strings
    foreach ($customers as $customer) {
        foreach ($headers as $header) {
            $value = isset($customer[$header]) ? $customer[$header] : '';
            $sharedStrings[] = $value;
        }
    }
    
    return $sharedStrings;
}

function getWorksheetXml($customers, $headers, $sharedStrings) {
    $totalRows = count($customers) + 1; // +1 for header row
    $totalCols = count($headers);
    $lastCell = getCellReference($totalRows, $totalCols - 1);
    
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">' . "\n";
    $xml .= '<dimension ref="A1:' . $lastCell . '"/>' . "\n";
    $xml .= '<sheetViews>' . "\n";
    $xml .= '<sheetView workbookViewId="0"/>' . "\n";
    $xml .= '</sheetViews>' . "\n";
    $xml .= '<sheetData>' . "\n";
    
    // Header row (row 1)
    $xml .= '<row r="1">' . "\n";
    $col = 0;
    $stringIdx = 0;
    foreach ($headers as $header) {
        $cellRef = getCellReference(1, $col);
        $xml .= '<c r="' . $cellRef . '" t="s"><v>' . $stringIdx . '</v></c>' . "\n";
        $col++;
        $stringIdx++;
    }
    $xml .= '</row>' . "\n";
    
    // Data rows
    $rowNum = 2;
    foreach ($customers as $customer) {
        $xml .= '<row r="' . $rowNum . '">' . "\n";
        $col = 0;
        foreach ($headers as $header) {
            $cellRef = getCellReference($rowNum, $col);
            $xml .= '<c r="' . $cellRef . '" t="s"><v>' . $stringIdx . '</v></c>' . "\n";
            $col++;
            $stringIdx++;
        }
        $xml .= '</row>' . "\n";
        $rowNum++;
    }
    
    $xml .= '</sheetData>' . "\n";
    $xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>' . "\n";
    $xml .= '</worksheet>';
    
    return $xml;
}

function getCellReference($row, $col) {
    $colLetter = '';
    $colNum = $col;
    while ($colNum >= 0) {
        $colLetter = chr(65 + ($colNum % 26)) . $colLetter;
        $colNum = intval($colNum / 26) - 1;
        if ($colNum < 0) break;
    }
    return $colLetter . $row;
}

function getSharedStringsXml($sharedStrings) {
    $totalStrings = count($sharedStrings);
    
    $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
    $xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $totalStrings . '" uniqueCount="' . $totalStrings . '">' . "\n";
    
    // Add all strings in order
    foreach ($sharedStrings as $string) {
        if ($string === '' || $string === null) {
            $xml .= '<si><t></t></si>' . "\n";
        } else {
            $xml .= '<si><t>' . htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>' . "\n";
        }
    }
    
    $xml .= '</sst>';
    return $xml;
}

function getStylesXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<numFmts count="0"/>
<fonts count="1">
<font>
<sz val="11"/>
<color theme="1"/>
<name val="Calibri"/>
<family val="2"/>
<scheme val="minor"/>
</font>
</fonts>
<fills count="2">
<fill><patternFill patternType="none"/></fill>
<fill><patternFill patternType="gray125"/></fill>
</fills>
<borders count="1">
<border><left/><right/><top/><bottom/><diagonal/></border>
</borders>
<cellStyleXfs count="1">
<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
</cellStyleXfs>
<cellXfs count="1">
<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
</cellXfs>
<cellStyles count="1">
<cellStyle name="Normal" xfId="0" builtinId="0"/>
</cellStyles>
<dxfs count="0"/>
</styleSheet>';
}

function getAppXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
<Application>CRM System</Application>
<TotalTime>0</TotalTime>
</Properties>';
}

function getCoreXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<dc:creator>CRM System</dc:creator>
<dcterms:created xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:created>
<dcterms:modified xsi:type="dcterms:W3CDTF">' . date('c') . '</dcterms:modified>
</cp:coreProperties>';
}

function getContentTypesXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
</Types>';
}

function getRelsXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
}

function getWorkbookRelsXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
}

function getWorkbookXml() {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x15" xmlns:x15="http://schemas.microsoft.com/office/spreadsheetml/2010/11/main">
<sheets>
<sheet name="Customers" sheetId="1" r:id="rId1"/>
</sheets>
</workbook>';
}

?>
