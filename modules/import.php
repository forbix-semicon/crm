<?php
/**
 * Import Module - Excel and DB Import Functionality
 */

function validateExcelFile($pdo, $filepath) {
    // This function validates the Excel file and returns preview/validation results
    try {
        require_once __DIR__ . '/admin.php';
        
        $rows = readExcelFile($filepath);
        
        if (empty($rows) || count($rows) < 2) {
            return [
                'success' => false,
                'valid' => false,
                'message' => 'Excel file is empty or has no data rows',
                'totalRows' => 0,
                'validRows' => 0,
                'errors' => ['File contains no data rows']
            ];
        }
        
        // Get allowed values from database
        $allowedValues = getAllowedValues($pdo);
        
        // First row is headers
        $headers = array_map('trim', $rows[0]);
        
        // Expected columns
        $expectedColumns = [
            'customer_id', 'date', 'time', 'customer_name', 'company_name',
            'primary_contact', 'secondary_contact', 'email_id', 'city',
            'product_category', 'requirement', 'source', 'assigned_to',
            'customer_type', 'status', 'comments'
        ];
        
        // Validate headers
        $headerMap = [];
        foreach ($expectedColumns as $col) {
            $index = array_search($col, $headers);
            if ($index !== false) {
                $headerMap[$col] = $index;
            }
        }
        
        if (empty($headerMap)) {
            return [
                'success' => false,
                'valid' => false,
                'message' => 'Invalid Excel format: Column headers do not match expected format',
                'totalRows' => count($rows) - 1,
                'validRows' => 0,
                'errors' => ['Column headers do not match expected format. Required columns: ' . implode(', ', $expectedColumns)]
            ];
        }
        
        $errors = [];
        $warnings = [];
        $validRows = 0;
        $totalRows = count($rows) - 1; // Exclude header
        $needsAutoCID = 0;
        $duplicateCIDs = [];
        $preview = [];
        $invalidFields = []; // Track fields that will be cleared
        
        // Validate data rows (skip header row)
        for ($i = 1; $i < count($rows); $i++) {
            $lineNumber = $i + 1;
            $row = $rows[$i];
            
            if (count($row) < count($headers)) {
                $errors[] = "Row $lineNumber: Insufficient columns";
                continue;
            }
            
            // Map row data to columns
            $data = [];
            foreach ($headerMap as $col => $index) {
                $value = isset($row[$index]) ? trim($row[$index]) : '';
                $originalValue = $value;
                
                // Validate field value against allowed values
                $validatedValue = validateFieldValue($col, $value, $allowedValues);
                
                // Track if value was cleared
                if (!empty($originalValue) && empty($validatedValue) && in_array($col, ['product_category', 'source', 'assigned_to', 'customer_type', 'status'])) {
                    $invalidFields[] = "Row $lineNumber: '$col' value '{$originalValue}' not found in allowed values (will be cleared)";
                }
                
                $data[$col] = $validatedValue;
            }
            
            // Check if customer_id is empty or missing
            if (empty($data['customer_id'])) {
                $needsAutoCID++;
                $data['customer_id'] = '[AUTO]'; // Will be generated
                $warnings[] = "Row $lineNumber: Customer ID will be auto-generated";
            } else {
                // Check if customer_id already exists
                $checkStmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
                $checkStmt->execute([$data['customer_id']]);
                if ($checkStmt->fetch()) {
                    $duplicateCIDs[] = $data['customer_id'];
                    $errors[] = "Row $lineNumber: Customer ID {$data['customer_id']} already exists in database";
                    continue;
                }
            }
            
            // Add to preview (first 5 rows)
            if (count($preview) < 5) {
                $preview[] = [
                    'row' => $lineNumber,
                    'customer_id' => $data['customer_id'],
                    'customer_name' => $data['customer_name'] ?? '',
                    'company_name' => $data['company_name'] ?? ''
                ];
            }
            
            $validRows++;
        }
        
        // Add invalid field warnings
        if (!empty($invalidFields)) {
            $warnings = array_merge($warnings, array_slice($invalidFields, 0, 10));
        }
        
        $isValid = empty($errors);
        $message = '';
        
        if ($isValid) {
            $message = "✓ Excel file is OK to import!";
        } else {
            $message = "✗ Excel file has ERRORS!";
        }
        
        return [
            'success' => true,
            'valid' => $isValid,
            'message' => $message,
            'totalRows' => $totalRows,
            'validRows' => $validRows,
            'needsAutoCID' => $needsAutoCID,
            'errors' => array_slice($errors, 0, 10), // Limit to 10 errors for preview
            'warnings' => array_slice($warnings, 0, 10),
            'preview' => $preview
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'valid' => false,
            'message' => 'Validation failed: ' . $e->getMessage(),
            'totalRows' => 0,
            'validRows' => 0,
            'errors' => [$e->getMessage()]
        ];
    }
}

