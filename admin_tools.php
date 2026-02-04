<?php
// admin_tools.php - Client & Admin Dashboard
// Allows downloading data and resetting the system.

$message = "";
$csvFile = 'registrations_2026.csv';
$abstractsDir = 'abstracts';

// --- RESET LOGIC (Admin Action) ---
if (isset($_POST['action']) && $_POST['action'] == 'reset') {
    $backupDir = 'backups';
    if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

    $timestamp = date('Y-m-d_H-i-s');
    
    // 1. Archive CSV
    if (file_exists($csvFile)) {
        rename($csvFile, "$backupDir/registrations_$timestamp.csv");
    }

    // 2. Archive Abstracts folder
    if (is_dir($abstractsDir)) {
        rename($abstractsDir, "$backupDir/abstracts_$timestamp");
    }

    // 3. Recreate empty files
    $handle = fopen($csvFile, 'w');
    fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
    fputcsv($handle, array('Date', 'Form Type', 'Name', 'Institution', 'Email', 'Abstract File', 'Details'), ";");
    fclose($handle);
    
    mkdir($abstractsDir, 0755, true);

    $message = "✅ Success! Data has been archived to the 'backups' folder. The system is now reset.";
}

// --- FILE DISPLAY LOGIC ---
$abstractFiles = glob("$abstractsDir/*.txt");
$csvExists = file_exists($csvFile);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PlantWallK - Admin Dashboard</title>
    <style>
        body { font-family: -apple-system, system-ui, sans-serif; background: #f3f4f6; color: #1f2937; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; display: grid; gap: 2rem; }
        
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; color: #111827; border-bottom: 2px solid #f3f4f6; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        
        /* Buttons and Links */
        .btn-download { 
            display: inline-block; background: #2563eb; color: white; text-decoration: none; 
            padding: 12px 20px; border-radius: 6px; font-weight: 600; transition: background 0.2s;
        }
        .btn-download:hover { background: #1d4ed8; }
        
        .btn-danger { 
            background: #dc2626; color: white; border: none; padding: 12px 24px; 
            border-radius: 6px; font-weight: 600; cursor: pointer; width: 100%; font-size: 1rem;
        }
        .btn-danger:hover { background: #b91c1c; }

        /* Abstract List */
        .file-list { list-style: none; padding: 0; margin: 0; max-height: 300px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; }
        .file-list li { border-bottom: 1px solid #e5e7eb; }
        .file-list li:last-child { border-bottom: none; }
        .file-list a { 
            display: block; padding: 10px 15px; text-decoration: none; color: #4b5563; 
            display: flex; justify-content: space-between; align-items: center;
        }
        .file-list a:hover { background: #f9fafb; color: #2563eb; }
        .file-tag { font-size: 0.75rem; background: #e5e7eb; padding: 2px 6px; border-radius: 4px; }

        .alert { background: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .empty-msg { color: #9ca3af; font-style: italic; text-align: center; padding: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        
        <?php if($message): ?>
            <div class="alert"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>📂 Registrations & Abstracts</h2>
            
            <div style="margin-bottom: 2rem;">
                <h3>1. Master Excel File</h3>
                <p>Contains all registrations and details.</p>
                <?php if($csvExists): ?>
                    <a href="<?php echo $csvFile; ?>" class="btn-download">⬇️ Download Excel File (.csv)</a>
                <?php else: ?>
                    <p class="empty-msg">No registrations yet.</p>
                <?php endif; ?>
            </div>

            <div>
                <h3>2. Abstracts (Text Files)</h3>
                <p>List of automatically generated files:</p>
                
                <?php if(count($abstractFiles) > 0): ?>
                    <ul class="file-list">
                        <?php foreach($abstractFiles as $file): ?>
                            <li>
                                <a href="<?php echo $file; ?>" target="_blank">
                                    <span>📄 <?php echo basename($file); ?></span>
                                    <span class="file-tag">View</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="file-list">
                        <p class="empty-msg">No abstracts received.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid #dc2626;">
            <h2 style="color: #dc2626; border-color: #fee2e2;">⚠️ Admin Zone</h2>
            <p>Use this button to <strong>archive</strong> current data and <strong>reset</strong> the system for a new campaign.</p>
            
            <form method="POST" onsubmit="return confirm('Are you sure? Current data will be moved to archives.');">
                <input type="hidden" name="action" value="reset">
                <button type="submit" class="btn-danger">📦 Archive & Reset System</button>
            </form>
        </div>

    </div>
</body>
</html>