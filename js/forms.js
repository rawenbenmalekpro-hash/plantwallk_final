/* js/forms.js — Gestionnaire de formulaire & Fond d'écran */

async function submitForm(event, type) {
  event.preventDefault();
  const form = event.target;
  const btn = form.querySelector('button[type="submit"]');
  const msg = form.querySelector('.form-message');
  
  // 1. Verrouiller l'interface pendant l'envoi
  const originalText = btn.innerText;
  btn.innerText = 'Envoi en cours...';
  btn.disabled = true;
  msg.hidden = true;
  msg.className = 'form-message'; 

  // 2. Récupérer les données (FormData gère tout automatiquement)
  const formData = new FormData(form);

  // 3. Envoyer à submit.php (qui est sur le même serveur)
  try {
    const response = await fetch('submit.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) throw new Error('Erreur serveur');

    // 4. Succès
    msg.innerText = 'Inscription réussie ! Nous avons bien reçu votre résumé.';
    msg.classList.add('success');
    msg.hidden = false;
    form.reset();
    
    // Remettre le bouton après 3 secondes
    setTimeout(() => {
        btn.innerText = originalText;
        btn.disabled = false;
    }, 3000);

  } catch (error) {
    // 5. Erreur
    console.error(error);
    msg.innerText = 'Erreur lors de l\'envoi. Veuillez réessayer ou nous contacter.';
    msg.classList.add('error');
    msg.hidden = false;
    btn.innerText = originalText;
    btn.disabled = false;
  }
}

/* ======== Gestion du Diaporama (Background Cycle) ======== */
function initBackgroundCycle() {
  // 1. Liste des images
  const images = [
    './images/Jan%20Martinek%201.jpg',
    './images/Jan%20Martinek%202.jpg',
    './images/Jan%20Martinek%203.jpg',
    './images/Petra%20Cifrova%201.jpg',
    './images/Petra%20Cifrova%202.jpg'
  ];

  // 2. Créer le conteneur du fond
  const container = document.createElement('div');
  container.id = 'bg-cycler';
  Object.assign(container.style, {
    position: 'fixed', inset: 0, zIndex: -1,
    background: '#020617' // Fond noir par défaut
  });

  // 3. Fonction pour créer une "couche" d'image
  const createLayer = (src, initialOpacity) => {
    const layer = document.createElement('div');
    Object.assign(layer.style, {
      position: 'absolute', inset: 0,
      backgroundSize: 'cover', backgroundPosition: 'center',
      backgroundImage: `url('${src}')`,
      opacity: initialOpacity,
      transition: 'opacity 1.5s ease-in-out',
      filter: 'brightness(0.5)' // Assombrit légèrement pour lire le texte
    });
    return layer;
  };

  // 4. Initialisation des calques
  let currentIndex = 0;
  let layerBack = createLayer(images[0], 1); // Visible
  let layerFront = createLayer(images[1], 0); // Invisible

  container.appendChild(layerBack);
  container.appendChild(layerFront);
  
  // Insérer au tout début du body
  document.body.prepend(container);

  // 5. Lancer le cycle (toutes les 5 secondes)
  setInterval(() => {
    const nextIndex = (currentIndex + 1) % images.length;
    
    // Charger la nouvelle image sur le calque avant
    layerFront.style.backgroundImage = `url('${images[nextIndex]}')`;
    layerFront.style.opacity = 1;

    // Une fois la transition finie (1.5s), on échange les rôles
    setTimeout(() => {
      layerBack.style.backgroundImage = layerFront.style.backgroundImage;
      layerFront.style.opacity = 0;
      currentIndex = nextIndex;
    }, 1500);
  }, 5000);
}

// Lancer le script quand la page est chargée
document.addEventListener('DOMContentLoaded', initBackgroundCycle);