function readExcelFile($filepath) {
    // Shared function to read Excel file (supports .xlsx and .csv)
    if (!file_exists($filepath)) {
        throw new Exception('File not found');
    }
    
    // Check if it's a CSV file
    $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
    if ($extension === 'csv') {
        return readCSVFile($filepath);
    }
    
    // Check if PhpSpreadsheet is available
    $phpspreadsheetAvailable = false;
    if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $phpspreadsheetAvailable = class_exists('PhpOffice\PhpSpreadsheet\IOFactory');
    }
    
    if ($phpspreadsheetAvailable) {
        // Use PhpSpreadsheet to read Excel file
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filepath);
        $worksheet = $spreadsheet->getActiveSheet();
        return $worksheet->toArray();
    } else {
        // Fallback: Try to read Excel file using ZIP
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            $zipResult = $zip->open($filepath);
            if ($zipResult === TRUE) {
                // Read shared strings
                $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
                // Read worksheet
                $worksheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
                $zip->close();
                
                if ($worksheetXml && $sharedStringsXml) {
                    return parseExcelXlsx($worksheetXml, $sharedStringsXml);
                } else {
                    throw new Exception('Failed to read Excel file structure');
                }
            } else {
                throw new Exception('Failed to open Excel file. Error code: ' . $zipResult);
            }
        } else {
            // Final fallback: Try to read as CSV if file extension suggests it might be CSV
            throw new Exception('ZIP extension not available and PhpSpreadsheet not installed. Please install PhpSpreadsheet using: composer require phpoffice/phpspreadsheet, or use CSV format instead.');
        }
    }
}

function readCSVFile($filepath) {
    // Read CSV file (no ZIP or PhpSpreadsheet required)
    $rows = [];
    $fp = fopen($filepath, 'r');
    if (!$fp) {
        throw new Exception('Failed to open CSV file');
    }
    
    // Read file line by line
    while (($row = fgetcsv($fp)) !== FALSE) {
        $rows[] = $row;
    }
    
    fclose($fp);
    return $rows;
}

function getAllowedValues($pdo) {
    // Get all allowed values from database tables
    // Returns array with lowercase keys mapping to original values
    $allowed = [];
    
    // Product Categories
    $stmt = $pdo->query("SELECT name FROM product_categories ORDER BY name");
    $allowed['product_category'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['name']);
        $allowed['product_category'][strtolower($name)] = $name;
    }
    
    // Customer Types
    $stmt = $pdo->query("SELECT name FROM customer_types ORDER BY name");
    $allowed['customer_type'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['name']);
        $allowed['customer_type'][strtolower($name)] = $name;
    }
    
    // Statuses
    $stmt = $pdo->query("SELECT name FROM statuses ORDER BY name");
    $allowed['status'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['name']);
        $allowed['status'][strtolower($name)] = $name;
    }
    
    // Sources
    $stmt = $pdo->query("SELECT name FROM sources ORDER BY name");
    $allowed['source'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['name']);
        $allowed['source'][strtolower($name)] = $name;
    }
    
    // Users (for assigned_to)
    $stmt = $pdo->query("SELECT username FROM users ORDER BY username");
    $allowed['assigned_to'] = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $name = trim($row['username']);
        $allowed['assigned_to'][strtolower($name)] = $name;
    }
    
    return $allowed;
}

function validateFieldValue($field, $value, $allowedValues) {
    // Validate field value against allowed values
    if (empty($value)) {
        return ''; // Empty values are always valid (will be stored as empty)
    }
    
    $value = trim($value);
    
    // Email ID validation (check format, not against a list)
    if ($field === 'email_id') {
        // Split multiple emails by newline
        $emails = preg_split('/[\r\n]+/', $value);
        $validEmails = [];
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email)) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validEmails[] = $email;
                }
                // If invalid, skip it (don't add to validEmails)
            }
        }
        return implode("\n", $validEmails); // Return only valid emails, empty if none valid
    }
    
    // City, Requirement, Comments - free text fields, no validation needed
    if (in_array($field, ['city', 'requirement', 'comments'])) {
        return $value; // Return as-is
    }
    
    // Check against allowed values (case-insensitive)
    if (isset($allowedValues[$field])) {
        $valueLower = strtolower($value);
        if (isset($allowedValues[$field][$valueLower])) {
            // Match found - return the original database value (preserves correct case)
            return $allowedValues[$field][$valueLower];
        }
        // Value not found in allowed list - return empty
        return '';
    }
    
    // Field not in validation list - return as-is
    return $value;
}

