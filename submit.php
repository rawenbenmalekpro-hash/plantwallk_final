<?php
// submit.php - Form Handler
// Saves data to 'registrations_2026.csv'

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file = 'registrations_2026.csv';

    // Open file in append mode
    $handle = fopen($file, 'a');

    // Create headers if file is new
    if (filesize($file) == 0) {
        // Add BOM for Excel UTF-8 compatibility
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, array('Date', 'Form Type', 'Name', 'Institution', 'Email', 'Details'), ";");
    }

    // 1. Common Data
    $date = date('Y-m-d H:i:s');
    $formType = $_POST['form_type'] ?? 'General';
    $inst = $_POST['institution'] ?? $_POST['affiliation'] ?? '';
    $email = $_POST['email'] ?? '';

    // 2. Name Logic (Handle both Registration and Workshop forms)
    $firstName = $_POST['firstName'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $name = trim("$firstName $surname");
    
    if (empty($name)) {
        // Fallback for Workshop form which uses 'organiser_name'
        $name = $_POST['organiser_name'] ?? $_POST['name'] ?? '';
    }

    // 3. Dynamic Details (Capture everything else)
    $details = [];

    // Registration Fields
    if (!empty($_POST['yri_status'])) $details[] = "YRI Status: " . ($_POST['yri_status'] == 'true' ? 'Yes' : 'No');
    if (!empty($_POST['is_member']))  $details[] = "Member: " . ($_POST['is_member'] == 'true' ? 'Yes' : 'No');
    if (!empty($_POST['role']))       $details[] = "Role: " . $_POST['role'];
    if (!empty($_POST['type']))       $details[] = "Preference/Format: " . $_POST['type'];
    if (!empty($_POST['presence']))   $details[] = "Presence: " . $_POST['presence'];
    if (!empty($_POST['title']))      $details[] = "Title: " . $_POST['title'];
    
    // Workshop Fields
    if (!empty($_POST['location']))      $details[] = "Location: " . $_POST['location'];
    if (!empty($_POST['topic']))         $details[] = "Topic: " . $_POST['topic'];
    if (!empty($_POST['duration_days'])) $details[] = "Duration: " . $_POST['duration_days'] . " days";

    // Handle Abstract / Description (Clean up newlines for CSV)
    if (!empty($_POST['abstract'])) {
        $cleanAbstract = str_replace(["\r", "\n", ";"], " ", $_POST['abstract']);
        $details[] = "Abstract: " . substr($cleanAbstract, 0, 100) . "... (see full text)"; 
        // Note: We truncate abstract in CSV summary to keep it readable, 
        // but you might want to save the full abstract to a separate file or database later.
    }
    if (!empty($_POST['description'])) {
         $cleanDesc = str_replace(["\r", "\n", ";"], " ", $_POST['description']);
         $details[] = "Description: " . $cleanDesc;
    }

    // Convert details array to string
    $detailsString = implode(" | ", $details);

    // 4. Write to CSV
    fputcsv($handle, array($date, $formType, $name, $inst, $email, $detailsString), ";");
    fclose($handle);

    // Response for JS
    echo "OK";
}
?>