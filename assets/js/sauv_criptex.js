
const formEl = document.getElementById('cryptex-form');
const root = document.getElementById('cryptex');
const secret = document.getElementById('secret');

function updatePreview(){
    root.dataset.alphabet = (document.getElementById('{{ form.alphabet.vars.id }}').value || 'ABCDEFGHIJKLMNOPQRSTUVWXYZ').toUpperCase();
    root.dataset.scramble = document.getElementById('{{ form.scramble.vars.id }}').checked ? 'true':'false';
    root.dataset.autocheck = document.getElementById('{{ form.autocheck.vars.id }}').checked ? 'true':'false';
    const hashMode = document.getElementById('{{ form.hashMode.vars.id }}').checked;
    if (hashMode){
        root.dataset.solution = '';
        // Laisse data-hash vide en preview (ou calcule un hash côté client si tu veux)
        root.dataset.hash = '';
    } else {
        root.dataset.hash = '';
        root.dataset.solution = (document.getElementById('{{ form.solution.vars.id }}').value || '').toUpperCase();
    }
    secret.innerHTML = '<p>'+(document.getElementById('{{ form.successMessage.vars.id }}').value || 'Bravo !')+'</p>';

    // Re-monter un nouveau composant (simple: on remplace le nœud)
    const clone = root.cloneNode(false);
    root.parentNode.replaceChild(clone, root);
    // ré-attacher le script cryptex (IIFE) — place ton <script>(function(){...})()</script> *après* ce bloc
    // astuce: déclenche un event custom pour que ton module relise les data-*
    document.dispatchEvent(new Event('reinit-cryptex'));
}

formEl.addEventListener('input', updatePreview);
document.addEventListener('reinit-cryptex', ()=>{/* si tu modularises, écoute cet event */});
