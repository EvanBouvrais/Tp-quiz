
const utilisateurConnecte = window.utilisateurConnecte;

function accederAuQuiz(event, quizId) {
    if (utilisateurConnecte) {
        return true;
    }

    event.preventDefault();
    openConnexion(quizId);
    return false;
}

function openConnexion(quizId = '') {
    const fenetre = document.getElementById('fenetreConnexion');
    const champQuiz = document.getElementById('quiz_id');

    if (!fenetre) {
        console.error("La fenêtre de connexion est introuvable.");
        return;
    }

    // Mémoriser le quiz sélectionné, si le champ existe.
    if (champQuiz) {
        champQuiz.value = quizId;
    }

    fenetre.classList.remove('hidden');
    fenetre.classList.add('flex');
}

function closeConnexion() {
    const fenetre = document.getElementById('fenetreConnexion');

    if (!fenetre) {
        console.error("La fenêtre de connexion est introuvable.");
        return;
    }

    fenetre.classList.remove('flex');
    fenetre.classList.add('hidden');
}