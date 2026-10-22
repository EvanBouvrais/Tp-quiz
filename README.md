# Tp-quiz

## Configuration de la base de données

1. Importer le fichier `mini-quizz (2) (1).sql` dans MySQL via phpMyAdmin ou la ligne de commande.
2. Par défaut, l'application utilise `127.0.0.1`, la base `mini-quizz (2) (1)` (base importée dans ton Laragon) et l'utilisateur MySQL `root` sans mot de passe.
3. Si ta configuration MySQL est différente, définis `DB_HOST`, `DB_NAME`, `DB_USER` et `DB_PASSWORD` dans l'environnement PHP/Apache avant de démarrer Apache.

La page d'accueil affiche les quiz enregistrés dans `quizzes`. Les questions et les réponses proviennent de `questions` et `answer_options`. La connexion crée ou retrouve un compte dans `users`, puis chaque partie terminée enregistre son score dans `quiz_scores`.
