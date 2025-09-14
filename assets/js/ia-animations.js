const canvas = document.getElementById('ia-animation');
const ctx = canvas.getContext('2d');
canvas.width = canvas.clientWidth;
canvas.height = canvas.clientHeight;
// version avec api python
let currentStep = 0;
let steps = [];

// Fonction pour afficher les étapes
function displayStep(step) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = 'white';
    ctx.font = '20px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(step, canvas.width / 2, canvas.height / 2);
}

// Exemple de déclenchement
document.getElementById('chatbot-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        const question = e.target.value.trim();
        if (question) {
            e.target.value = '';
            fetchProgress(question);
        }
    }
});

function resizeCanvas() {
    canvas.width = canvas.clientWidth;
    canvas.height = canvas.clientHeight;
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas(); // Appelle au démarrage

function fetchProgress(question) {
    fetch('/api/chatbot/analyze', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ question: question })
    })
        .then(response => response.text()) // Récupère la réponse brute
        .then(rawData => {

            // Filtrer pour ne garder que la partie JSON valide
            const startIndex = rawData.indexOf('{');
            const endIndex = rawData.lastIndexOf('}');
            if (startIndex !== -1 && endIndex !== -1) {
                const jsonPart = rawData.slice(startIndex, endIndex + 1);
                try {
                    const data = JSON.parse(jsonPart);
                    console.log("Données JSON :", data);
                    simulateProgressWithAnimations(data);
                } catch (err) {
                    console.error("Erreur de parsing JSON :", err, jsonPart);
                }
            } else {
                console.error("JSON invalide dans la réponse :", rawData);
            }
        })
        .catch(error => console.error('Erreur lors de la récupération des étapes:', error));
}

// Afficher la barre de progression
function displayProgressBar(currentStep, totalSteps) {
    ctx.clearRect(0, canvas.height - 20, canvas.width, 20); // Nettoyer l'espace dédié à la barre
    ctx.fillStyle = '#333';
    ctx.fillRect(0, canvas.height - 20, canvas.width, 20);

    ctx.fillStyle = '#4caf50'; // Couleur de progression
    const progressWidth = (currentStep / totalSteps) * canvas.width;
    ctx.fillRect(0, canvas.height - 20, progressWidth, 20);

    ctx.fillStyle = 'white';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`Étape ${currentStep} / ${totalSteps}`, canvas.width / 2, canvas.height - 5);
}

// Afficher le nom de l'étape courante
function displayCurrentStep(stepName) {
    ctx.clearRect(0, 0, canvas.width, 50); // Nettoyer l'espace réservé au nom de l'étape
    ctx.fillStyle = 'yellow';
    ctx.font = '18px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(stepName, canvas.width / 2, 30);
}

function displayKeywords(keywords) {
    ctx.clearRect(0, 50, canvas.width, canvas.height-70);

    const angleStep = (2 * Math.PI) / keywords.length;
    keywords.forEach((keyword, index) => {
        const angle = index * angleStep;
        const x = canvas.width / 2 + Math.cos(angle) * 150; // Rayon du cercle
        const y = canvas.height / 2 + Math.sin(angle) * 150;

        ctx.fillStyle = 'white';
        ctx.font = '20px Arial';
        ctx.textAlign = "center";
        ctx.fillText(keyword, x, y);
    });

    const keywordParticles = keywords.map((keyword, index) => ({
        text: keyword,
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        opacity: 0,
        size: 20 + Math.random() * 10,
        targetX: canvas.width / 2,
        targetY: canvas.height / 2 - (keywords.length / 2 - index) * 30,
    }));

    function animateKeywords() {
        ctx.clearRect(0, 50, canvas.width, canvas.height-70);

        keywordParticles.forEach((particle) => {
            // Move towards target position
            particle.x += (particle.targetX - particle.x) * 0.05;
            particle.y += (particle.targetY - particle.y) * 0.05;
            particle.opacity += 0.02;

            // Draw keyword
            ctx.fillStyle = `rgba(255, 255, 255, ${particle.opacity})`;
            ctx.font = `${particle.size}px Arial`;
            ctx.textAlign = "center";
            ctx.fillText(particle.text, particle.x, particle.y);
        });

        if (keywordParticles.some(p => p.opacity < 1)) {
            requestAnimationFrame(animateKeywords);
        }
    }

    animateKeywords();
}

