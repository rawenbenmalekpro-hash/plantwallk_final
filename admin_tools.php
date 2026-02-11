<?php
session_start();

// =================================================================
// 🔒 SECURITY CONFIGURATION
// =================================================================

// PASTE YOUR GENERATED HASH BELOW (Keep the single quotes!)
// Example: $MASTER_SECRET_HASH = '$2y$10$wK9...';
$MASTER_SECRET_HASH = 'PASTE_YOUR_HASH_HERE';

// =================================================================

$dataDir = 'secure_data';
$usersFile = "$dataDir/users.json";
$invitesFile = "$dataDir/invites.json";

// Ensure secure storage exists
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    // Block all access to data files
    file_put_contents("$dataDir/.htaccess", "Deny from all");
}

// Data Helpers
function getData($f) { return file_exists($f) ? json_decode(file_get_contents($f), true) : []; }
function saveData($f, $d) { file_put_contents($f, json_encode($d)); }

// --- LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_tools.php");
    exit;
}

// --- INITIAL SETUP (First Time Only) ---
$users = getData($usersFile);
if (empty($users)) {
    // Step 1: Validate Master Key
    if (isset($_POST['setup_key'])) {
        if (password_verify($_POST['setup_key'], $MASTER_SECRET_HASH)) {
            $_SESSION['setup_verified'] = true;
        } else {
            $error = "⛔ Incorrect Master Key.";
        }
    }

    // Step 2: Create Master Account
    if (isset($_SESSION['setup_verified']) && isset($_POST['master_email']) && isset($_POST['master_pass'])) {
        $email = trim($_POST['master_email']);
        $pass = password_hash($_POST['master_pass'], PASSWORD_DEFAULT);
        
        $users[$email] = $pass;
        saveData($usersFile, $users);
        
        $_SESSION['user'] = $email;
        unset($_SESSION['setup_verified']);
        header("Location: admin_tools.php");
        exit;
    }
    
    // SETUP SCREEN
    ?>
    <!DOCTYPE html><html lang="en"><head><title>System Setup</title><style>body{font-family:sans-serif;background:#f3f4f6;display:flex;justify-content:center;align-items:center;height:100vh;}.box{background:white;padding:2rem;border-radius:12px;width:400px;box-shadow:0 10px 15px rgba(0,0,0,0.1);}input{width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}button{width:100%;padding:10px;background:#166534;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;}</style></head>
    <body>
        <div class="box">
            <h2 style="color:#166534;margin-top:0;">🌱 System Initialization</h2>
            <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
            
            <?php if(!isset($_SESSION['setup_verified'])): ?>
                <p>Please enter the <strong>Master Secret Key</strong> defined in the code to claim ownership.</p>
                <form method="POST">
                    <input type="password" name="setup_key" placeholder="Enter Master Key" required autofocus>
                    <button type="submit">Verify Identity</button>
                </form>
            <?php else: ?>
                <p style="color:green;font-weight:bold;">Identity Verified.</p>
                <p>Create the Master Admin account:</p>
                <form method="POST">
                    <input type="email" name="master_email" placeholder="Your Email Address" required>
                    <input type="password" name="master_pass" placeholder="Create Password" required>
                    <button type="submit">Initialize System</button>
                </form>
            <?php endif; ?>
        </div>
    </body></html>
    <?php
    exit;
}

// --- HANDLE INVITE LINKS ---
if (isset($_GET['invite'])) {
    $token = $_GET['invite'];
    $invites = getData($invitesFile);
    
    // Check if token exists and is valid (optional: add expiry logic here)
    if (isset($invites[$token])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_email']) && isset($_POST['new_pass'])) {
            // Create the new user
            $email = trim($_POST['new_email']);
            // Check if email already exists
            if (isset($users[$email])) {
                $error = "User already exists.";
            } else {
                $users[$email] = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
                saveData($usersFile, $users);
                
                // Burn the token (One-time use)
                unset($invites[$token]);
                saveData($invitesFile, $invites);
                
                $_SESSION['user'] = $email;
                header("Location: admin_tools.php");
                exit;
            }
        }
        
        // SHOW INVITE ACCEPT SCREEN
        ?>
        <!DOCTYPE html><html lang="en"><head><title>Accept Invitation</title><style>body{font-family:sans-serif;background:#f3f4f6;display:flex;justify-content:center;align-items:center;height:100vh;}.box{background:white;padding:2rem;border-radius:12px;width:400px;box-shadow:0 10px 15px rgba(0,0,0,0.1);}input{width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}button{width:100%;padding:10px;background:#2563eb;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;}</style></head>
        <body>
            <div class="box">
                <h2 style="color:#2563eb;margin-top:0;">📩 Admin Invitation</h2>
                <p>You have been invited to manage PlantWallK.</p>
                <p>Please set up your credentials:</p>
                <?php if(isset($error)) echo "<p style='color:red'>$error</p>"; ?>
                <form method="POST">
                    <input type="email" name="new_email" placeholder="Your Email" required>
                    <input type="password" name="new_pass" placeholder="Choose a Password" required>
                    <button type="submit">Create Account</button>
                </form>
            </div>
        </body></html>
        <?php
        exit;
    } else {
        die("Invalid or expired invitation link.");
    }
}

// --- LOGIN SCREEN ---
if (!isset($_SESSION['user'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $u = $_POST['email'] ?? '';
        $p = $_POST['password'] ?? '';
        if (isset($users[$u]) && password_verify($p, $users[$u])) {
            $_SESSION['user'] = $u;
            header("Location: admin_tools.php");
            exit;
        } else {
            $error = "Invalid login.";
        }
    }
    ?>
    <!DOCTYPE html><html lang="en"><head><title>Admin Login</title><style>body{font-family:sans-serif;background:#f3f4f6;display:flex;justify-content:center;align-items:center;height:100vh;}.box{background:white;padding:2rem;border-radius:12px;width:350px;box-shadow:0 10px 15px rgba(0,0,0,0.1);}input{width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:4px;box-sizing:border-box;}button{width:100%;padding:10px;background:#166534;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;}</style></head>
    <body>
        <div class="box">
            <h2 style="color:#166534;text-align:center;">🔐 PlantWallK Login</h2>
            <?php if(isset($error)) echo "<div style='color:red;text-align:center;margin-bottom:10px'>$error</div>"; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Log In</button>
            </form>
        </div>
    </body></html>
    <?php
    exit;
}

// =================================================================
// 🚀 DASHBOARD (Logged In)
// =================================================================

// GENERATE INVITE LINK
$newLink = "";
if (isset($_POST['create_link'])) {
    // Generate a cryptographically secure random token
    $token = bin2hex(random_bytes(24));
    $invites = getData($invitesFile);
    $invites[$token] = time(); // Store creation time
    saveData($invitesFile, $invites);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $newLink = "$protocol://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?invite=$token";
}

function getRegFiles($event) { return glob("registrations/$event/abstracts/*.html"); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PlantWallK Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: -apple-system, system-ui, sans-serif; background: #f9fafb; padding: 20px; color:#333; }
        .header { display: flex; justify-content: space-between; align-items: center; background:white; padding:15px 20px; border-radius:8px; box-shadow:0 2px 4px rgba(0,0,0,0.05); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-top: 4px solid #166534; }
        .btn { display: inline-block; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; cursor:pointer; border:none; margin-left:5px; }
        .btn-blue { background: #eff6ff; color: #1e40af; } .btn-blue:hover { background: #dbeafe; }
        .btn-green { background: #f0fdf4; color: #166534; } .btn-green:hover { background: #dcfce7; }
        .btn-gray { background: #f3f4f6; color: #4b5563; }
        .logout { color: #dc2626; text-decoration: none; font-weight: bold; margin-left: 15px; }
        ul { list-style: none; padding: 0; max-height: 400px; overflow-y: auto; }
        li { padding: 10px 0; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items:center; }
        input[type="text"] { padding:8px; border:1px solid #ddd; border-radius:4px; width:100%; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <h1 style="margin:0; font-size:1.5rem;">🌱 Admin Dashboard</h1>
            <span style="font-size:0.9rem; color:#666;">User: <strong><?php echo $_SESSION['user']; ?></strong></span>
        </div>
        <div>
            <form method="POST" style="display:inline;">
                <button type="submit" name="create_link" class="btn btn-green" style="padding:10px;">➕ Generate Invite Link</button>
            </form>
            <a href="?logout=true" class="logout">Logout</a>
        </div>
    </div>

    <?php if($newLink): ?>
    <div style="background:#dcfce7; border:1px solid #bbf7d0; padding:15px; margin-top:20px; border-radius:8px;">
        <h3 style="margin-top:0; color:#166534;">🎉 Secure Invite Link Created</h3>
        <p style="margin-bottom:5px;">Copy and email this link to your collaborator. They can use it to create their own login.</p>
        <input type="text" value="<?php echo $newLink; ?>" onclick="this.select()" readonly>
    </div>
    <?php endif; ?>

    <div class="grid">
        <div class="card">
            <h2>🇨🇿 Prague Meeting</h2>
            <div style="margin-bottom: 15px;">
                <?php if(file_exists('registrations/prague/list.csv')): ?>
                    <a href="registrations/prague/list.csv" class="btn btn-blue">⬇️ Download CSV</a>
                <?php else: ?>
                    <span style="color:#999">No registrations.</span>
                <?php endif; ?>
            </div>
            <ul>
                <?php 
                foreach(getRegFiles('prague') as $f) {
                    $name = str_replace('_', ' ', preg_replace('/_\d{8}_\d{6}/', '', basename($f, '.html')));
                    $txt = str_replace('.html', '.txt', $f);
                    echo "<li><strong>$name</strong><div>
                        <a href='$txt' target='_blank' class='btn btn-gray'>TXT</a>
                        <a href='$f' target='_blank' class='btn btn-blue'>View</a>
                        <button onclick=\"dlPDF('$f','$name')\" class='btn btn-green'>PDF</button>
                    </div></li>";
                } 
                ?>
            </ul>
        </div>

        <div class="card" style="border-top-color: #0ea5e9;">
            <h2>🇪🇺 EPCC Satellite</h2>
            <div style="margin-bottom: 15px;">
                <?php if(file_exists('registrations/epcc/list.csv')): ?>
                    <a href="registrations/epcc/list.csv" class="btn btn-blue">⬇️ Download CSV</a>
                <?php else: ?>
                    <span style="color:#999">No registrations.</span>
                <?php endif; ?>
            </div>
            <ul>
                <?php 
                foreach(getRegFiles('epcc') as $f) {
                    $name = str_replace('_', ' ', preg_replace('/_\d{8}_\d{6}/', '', basename($f, '.html')));
                    $txt = str_replace('.html', '.txt', $f);
                    echo "<li><strong>$name</strong><div>
                        <a href='$txt' target='_blank' class='btn btn-gray'>TXT</a>
                        <a href='$f' target='_blank' class='btn btn-blue'>View</a>
                        <button onclick=\"dlPDF('$f','$name')\" class='btn btn-green'>PDF</button>
                    </div></li>";
                } 
                ?>
            </ul>
        </div>
    </div>

    <div id="pdf-box" style="display:none;"></div>
    <script>
    async function dlPDF(url, name) {
        try {
            const res = await fetch(url);
            const html = await res.text();
            const box = document.getElementById('pdf-box');
            box.innerHTML = html;
            box.style.display = 'block';
            await html2pdf().set({ margin:10, filename: name+'.pdf', html2canvas:{scale:2} }).from(box).save();
            box.style.display = 'none'; box.innerHTML = '';
        } catch(e) { alert('PDF Error: '+e); }
    }
    </script>
</body>
</html>