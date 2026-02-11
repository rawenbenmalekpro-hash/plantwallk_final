<?php
// submit.php - Professional Handler
// Saves: CSV (List), HTML (Summary), TXT (Abstract)

// --- CONFIGURATION ---
$contactEmail = "contact@plantwallk.org";
$senderEmail = "no-reply@plantwallk.org"; 

date_default_timezone_set('Europe/Paris');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. SETUP PATHS
    $rawType = $_POST['form_type'] ?? 'General';
    
    // Determine Folder (prague vs epcc)
    if (stripos($rawType, 'Prague') !== false) $eventSlug = 'prague';
    elseif (stripos($rawType, 'EPCC') !== false) $eventSlug = 'epcc';
    else $eventSlug = 'other';

    $baseDir = "registrations/$eventSlug";
    $abstractsDir = "$baseDir/abstracts"; // Stores .html and .txt
    
    // Create Secure Directory
    if (!is_dir($abstractsDir)) {
        mkdir($abstractsDir, 0755, true);
        file_put_contents("registrations/.htaccess", "Options -Indexes\nDeny from all");
    }

    // 2. DATA COLLECTION
    $date = date('Y-m-d H:i:s');
    $firstName = $_POST['firstName'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $name = trim("$firstName $surname");
    if (empty($name)) $name = $_POST['organiser_name'] ?? 'Unknown';
    $email = $_POST['email'] ?? '';
    $institution = $_POST['institution'] ?? $_POST['affiliation'] ?? '';
    $abstractText = $_POST['abstract'] ?? $_POST['description'] ?? '';

    // Create a safe filename ID
    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', str_replace(' ', '_', $name));
    $fileId = "{$safeName}_" . date('Ymd_His');

    // 3. GENERATE FILES

    // A. CSV (Excel List)
    $csvFile = "$baseDir/list.csv";
    $handle = fopen($csvFile, 'a');
    if (filesize($csvFile) == 0) {
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM
        fputcsv($handle, array('Date', 'Name', 'Email', 'Institution', 'Title', 'Format', 'Details'), ";");
    }
    
    // Build Details String
    $details = [];
    $fields = ['yri_status', 'is_member', 'role', 'type', 'presence', 'title', 'location', 'topic'];
    foreach ($fields as $f) {
        if (!empty($_POST[$f])) {
            $val = $_POST[$f] === 'true' ? 'Yes' : ($_POST[$f] === 'false' ? 'No' : $_POST[$f]);
            $details[] = ucfirst($f) . ": $val";
        }
    }
    $detailsStr = implode(" | ", $details);
    
    fputcsv($handle, array($date, $name, $email, $institution, $_POST['title']??'', $_POST['type']??'', $detailsStr), ";");
    fclose($handle);

    // B. TXT (Raw Abstract)
    $txtContent = "Name: $name\nDate: $date\nEmail: $email\nInstitution: $institution\n\n=== DETAILS ===\n$detailsStr\n\n=== ABSTRACT ===\n$abstractText";
    file_put_contents("$abstractsDir/$fileId.txt", $txtContent);

    // C. HTML (For Dashboard & PDF)
    $htmlContent = "
    <div class='reg-summary'>
        <h2 style='color:#166534; border-bottom:2px solid #ccc; padding-bottom:10px;'>$rawType</h2>
        <p><strong>Name:</strong> $name</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Institution:</strong> $institution</p>
        <p><strong>Date:</strong> $date</p>
        <hr>
        <h3>Details</h3>
        <p>$detailsStr</p>
        <hr>
        <h3>Abstract</h3>
        <div style='background:#f9f9f9; padding:15px; border-left:4px solid #166534;'>
            " . nl2br(htmlspecialchars($abstractText)) . "
        </div>
    </div>";
    file_put_contents("$abstractsDir/$fileId.html", $htmlContent);

    // 4. SEND EMAIL
    if (!empty($email)) {
        $subject = "Registration Confirmed: $rawType";
        $headers = "MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: PlantWallK <$senderEmail>\r\nReply-To: $contactEmail\r\n";
        
        $mailBody = "
        <p>Dear $name,</p>
        <p>We have received your registration for <strong>$rawType</strong>.</p>
        $htmlContent
        <p><br>Best regards,<br>PlantWallK Team</p>";

        mail($email, $subject, $mailBody, $headers);
    }

    echo "OK";
}
?>