function importFromExcel($pdo, $filepath) {
    try {
        require_once __DIR__ . '/../config/db.php';
        require_once __DIR__ . '/admin.php';
        
        $rows = readExcelFile($filepath);
        
        if (empty($rows) || count($rows) < 2) {
            return ['success' => false, 'message' => 'Excel file is empty or has no data rows'];
        }
        
        // Get allowed values from database
        $allowedValues = getAllowedValues($pdo);
        
        // First row is headers
        $headers = array_map('trim', $rows[0]);
        
        // Expected columns
        $expectedColumns = [
            'customer_id', 'date', 'time', 'customer_name', 'company_name',
            'primary_contact', 'secondary_contact', 'email_id', 'city',
            'product_category', 'requirement', 'source', 'assigned_to',
            'customer_type', 'status', 'comments'
        ];
        
        // Validate headers
        $headerMap = [];
        foreach ($expectedColumns as $col) {
            $index = array_search($col, $headers);
            if ($index !== false) {
                $headerMap[$col] = $index;
            }
        }
        
        if (empty($headerMap)) {
            return ['success' => false, 'message' => 'Invalid Excel format: Column headers do not match expected format'];
        }
        
        $imported = 0;
        $errors = [];
        $lineNumber = 1;
        
        // Get next customer ID for auto-generation
        // Function is in config/db.php which is already included via api.php
        $nextCID = getNextCustomerID($pdo);
        $cidCounter = 0;
        
        // Extract base number from next CID for incrementing
        $baseCIDNum = 20001;
        if (preg_match('/CID(\d+)/', $nextCID, $matches)) {
            $baseCIDNum = intval($matches[1]);
        }
        
        // Prepare insert statement
        $columns = implode(', ', array_keys($headerMap));
        $placeholders = ':' . implode(', :', array_keys($headerMap));
        $stmt = $pdo->prepare("INSERT INTO customers ($columns) VALUES ($placeholders)");
        
        // Read data rows (skip header row)
        for ($i = 1; $i < count($rows); $i++) {
            $lineNumber++;
            $row = $rows[$i];
            
            if (count($row) < count($headers)) {
                $errors[] = "Row $lineNumber: Insufficient columns";
                continue;
            }
            
            // Map row data to columns
            $data = [];
            foreach ($headerMap as $col => $index) {
                $value = isset($row[$index]) ? trim($row[$index]) : '';
                // Handle Excel date format if needed
                if (($col === 'date' || $col === 'time') && is_numeric($value) && $value > 25569) {
                    // Excel date serial number
                    $value = date('Y-m-d', ($value - 25569) * 86400);
                }
                
                // Validate field value against allowed values
                $data[$col] = validateFieldValue($col, $value, $allowedValues);
            }
            
            // Auto-generate CID if missing
            if (empty($data['customer_id'])) {
                // Generate next CID by incrementing
                $newCIDNum = $baseCIDNum + $cidCounter;
                $data['customer_id'] = 'CID' . $newCIDNum;
                $cidCounter++;
            }
            
            // Check if customer_id already exists
            $checkStmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
            $checkStmt->execute([$data['customer_id']]);
            if ($checkStmt->fetch()) {
                $errors[] = "Row $lineNumber: Customer ID {$data['customer_id']} already exists (skipped)";
                continue;
            }
            
            // Insert customer
            try {
                $stmt->execute($data);
                $imported++;
            } catch (PDOException $e) {
                $errors[] = "Row $lineNumber: " . $e->getMessage();
            }
        }
        
        $message = "Imported $imported customer(s)";
        if (!empty($errors)) {
            $message .= ". " . count($errors) . " error(s): " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " (and " . (count($errors) - 5) . " more)";
            }
        }
        
        return [
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
    }
}

