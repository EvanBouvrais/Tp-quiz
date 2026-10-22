<?php
    session_start();

    $isConnected = isset($_SESSION["user_id"]);
    $username = $_SESSION["username"] ?? "";

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Selection</title>
    <link href="output.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>

<body class="m-0 min-h-screen max-h-screen w-full index-main-bg">

    <!-- En-tête -->
    <header class="flex h-30 w-full items-stretch">

        <!-- Titre de la page -->
        <section class="flex flex-1 items-center border-r-4 border-b-2 border-black bg-white px-4">
            <h1 class="text-2xl text-purple-800 sm:text-4xl">
                QUIZ SELECTION:
            </h1>
        </section>

        <!-- Zone de connexion -->
        
        <!-- Zone de connexion -->
        <section class="flex h-25 w-50 shrink-0 flex-col items-center justify-center gap-1 border-b-2 border-black bg-white">

            <p class="text-base leading-5">
                Connected as :
            </p>

            <?php if ($isConnected): ?>

                <p class="font-semibold text-purple-800">
                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <button
                    type="button"
                    onclick="openConnexion()"
                    class="cursor-pointer text-sm text-blue-600 underline hover:text-blue-800"
                >
                    Change user
                </button>

            <?php else: ?>

                <button type="button" onclick="openConnexion()" class="cursor-pointer text-blue-600 underline hover:text-blue-800">
                    Sign in
                </button>

            <?php endif; ?>

        </section>


    </header>

    <!-- Liste des quiz -->
    <main class="flex flex-col gap-3 px-15 pt-20">

        <!-- Quiz 1 -->
        <a href="quiz.php" class="quiz-link flex h-30 items-center justify-between rounded-2xl bg-linear-to-b from-blue-400 via-blue-700 to-blue-900 px-4 py-3 text-white">

            <div>
                <p class="text-2xl">Quiz n°</p>
                <p class="text-3xl">[]</p>
            </div>

            <p class="self-end text-sm">Max score :</p>

        </a>
    </main>

    <!-- Fenêtre de connexion -->
    <section id="fenetreConnexion" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true" aria-labelledby="titreConnexion">
        <div class="w-full max-w-md rounded-xl border-2 border-[#2577D9] bg-[#202A36] p-6">

            <!-- En-tête de la fenêtre -->
            <div class="mb-4 flex items-center justify-between">
                <h2 id="titreConnexion" class="text-xl font-bold text-white">
                    Connexion
                </h2>

                <button type="button" onclick="closeConnexion()" class="text-2xl text-white" aria-label="Fermer la fenêtre">
                    &times;
                </button>
            </div>

            <p class="mb-4 text-gray-300">
                Choisis ton pseudonyme pour participer aux quiz.
            </p>

            <!-- Formulaire -->
            <form action="traitement-connexion.php" method="POST">

                <label for="username" class="mb-2 block text-white">
                    Pseudonyme
                </label>

                <input type="text" id="username" name="username" maxlength="50" required autocomplete="nickname" placeholder="Ton pseudonyme" class="mb-4 w-full rounded-md border border-[#2577D9] bg-[#151D28] p-2 text-white">

                <!-- Quiz sélectionné, s'il y en a un -->
                <input type="hidden" id="quiz_id" name="quiz_id" value="" >

                <button type="submit" class="w-full rounded-md bg-[#2577D9] p-2 text-white hover:bg-blue-600">
                    Se connecter
                </button>

            </form>
        </div>
    </section>


    <script>
        function openConnexion(quizId = '') {
            const fenetre = document.getElementById('fenetreConnexion');

            // Mémoriser le quiz sélectionné
            document.getElementById('quiz_id').value = quizId;

            fenetre.classList.remove('hidden');
            fenetre.classList.add('flex');
        }

        function closeConnexion() {
            const fenetre = document.getElementById('fenetreConnexion');

            fenetre.classList.remove('flex');
            fenetre.classList.add('hidden');
        }
    </script>

</body>
</html>