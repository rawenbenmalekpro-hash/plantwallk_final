<?php
// submit.php - Réception des formulaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nom du fichier qui contiendra les données (changez l'année si besoin)
    $file = 'inscriptions_site_2026.csv';

    // Ouvre le fichier
    $handle = fopen($file, 'a');

    // Si le fichier est vide, on écrit la première ligne (les titres)
    if (filesize($file) == 0) {
        // Ajout du BOM pour que Excel lise bien les accents (UTF-8)
        fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($handle, array('Date', 'Type Formulaire', 'Nom', 'Institution', 'Email', 'Details'), ";");
    }

    // Récupération des données du formulaire
    $date = date('Y-m-d H:i:s');
    $formType = $_POST['form_type'] ?? 'General';
    $name = $_POST['organiser_name'] ?? $_POST['name'] ?? $_POST['full_name'] ?? ''; // Supporte différents noms de champs
    $inst = $_POST['institution'] ?? $_POST['affiliation'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // On rassemble le reste dans une colonne "Détails"
    $details = "";
    if (isset($_POST['location'])) $details .= "Lieu: " . $_POST['location'] . " | ";
    if (isset($_POST['topic'])) $details .= "Sujet: " . $_POST['topic'] . " | ";
    if (isset($_POST['dietary'])) $details .= "Diététique: " . $_POST['dietary'] . " | ";

    // Écriture de la ligne dans le fichier CSV (séparateur point-virgule pour Excel fr)
    fputcsv($handle, array($date, $formType, $name, $inst, $email, $details), ";");
    fclose($handle);

    // Redirection vers l'accueil après l'envoi
    echo "<script>alert('Merci ! Votre inscription a bien été reçue.'); window.location.href='index.html';</script>";
}
?>