function displayConceptMap(concepts) {

    if (!Array.isArray(concepts) || concepts.length === 0) {
        console.warn("Aucun concept valide pour l'animation. Concepts reçus :", concepts);
        return;
    }

    ctx.clearRect(0, 50, canvas.width, canvas.height-70);

    const nodes = concepts.map((concept, index) => ({
        text: concept,
        x: Math.max(50, Math.min(Math.random() * canvas.width, canvas.width - 50)), // Limite à 50px du bord
        y: Math.max(50, Math.min(Math.random() * canvas.height, canvas.height - 50)), // Limite à 50px du bord
        radius: 20 + Math.random() * 10,
        color: `hsl(${Math.random() * 360}, 100%, 50%)`,
    }));

    // Génération des connexions aléatoires entre les nœuds
    const connections = [];
    for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
            if (Math.random() > 0.5) { // Ajuster la probabilité de connexion
                connections.push({ from: nodes[i], to: nodes[j] });
            }
        }
    }

    let pulse = 0;
    let animationFrame;

    // Fonction d'animation des nœuds et connexions
    function drawNodes() {
        ctx.clearRect(0, 50, canvas.width, canvas.height - 70);

        // Dessiner les connexions avec effet lumineux
        connections.forEach(({ from, to }) => {
            ctx.strokeStyle = `rgba(255, 255, 255, ${Math.abs(Math.sin(pulse))})`;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(from.x, from.y);
            ctx.lineTo(to.x, to.y);
            ctx.stroke();
        });

        // Dessiner les nœuds
        nodes.forEach((node) => {
            ctx.fillStyle = node.color;
            ctx.beginPath();
            ctx.arc(node.x, node.y, node.radius, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();

            ctx.fillStyle = "white";
            ctx.font = "14px Arial";
            ctx.textAlign = "center";
            ctx.fillText(node.text, node.x, node.y + 5);
        });

        // Ajuster la pulsation
        pulse += 0.05;
        animationFrame = requestAnimationFrame(drawNodes);
    }

    drawNodes();

    setTimeout(() => cancelAnimationFrame(animationFrame), 3000); // Arrête l'animation après 3 secondes
}

function displayDefinitionAnimation(definition) {
   // const canvas = document.getElementById('ia-canvas');
  //  const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 50, canvas.width, canvas.height-70);
    if (!definition) {
        console.warn("Définition introuvable pour l'animation.");
        return;
    }
    let opacity = 0;

    function fadeIn() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.globalAlpha = opacity;
        ctx.fillStyle = 'yellow';
        ctx.font = '20px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(definition, canvas.width / 2, canvas.height / 2);
        ctx.globalAlpha = 1;

        if (opacity < 1) {
            opacity += 0.05;
            requestAnimationFrame(fadeIn);
        }
    }

    fadeIn();
}

function displayChatbotResponse(data) {
    const chatbotMessages = document.getElementById('chatbot-messages');

    const messageDiv = document.createElement('div');
    messageDiv.className = 'bot-response';
    messageDiv.innerText = data.response;

    chatbotMessages.appendChild(messageDiv);

    if (data.related_concepts.concepts && data.related_concepts.concepts.length > 0) {
        const conceptsDiv = document.createElement('div');
        conceptsDiv.className = 'bot-concepts';
        conceptsDiv.innerText = `Concepts associés : ${data.related_concepts.concepts.join(', ')}`;
        chatbotMessages.appendChild(conceptsDiv);
    }
    if (data.related_concepts.definition) {
        //Object.entries(data.related_concepts.definition).forEach(([keyword, definition]) => {
        const definitionDiv = document.createElement('div');
        definitionDiv.className = 'bot-definition';
        definitionDiv.innerText = `définition : ${data.related_concepts.definition}`;
        chatbotMessages.appendChild(definitionDiv);
    }

    chatbotMessages.scrollTop = chatbotMessages.scrollHeight; // Défiler jusqu'au bas
}

