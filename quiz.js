let intervalleTimer = null;

function afficherCorrection(formulaire) {
    formulaire.dataset.reponseVerrouillee = "true";
    const cases = Array.from(formulaire.querySelectorAll(".reponse"));

    cases.forEach((caseReponse) => {
        caseReponse.classList.add(
            caseReponse.dataset.correct === "true"
                ? "reponse-correcte"
                : "reponse-incorrecte",
            "reponse-verrouillee"
        );
    });

    formulaire.querySelectorAll('input[type="radio"]').forEach((radio) => {
        radio.disabled = true;
    });
}

function lancerTimer() {
    const affichage = document.getElementById("tempsRestant");
    const timer = document.getElementById("timer");
    const barre = document.getElementById("timerBarre");
    const formulaire = document.getElementById("quizForm");

    if (!affichage || !timer || !barre || !formulaire) {
        return;
    }

    let temps = 30;

    timer.style.color = "#5100b9";
    barre.style.width = "100%";
    barre.style.backgroundColor = "#5100b9";

    intervalleTimer = window.setInterval(() => {
        temps--;

        affichage.textContent = temps;
        barre.style.width = (temps / 30 * 100) + "%";

        if (temps <= 5) {
            timer.style.color = "#c62828";
            barre.style.backgroundColor = "#c62828";
        }

        if (temps <= 0) {
            window.clearInterval(intervalleTimer);
            intervalleTimer = null;

            afficherCorrection(formulaire);

            const tempsEcoule = document.createElement("input");
            tempsEcoule.type = "hidden";
            tempsEcoule.name = "temps_ecoule";
            tempsEcoule.value = "1";
            formulaire.appendChild(tempsEcoule);

            // Envoie le formulaire sans exiger une réponse radio.
            window.setTimeout(() => formulaire.submit(), 1000);
        }
    }, 1000);
}

lancerTimer();

function verifierReponse(reponse) {
    const formulaire = reponse.form;

    if (
        !formulaire ||
        formulaire.dataset.reponseVerrouillee === "true"
    ) {
        return;
    }

    if (intervalleTimer !== null) {
        window.clearInterval(intervalleTimer);
        intervalleTimer = null;
    }

    afficherCorrection(formulaire);

    const valeurReponse = document.createElement("input");
    valeurReponse.type = "hidden";
    valeurReponse.name = "reponse";
    valeurReponse.value = reponse.value;
    formulaire.appendChild(valeurReponse);

    window.setTimeout(() => formulaire.submit(), 1000);
}
