// escape-garden.js

let currentStep = 1;

function startGame() {
    const intro = document.querySelector('.intro');
    const gameSection = document.querySelector('#game-section');
    if (intro && gameSection) {
        intro.classList.add('hidden');
        gameSection.classList.remove('hidden');
        showStep(currentStep);
    }
}

function showStep(stepNumber) {
    const steps = document.querySelectorAll('.step');
    steps.forEach(step => step.classList.add('hidden'));
    const current = document.querySelector(`#step-${stepNumber}`);
    if (current) {
        current.classList.remove('hidden');
    }
}

function checkCode() {
    const input = document.getElementById('codeInput');
    const feedback = document.getElementById('codeFeedback');
    if (!input || !feedback) return;

    const value = input.value.trim().toLowerCase();
    if (value === 'wifi') {
        feedback.innerHTML = "<span style='color:green'>Code correct !</span>";
        currentStep = 'final';
        showStep(currentStep);
    } else {
        feedback.innerHTML = "<span style='color:red'>Essaie encore...</span>";
    }
}

function nextStep(event) {
    const button = event.currentTarget;
    button.disabled = true;
    currentStep++;
    showStep(currentStep);
    setTimeout(() => {
        button.disabled = false;
    }, 1000);
}

function showForm(type) {
    document.getElementById('choice-section').style.display = 'none';
    document.getElementById('register-form').classList.add('hidden');
    document.getElementById('login-form').classList.add('hidden');

    if (type === 'register') {
        document.getElementById('register-form').classList.remove('hidden');
    } else if (type === 'login') {
        document.getElementById('login-form').classList.remove('hidden');
    }
}


// Attacher les événements après chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    const startBtn = document.querySelector('.start-btn');
    if (startBtn) {
        startBtn.addEventListener('click', startGame);
    }

    const checkBtn = document.querySelector('#step-3 button');
    if (checkBtn) {
        checkBtn.addEventListener('click', checkCode);
    }

    const nextBtns = document.querySelectorAll('button[data-next-step]');
    nextBtns.forEach(btn => {
        btn.addEventListener('click', nextStep);
    });

    const btnRegister = document.getElementById('btn-register');
    const btnLogin = document.getElementById('btn-login');

    if (btnRegister) {
        btnRegister.addEventListener('click', () => showForm('register'));
    }
    if (btnLogin) {
        btnLogin.addEventListener('click', () => showForm('login'));
    }

});


/*
(function(){
    const root = document.getElementById('cryptex');
    if(!root) return;

    // Config
    const SOLUTION = (root.dataset.solution || 'CODE').toUpperCase();
    const ALPHABET = (root.dataset.alphabet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZ').split('');
    const SCRAMBLE = root.dataset.scramble !== 'false';
    const AUTOCHECK = root.dataset.autocheck !== 'false';

    const status = document.getElementById('cryptex-status');
    const revealBtn = document.getElementById('reveal');
    const secret = document.getElementById('secret');

    // Etat courant des bagues
    const state = [];
    const rings = [];

    // Utilitaires
    const mod = (n, m) => ((n % m) + m) % m;

    function makeRing(initialChar, index){
        const ring = document.createElement('div');
        ring.className = 'ring';
        ring.setAttribute('role','group');
        ring.setAttribute('aria-label', `Bague ${index+1}`);

        const btnUp = document.createElement('button');
        btnUp.type = 'button';
        btnUp.setAttribute('aria-label', `Tourner la bague ${index+1} vers le haut`);
        btnUp.textContent = '▲';

        const value = document.createElement('div');
        value.className = 'ring-value';
        value.setAttribute('role','textbox');
        value.setAttribute('aria-live','off');
        value.setAttribute('aria-label', `Lettre sélectionnée bague ${index+1}`);

        const btnDown = document.createElement('button');
        btnDown.type = 'button';
        btnDown.setAttribute('aria-label', `Tourner la bague ${index+1} vers le bas`);
        btnDown.textContent = '▼';

        const startIdx = ALPHABET.indexOf(initialChar);
        state[index] = startIdx >= 0 ? startIdx : 0;

        function render(){ value.textContent = ALPHABET[state[index]]; }

        function step(delta){
            state[index] = mod(state[index] + delta, ALPHABET.length);
            render();
            if (AUTOCHECK) checkSolved();
        }

        // Interactions
        btnUp.addEventListener('click', ()=> step(1));
        btnDown.addEventListener('click', ()=> step(-1));

        // Molette
        ring.addEventListener('wheel', (e)=>{
            e.preventDefault();
            step(e.deltaY > 0 ? -1 : 1);
        }, {passive:false});

        // Clavier (flèches haut/bas)
        ring.tabIndex = 0;
        ring.addEventListener('keydown', (e)=>{
            if(e.key === 'ArrowUp'){ e.preventDefault(); step(1); }
            if(e.key === 'ArrowDown'){ e.preventDefault(); step(-1); }
        });

        ring.appendChild(btnUp);
        ring.appendChild(value);
        ring.appendChild(btnDown);

        render();
        return {root: ring, render};
    }

    // Construire les bagues
    const letters = SOLUTION.split('');
    letters.forEach((ch, i) => {
        const init = SCRAMBLE
            ? ALPHABET[Math.floor(Math.random()*ALPHABET.length)]
            : ch;
        const ring = makeRing(init, i);
        rings.push(ring);
        root.appendChild(ring.root);
    });

    function currentWord(){
        return state.map(i => ALPHABET[i]).join('');
    }

    function checkSolved(){
        const word = currentWord();
        const ok = word === SOLUTION;
        status.textContent = ok ? '✅ Code correct' : '…';
        revealBtn.hidden = !ok;
        if (ok) {
            root.dispatchEvent(new CustomEvent('cryptex:solved',{detail:{solution: word}}));
        }
        return ok;
    }

    // Bouton "Voir le message"
    revealBtn.addEventListener('click', ()=>{
        if (checkSolved()){
            secret.hidden = false;
            revealBtn.disabled = true;
            revealBtn.textContent = 'Déverrouillé';
        }
    });

    // Si tu préfères une validation manuelle, mets data-autocheck="false"
    // et ajoute un bouton "Valider" qui appelle checkSolved().

    // Vérification initiale (au cas où SCRAMBLE=false)
    if (AUTOCHECK) checkSolved();
})();
*/
