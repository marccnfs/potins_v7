const suggestionsList = [
    ['Dernières actualités IA', 'Tendances cette semaine', 'Un article sur OpenAI'],
    ['Quoi de neuf en IA ?', 'Articles populaires', 'Un rapport sur la technologie'],
    ['Nouveautés OpenAI', 'Découvertes en IA', 'Applications concrètes de l’IA']
];

const chatbotWindow = document.getElementById('chatbot-window');

let isDragging = false;
let offsetX, offsetY;

chatbotWindow.addEventListener('mousedown', (e) => {
    if (e.target.id === 'chatbot-icon-inside') {
        isDragging = true;
        offsetX = e.clientX - chatbotWindow.offsetLeft;
        offsetY = e.clientY - chatbotWindow.offsetTop;
    }
});

document.addEventListener('mousemove', (e) => {
    if (isDragging) {
        chatbotWindow.style.left = `${e.clientX - offsetX}px`;
        chatbotWindow.style.top = `${e.clientY - offsetY}px`;
    }
});

document.addEventListener('mouseup', () => {
    isDragging = false;
});

document.getElementById('open-fullscreen-chatbot').addEventListener('click', () => {
    window.location.href = '/chatbot/full'; // Remplace par le chemin exact vers la page complète
});

document.getElementById('refresh-suggestions').addEventListener('click', () => {
    const randomIndex = Math.floor(Math.random() * suggestionsList.length);
    const newSuggestions = suggestionsList[randomIndex];

    const suggestionsContainer = document.getElementById('chatbot-suggestions');
    suggestionsContainer.innerHTML = newSuggestions.map(
        suggestion => `<button class="chatbot-suggestion">${suggestion}</button>`
    ).join('');
});