function displayCircuitAnimation() {
    ctx.clearRect(0, 50, canvas.width, canvas.height-70);

    const nodes = Array.from({ length: 20 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        isActive: false,
    }));

    const connections = [];
    for (let i = 0; i < nodes.length; i++) {
        for (let j = i + 1; j < nodes.length; j++) {
            if (Math.random() > 0.7) {
                connections.push({ from: nodes[i], to: nodes[j] });
            }
        }
    }

    let activeIndex = 0;

    function drawCircuit() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Dessiner les connexions
        connections.forEach(({ from, to }) => {
            ctx.strokeStyle = `rgba(0, 255, 0, 0.2)`;
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.moveTo(from.x, from.y);
            ctx.lineTo(to.x, to.y);
            ctx.stroke();
        });

        // Dessiner les nœuds
        nodes.forEach((node, index) => {
            ctx.fillStyle = node.isActive ? 'lime' : 'rgba(255, 255, 255, 0.5)';
            ctx.beginPath();
            ctx.arc(node.x, node.y, 5, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();

            if (index === activeIndex) {
                node.isActive = true;
            }
        });

        // Simuler un flux lumineux
        activeIndex = (activeIndex + 1) % nodes.length;

        requestAnimationFrame(drawCircuit);
    }

    drawCircuit();
}

function selectAnimation(data) {
    switch (data.type) {
        case 'technique':
            return () => displayCircuitAnimation();
        case 'philosophique':
            return () => displayKeywords(data.keywords);
        default:
            return () => displayAbstractAnimation(data.keywords);
    }
}

function displayAbstractAnimation() {
    ctx.clearRect(0, 50, canvas.width, canvas.height-70);

    let particles = Array.from({ length: 100 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        size: Math.random() * 5,
        speedX: Math.random() * 2 - 1,
        speedY: Math.random() * 2 - 1,
        opacity: Math.random(),
    }));

    function animateParticles() {
        ctx.clearRect(0, 50, canvas.width, canvas.height-70);

        particles.forEach((particle) => {
            particle.x += particle.speedX;
            particle.y += particle.speedY;

            if (particle.x < 0 || particle.x > canvas.width) particle.speedX *= -1;
            if (particle.y < 0 || particle.y > canvas.height) particle.speedY *= -1;

            ctx.fillStyle = `rgba(255, 255, 255, ${particle.opacity})`;
            ctx.beginPath();
            ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();
        });

        requestAnimationFrame(animateParticles);
    }

    animateParticles();
}

function displayFinalAssembly(data) {
    let radius = 0;
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;

    function animateAssembly() {
        ctx.clearRect(0, 50, canvas.width, canvas.height-70);

        // Dessine le cercle lumineux
        ctx.strokeStyle = `rgba(0, 255, 255, ${1 - radius / 300})`;
        ctx.lineWidth = 10;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
        ctx.stroke();

        // Effet de "halo"
        ctx.fillStyle = `rgba(0, 255, 255, ${0.5 - radius / 600})`;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius * 0.8, 0, Math.PI * 2);
        ctx.closePath();
        ctx.fill();

        radius += 5;

        if (radius < 300) {
            requestAnimationFrame(animateAssembly);
        } else {
            // Affiche la réponse finale
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.fillStyle = 'white';
            ctx.font = '24px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(data.response, centerX, centerY);
        }
    }

    animateAssembly();
}

function simulateProgressWithAnimations(data) {

    const animation = selectAnimation(data); // Choisir l'animation selon le type
    const animations = [
        animation, // Exécute l'animation spécifique
        () => {
            if (Array.isArray(data.related_concepts.concepts) && data.related_concepts.concepts.length > 0) {
                displayConceptMap(data.related_concepts.concepts);
            } else {
                console.warn("Aucun concept associé trouvé pour l'animation.");
            }
        },
        () => {
                const definition = data.related_concepts.definition || "Définition introuvable.";
                displayDefinitionAnimation(definition)
        },
        () => displayFinalAssembly(data),
    ];

    let currentAnimation = 0;

    function nextStep() {
        if (currentAnimation < animations.length) {
            // Affiche la progression et l'étape courante
            displayProgressBar(currentAnimation + 1, animations.length);
            displayCurrentStep(data.steps[currentAnimation]);

            animations[currentAnimation]();
            currentAnimation++;
            setTimeout(nextStep, 3000); // Temps entre les animations

        } else {
            // Afficher la réponse dans le chatbot
            displayChatbotResponse(data);
        }
    }

    nextStep();

    document.getElementById('skip-animation').addEventListener('click', () => {
        currentAnimation = animations.length; // Passer toutes les étapes
        displayChatbotResponse(data); // Affiche directement la réponse
    });
}



