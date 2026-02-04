/* js/forms.js — Form Handler & Background Wallpaper */

async function submitForm(event, type) {
  event.preventDefault();
  const form = event.target;
  const btn = form.querySelector('button[type="submit"]');
  const msg = form.querySelector('.form-message');
  
  // 1. Lock UI during submission
  const originalText = btn.innerText;
  btn.innerText = 'Sending...';
  btn.disabled = true;
  msg.hidden = true;
  msg.className = 'form-message'; 

  // 2. Retrieve data (FormData handles everything automatically)
  const formData = new FormData(form);

  // 3. Send to submit.php (hosted on the same server)
  try {
    const response = await fetch('submit.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) throw new Error('Server error');

    // 4. Success
    msg.innerText = 'Registration successful! We have received your abstract.';
    msg.classList.add('success');
    msg.hidden = false;
    form.reset();
    
    // Reset button after 3 seconds
    setTimeout(() => {
        btn.innerText = originalText;
        btn.disabled = false;
    }, 3000);

  } catch (error) {
    // 5. Error
    console.error(error);
    msg.innerText = 'Error sending form. Please try again or contact us.';
    msg.classList.add('error');
    msg.hidden = false;
    btn.innerText = originalText;
    btn.disabled = false;
  }
}

/* ======== Slideshow Management (Background Cycle) ======== */
function initBackgroundCycle() {
  // 1. Image list
  const images = [
    './images/Jan%20Martinek%201.jpg',
    './images/Jan%20Martinek%202.jpg',
    './images/Jan%20Martinek%203.jpg',
    './images/Petra%20Cifrova%201.jpg',
    './images/Petra%20Cifrova%202.jpg'
  ];

  // 2. Create background container
  const container = document.createElement('div');
  container.id = 'bg-cycler';
  Object.assign(container.style, {
    position: 'fixed', inset: 0, zIndex: -1,
    background: '#020617' // Default black background
  });

  // 3. Helper function to create an image layer
  const createLayer = (src, initialOpacity) => {
    const layer = document.createElement('div');
    Object.assign(layer.style, {
      position: 'absolute', inset: 0,
      backgroundSize: 'cover', backgroundPosition: 'center',
      backgroundImage: `url('${src}')`,
      opacity: initialOpacity,
      transition: 'opacity 1.5s ease-in-out',
      filter: 'brightness(0.5)' // Slightly darken to make text readable
    });
    return layer;
  };

  // 4. Initialize layers
  let currentIndex = 0;
  let layerBack = createLayer(images[0], 1); // Visible
  let layerFront = createLayer(images[1], 0); // Invisible

  container.appendChild(layerBack);
  container.appendChild(layerFront);
  
  // Insert at the very beginning of the body
  document.body.prepend(container);

  // 5. Start cycle (every 5 seconds)
  setInterval(() => {
    const nextIndex = (currentIndex + 1) % images.length;
    
    // Load new image onto the front layer
    layerFront.style.backgroundImage = `url('${images[nextIndex]}')`;
    layerFront.style.opacity = 1;

    // Once transition is finished (1.5s), swap roles
    setTimeout(() => {
      layerBack.style.backgroundImage = layerFront.style.backgroundImage;
      layerFront.style.opacity = 0;
      currentIndex = nextIndex;
    }, 1500);
  }, 5000);
}

// Run script when DOM is loaded
document.addEventListener('DOMContentLoaded', initBackgroundCycle);