function parseExcelXlsx($worksheetXml, $sharedStringsXml) {
    // Parse modern Excel .xlsx format (Office Open XML)
    $rows = [];
    
    // Parse shared strings first
    $sharedStrings = [];
    $sharedStringsObj = simplexml_load_string($sharedStringsXml);
    if ($sharedStringsObj !== false) {
        $namespaces = $sharedStringsObj->getNamespaces(true);
        foreach ($sharedStringsObj->si as $si) {
            $text = (string)$si->t;
            $sharedStrings[] = $text;
        }
    }
    
    // Parse worksheet
    $worksheetObj = simplexml_load_string($worksheetXml);
    if ($worksheetObj === false) {
        return [];
    }
    
    $namespaces = $worksheetObj->getNamespaces(true);
    
    // Get all rows from sheetData
    if (isset($worksheetObj->sheetData->row)) {
        foreach ($worksheetObj->sheetData->row as $row) {
            $rowData = [];
            $rowNum = (int)$row['r'];
            
            // Get all cells in this row
            $cells = [];
            foreach ($row->c as $cell) {
                $cellRef = (string)$cell['r'];
                // Extract column letter from cell reference (e.g., "A1" -> "A")
                preg_match('/^([A-Z]+)/', $cellRef, $matches);
                $colLetter = $matches[1] ?? '';
                $colIndex = columnLetterToIndex($colLetter);
                
                // Check if cell uses shared string (t="s")
                $cellType = (string)$cell['t'];
                $value = '';
                
                if (isset($cell->v)) {
                    $cellValue = (string)$cell->v;
                    if ($cellType === 's') {
                        // Shared string - get value from shared strings array
                        $stringIndex = (int)$cellValue;
                        if (isset($sharedStrings[$stringIndex])) {
                            $value = $sharedStrings[$stringIndex];
                        }
                    } else {
                        // Direct value
                        $value = $cellValue;
                    }
                }
                
                $cells[$colIndex] = $value;
            }
            
            // Sort cells by column index and fill in missing columns with empty strings
            ksort($cells);
            $maxCol = !empty($cells) ? max(array_keys($cells)) : -1;
            for ($i = 0; $i <= $maxCol; $i++) {
                $rowData[$i] = isset($cells[$i]) ? $cells[$i] : '';
            }
            
            $rows[] = $rowData;
        }
    }
    
    return $rows;
}

function columnLetterToIndex($letter) {
    // Convert Excel column letter to index (A=0, B=1, ..., Z=25, AA=26, etc.)
    $index = 0;
    $length = strlen($letter);
    for ($i = 0; $i < $length; $i++) {
        $index = $index * 26 + (ord($letter[$i]) - ord('A') + 1);
    }
    return $index - 1;
}

function parseExcelXml($xml) {
    // Legacy function for old XML Spreadsheet format (kept for backward compatibility)
    $rows = [];
    $xmlObj = simplexml_load_string($xml);
    if ($xmlObj === false) {
        return [];
    }
    
    $namespaces = $xmlObj->getNamespaces(true);
    $ss = isset($namespaces['ss']) ? $namespaces['ss'] : '';
    
    if (isset($xmlObj->Table->Row)) {
        foreach ($xmlObj->Table->Row as $row) {
            $rowData = [];
            if (isset($row->Cell)) {
                foreach ($row->Cell as $cell) {
                    $data = $cell->Data;
                    $rowData[] = (string)$data;
                }
            }
            $rows[] = $rowData;
        }
    }
    
    return $rows;
}

function importFromDB($pdo, $source_db_path, $target_db_path) {
    try {
        if (!file_exists($source_db_path)) {
            return ['success' => false, 'message' => 'Database file not found'];
        }
        
        // Connect to source database
        $sourcePdo = new PDO('sqlite:' . $source_db_path);
        $sourcePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sourcePdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Get all customers from source
        $stmt = $sourcePdo->query("SELECT * FROM customers ORDER BY customer_id DESC");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($customers)) {
            return ['success' => false, 'message' => 'No customers found in source database'];
        }
        
        $imported = 0;
        $errors = [];
        
        // Prepare insert statement for target database
        $columns = 'customer_id, date, time, customer_name, company_name, primary_contact, secondary_contact, email_id, city, product_category, requirement, source, assigned_to, customer_type, status, comments';
        $placeholders = ':customer_id, :date, :time, :customer_name, :company_name, :primary_contact, :secondary_contact, :email_id, :city, :product_category, :requirement, :source, :assigned_to, :customer_type, :status, :comments';
        $insertStmt = $pdo->prepare("INSERT INTO customers ($columns) VALUES ($placeholders)");
        
        foreach ($customers as $customer) {
            // Check if customer_id already exists
            $checkStmt = $pdo->prepare("SELECT id FROM customers WHERE customer_id = ?");
            $checkStmt->execute([$customer['customer_id']]);
            if ($checkStmt->fetch()) {
                $errors[] = "Customer ID {$customer['customer_id']} already exists (skipped)";
                continue;
            }
            
            // Insert customer
            try {
                $insertStmt->execute($customer);
                $imported++;
            } catch (PDOException $e) {
                $errors[] = "Customer ID {$customer['customer_id']}: " . $e->getMessage();
            }
        }
        
        $message = "Imported $imported customer(s) from database";
        if (!empty($errors)) {
            $message .= ". " . count($errors) . " error(s): " . implode('; ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= " (and " . (count($errors) - 5) . " more)";
            }
        }
        
        return [
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'errors' => $errors
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Import failed: ' . $e->getMessage()];
    }
}

?>
