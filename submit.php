<?php
// submit.php - Registration Handler
// 1. Saves data to 'registrations_2026.csv'
// 2. Saves full abstracts to 'abstracts/' folder

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $csvFile = 'registrations_2026.csv';
    $abstractsDir = 'abstracts';

    // Create the directory for abstracts if it doesn't exist
    if (!is_dir($abstractsDir)) {
        mkdir($abstractsDir, 0755, true);
    }

    // Open CSV in append mode
    $handle = fopen($csvFile, 'a');

    // Create headers if file is new
    if (filesize($csvFile) == 0) {
        // Add BOM for Excel UTF-8 compatibility
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        // Note the new column: 'Abstract File'
        fputcsv($handle, array('Date', 'Form Type', 'Name', 'Institution', 'Email', 'Abstract File', 'Details'), ";");
    }

    // --- Capture Data ---
    $date = date('Y-m-d H:i:s');
    $formType = $_POST['form_type'] ?? 'General';
    $inst = $_POST['institution'] ?? $_POST['affiliation'] ?? '';
    $email = $_POST['email'] ?? '';

    // Name Logic
    $firstName = $_POST['firstName'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $name = trim("$firstName $surname");
    
    if (empty($name)) {
        $name = $_POST['organiser_name'] ?? $_POST['name'] ?? 'Unknown_User';
    }

    // --- Abstract File Handling ---
    $abstractFilename = "None"; // Default if no abstract
    
    if (!empty($_POST['abstract'])) {
        // Create a safe filename: Name_Timestamp.txt
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $name));
        $timestamp = date('Ymd_His');
        $abstractFilename = "{$safeName}_{$timestamp}.txt";
        
        // Prepare content for the text file
        $fullContent = "Name: $name\nDate: $date\nEmail: $email\nTitle: " . ($_POST['title'] ?? '') . "\n\n=== ABSTRACT ===\n\n" . $_POST['abstract'];
        
        // Save the file
        file_put_contents("$abstractsDir/$abstractFilename", $fullContent);
    }

    // --- Other Details for CSV ---
    $details = [];
    if (!empty($_POST['yri_status'])) $details[] = "YRI: " . ($_POST['yri_status'] == 'true' ? 'Yes' : 'No');
    if (!empty($_POST['is_member']))  $details[] = "Member: " . ($_POST['is_member'] == 'true' ? 'Yes' : 'No');
    if (!empty($_POST['role']))       $details[] = "Role: " . $_POST['role'];
    if (!empty($_POST['type']))       $details[] = "Format: " . $_POST['type'];
    if (!empty($_POST['presence']))   $details[] = "Presence: " . $_POST['presence'];
    if (!empty($_POST['title']))      $details[] = "Title: " . $_POST['title'];
    
    // Flatten details for one cell
    $detailsString = implode(" | ", $details);

    // --- Write to CSV ---
    // We add the $abstractFilename to the CSV so you know which file corresponds to which row
    fputcsv($handle, array($date, $formType, $name, $inst, $email, $abstractFilename, $detailsString), ";");
    fclose($handle);

    echo "OK";
}
?>