document.addEventListener('DOMContentLoaded', function () {
    fetch('/api/news/ia') // Appelle la route Symfony
        .then(response => response.json())
        .then(data => {
            const swiperWrapper = document.querySelector('#ia-news-carousel .swiper-wrapper');
            swiperWrapper.innerHTML = ''; // On vide le carrousel initial

            data.forEach(article => {
                const slide = `
                    <div class="swiper-slide">
                        <img src="${article.image}" alt="${article.title}" class="news-image"/>
                        <h3>${article.title}</h3>
                        <p>${article.summary}</p>
                        <a href="${article.link}" class="news-link">Lire plus</a>
                    </div>
                `;
                swiperWrapper.insertAdjacentHTML('beforeend', slide);
            });

            // Initialisation de Swiper
            new Swiper('#ia-news-carousel', {
                loop: true,
                pagination: {el: '.swiper-pagination', clickable: true},
                navigation: {nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev'},
                autoplay: {delay: 5000},
            });
        })
        .catch(error => console.error('Erreur lors du chargement des actualités IA:', error));
});

document.addEventListener('DOMContentLoaded', function () {
    const chatbotIcon = document.getElementById('chatbot-icon');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const suggestions = document.querySelectorAll('.chatbot-suggestion');

    // Ouvrir/fermer le chatbot
    chatbotIcon.addEventListener('click', () => {
        chatbotWindow.classList.toggle('open');
    });

    // Suggestions prédéfinies
    suggestions.forEach(button => {
        button.addEventListener('click', () => {
            const question = button.textContent;
            appendMessage('Vous', question);
            sendQuestionToAPI(question);
        });
    });

    function sendQuestionToAPI(question) {
        fetch('/api/chatbot', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({question: question})
        })
            .then(response => response.json())
            .then(data => {
                console.log('Données reçues depuis sendQuestionToAPIde l\'API:', data); // Vérifie la structure
                const responseArray = data.response; // On extrait la clé `response`
                if (Array.isArray(responseArray)) {
                    appendMessage('Bot', responseArray); // On passe directement le tableau
                } else {
                    appendMessage('Bot', [
                        {
                            title: 'Erreur',
                            summary: 'Format inattendu des données',
                            link: '#',
                            image: '/images/error.png'
                        }
                    ]);
                }
            })
            .catch(error => {
                console.error('Erreur API :', error);
                appendMessage('Bot', "Une erreur est survenue. Réessayez plus tard.");
            });
    }

    // Mise à jour de l'envoi par l'input
    chatbotInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const question = chatbotInput.value.trim();
            if (question) {
                appendMessage('Vous', question);
                chatbotInput.value = '';
                sendQuestionToAPI(question);
            }
        }
    });

    function appendMessage(sender, data, feedbackEnabled = false) {
        // Vérifier si `data` contient une clé `response` avec un tableau
        if (sender === 'Bot' && data && Array.isArray(data)) {
            console.log(data)
            data.forEach(item => {
                const card = document.createElement('div');
                card.className = 'chatbot-card';
                card.innerHTML = `
                <img src="${item.image}" alt="${item.title}" class="card-image" />
                <div class="card-content">
                    <h4>${item.title}</h4>
                    <p>${item.summary}</p>
                    <a href="${item.link}" target="_blank">Lire plus</a>
                </div>
            `;
                chatbotMessages.appendChild(card);
            });
        } else if (sender === 'Bot' && typeof data === 'string') {
            const messageDiv = document.createElement('div');
            messageDiv.innerHTML = `<strong>${sender} :</strong> ${data}`;
            chatbotMessages.appendChild(messageDiv);
        } else {
            const messageDiv = document.createElement('div');
            messageDiv.innerHTML = `<strong>${sender} :</strong> ${data}`;
            chatbotMessages.appendChild(messageDiv);


            // Ajouter les boutons de feedback si activé
            if (feedbackEnabled && sender === 'Bot') {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.className = 'chatbot-feedback';
                feedbackDiv.innerHTML = `
            <button class="feedback-btn" data-feedback="up">👍</button>
            <button class="feedback-btn" data-feedback="down">👎</button>
        `;
                messageDiv.appendChild(feedbackDiv);

                // Gestion des clics sur le feedback
                feedbackDiv.querySelectorAll('.feedback-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const feedback = e.target.getAttribute('data-feedback');
                        sendFeedbackToAPI(item.summary || 'Aucune réponse', feedback); // Utiliser un texte valide
                        feedbackDiv.innerHTML = "Merci pour votre retour !";
                    });
                });
            }
        }

        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function sendFeedbackToAPI(responseText, feedback) {
        fetch('/api/feedback', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ responseText, feedback })
        })
            .then(response => response.json())
            .then(data => console.log('Feedback enregistré:', data))
            .catch(error => console.error('Erreur lors de l\'enregistrement du feedback:', error));
    }

    const micButton = document.createElement('button');
    micButton.innerHTML = "🎙️";
    micButton.style = "position: absolute; right: 10px; bottom: 10px;";
    chatbotWindow.appendChild(micButton);

    micButton.addEventListener('click', () => {
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'fr-FR';

        recognition.onresult = function(event) {
            const voiceQuery = event.results[0][0].transcript;
            appendMessage('Vous', voiceQuery);
            sendQuestionToAPI(voiceQuery);
        };

        recognition.start();
    });


});

const canvas = document.getElementById('ia-animation');
const ctx = canvas.getContext('2d');

canvas.width = canvas.clientWidth;
canvas.height = canvas.clientHeight;

let particles = [];

function createParticle(x, y) {
    return {
        x: x,
        y: y,
        size: Math.random() * 5 + 1,
        speedX: Math.random() * 3 - 1.5,
        speedY: Math.random() * 3 - 1.5,
        color: `hsl(${Math.random() * 360}, 100%, 50%)`,
    };
}

function animateParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach((particle, index) => {
        particle.x += particle.speedX;
        particle.y += particle.speedY;
        particle.size *= 0.98;

        if (particle.size <= 0.5) particles.splice(index, 1);

        ctx.fillStyle = particle.color;
        ctx.beginPath();
        ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
        ctx.closePath();
        ctx.fill();
    });
    requestAnimationFrame(animateParticles);
}

// Ajout de particules au clic
canvas.addEventListener('click', (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    for (let i = 0; i < 50; i++) {
        particlesArray.push(createParticle(x, y));
    }
});

animateParticles();



